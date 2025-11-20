<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Certificate;
use App\Services\BlockchainService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class VerificationController extends Controller
{
    protected $chain;

    public function __construct(BlockchainService $chain)
    {
        $this->chain = $chain;
    }

    public function showForm()
    {
        return view('verify.form');
    }

    public function verifyByUpload(Request $request)
    {
    // Allow any file type for verification uploads (not limited to PDF). Max size 50MB.
    $request->validate(['pdf' => 'required|file|max:51200']);
        $file = $request->file('pdf');
        $path = $file->store('temp_verifications');
        // Resolve the actual local path for the configured filesystem disk
        $local = Storage::path($path);

        // Fallback if Storage::path doesn't resolve (older config), use storage_path
        if (! file_exists($local)) {
            $local = storage_path('app/' . $path);
        }

        $sha = hash_file('sha256', $local);

        // Remove the temporary file via Storage to respect the disk
        try {
            Storage::delete($path);
        } catch (\Throwable $e) {
            @unlink($local);
        }

        return $this->verifyByHashInternal($sha);
    }

    public function verifyByHash(Request $request)
    {
        $request->validate(['hash' => 'required|string']);
        $sha = $request->input('hash');
        return $this->verifyByHashInternal($sha);
    }

    public function verifyByHashGet(string $sha)
    {
        // allow verification by GET route (useful for QR links)
        return $this->verifyByHashInternal($sha);
    }

    /**
     * Return a PNG QR image that encodes the public verification URL for a given SHA.
     * Proxies a small QR image from QuickChart to avoid adding new PHP deps.
     */
    public function qr(string $sha)
    {
        $verifyUrl = url('/verify/' . $sha);

        // Use QuickChart's simple QR endpoint
        $qrApi = 'https://quickchart.io/qr';
        $client = new Client(['timeout' => 10]);

        try {
            $res = $client->request('GET', $qrApi, ['query' => ['text' => $verifyUrl, 'size' => 300]]);
            $image = $res->getBody()->getContents();
            return response($image, 200)->header('Content-Type', 'image/png');
        } catch (\Throwable $e) {
            Log::error('QR generation failed: ' . $e->getMessage());
            // As a fallback, return a 1x1 transparent PNG
            $img = imagecreatetruecolor(1, 1);
            imagesavealpha($img, true);
            $trans_colour = imagecolorallocatealpha($img, 0, 0, 0, 127);
            imagefill($img, 0, 0, $trans_colour);
            ob_start();
            imagepng($img);
            $data = ob_get_clean();
            imagedestroy($img);
            return response($data, 200)->header('Content-Type', 'image/png');
        }
    }

    protected function verifyByHashInternal(string $sha)
    {
        $cert = Certificate::where('sha256_hash', $sha)->first();
        if (! $cert) {
            return view('verify.result', ['status' => false, 'message' => 'Certificate not found in records']);
        }

        // If we have an on-chain tx recorded, check it
        if ($cert->blockchain_tx) {
            $confirmed = $this->chain->checkTxConfirmed($cert->blockchain_tx);
            if ($confirmed) {
                return view('verify.result', ['status' => true, 'message' => '✅ Verified Certificate', 'certificate' => $cert]);
            }
            return view('verify.result', ['status' => false, 'message' => 'Transaction found but not confirmed yet', 'certificate' => $cert]);
        }

        // No tx yet: still valid in DB but not proven on-chain
        return view('verify.result', ['status' => false, 'message' => 'Certificate exists in DB but not yet published on-chain', 'certificate' => $cert]);
    }

    /**
     * Endpoint for node script to POST back the tx hash after sending the transaction.
     * Expects JSON: { payload_file: string, tx_hash: string }
     */
    public function blockchainCallback(Request $request)
    {
        // Basic HMAC verification to ensure the callback is from a trusted operator.
        // Operator should send header 'X-Signature' = hex(hmac_sha256(payload, secret)).
        $secret = env('OPERATOR_CALLBACK_SECRET');
        $payload = $request->getContent();
        $signature = $request->header('X-Signature') ?? $request->header('X-Hub-Signature-256');

        if ($secret) {
            if (! $signature) {
                return response()->json(['ok' => false, 'message' => 'Missing X-Signature header'], 403);
            }
            $computed = hash_hmac('sha256', $payload, $secret);
            if (! hash_equals($computed, preg_replace('/^sha256=/', '', $signature))) {
                return response()->json(['ok' => false, 'message' => 'Invalid signature'], 403);
            }
        } else {
            // If no secret configured, allow but log a warning in non-dev environments.
            if (! app()->environment('local')) {
                Log::warning('Operator callback received but OPERATOR_CALLBACK_SECRET is not set. Allowing for compatibility.');
            }
        }

        $request->validate(['payload_file' => 'required|string', 'tx_hash' => 'required|string']);
        $payloadFile = $request->input('payload_file');
        $tx = $request->input('tx_hash');

        $cert = Certificate::where('blockchain_payload_file', $payloadFile)->first();
        if (! $cert) {
            return response()->json(['ok' => false, 'message' => 'No matching local payload found'], 404);
        }

        $cert->blockchain_tx = $tx;
        $cert->save();

        return response()->json(['ok' => true]);
    }
}
