<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Certificate;
use App\Services\IpfsService;
use App\Services\BlockchainService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IssuerController extends Controller
{
    protected $ipfs;
    protected $chain;

    public function __construct(IpfsService $ipfs, BlockchainService $chain)
    {
        $this->ipfs = $ipfs;
        $this->chain = $chain;
    }

    public function dashboard()
    {
        $user = Auth::user();
        $certs = Certificate::where('issuer_id', $user->id)->orderBy('created_at', 'desc')->get();
        return view('issuer.dashboard', compact('certs'));
    }

    public function showUpload()
    {
        return view('issuer.upload');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'student_name' => 'required|string',
            'student_wallet' => 'nullable|string',
            'course' => 'nullable|string',
            'grade' => 'nullable|string',
            'completed_date' => 'nullable|date',
            'issue_date' => 'nullable|date',
        ]);

        $code = 'CERT-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));

        $data = [
            'student_name' => $request->input('student_name'),
            'course' => $request->input('course'),
            'grade' => $request->input('grade'),
            'completed_date' => $request->input('completed_date') ?? $request->input('issue_date') ?? now()->toDateString(),
            'issue_date' => $request->input('issue_date') ?? now()->toDateString(),
            'certificate_code' => $code,
        ];

        // Generate PDF from Blade view (we will add sha256_hash later once we know it)
        $pdf = Pdf::loadView('certificates.pdf', $data);

        // Store generated PDF in same area as previous uploads
        $filename = 'certificates/' . time() . '_' . Str::slug($data['student_name']) . '.pdf';
        Storage::put($filename, $pdf->output());
        $localPath = Storage::path($filename);

        // compute sha256
        $sha = hash_file('sha256', $localPath);

        // upload to IPFS
        $ipfsResult = $this->ipfs->uploadFile($localPath);
        $cid = $ipfsResult['cid'] ?? null;

        $cert = Certificate::create([
            'student_name' => $request->input('student_name'),
            'student_wallet' => $request->input('student_wallet'),
            'course' => $request->input('course'),
            'grade' => $request->input('grade'),
            'completed_date' => $data['completed_date'],
            'issue_date' => $request->input('issue_date'),
            'certificate_code' => $code,
            'pdf_path' => $filename,
            'sha256_hash' => $sha,
            'ipfs_cid' => $cid,
            'issuer_id' => Auth::id(),
        ]);

        $payloadFile = $this->chain->prepareOnchainPayload($sha, $cid, $cert->student_wallet, Auth::id());
        $cert->blockchain_payload_file = $payloadFile;
        $cert->save();

        return redirect()->route('issuer.dashboard')->with('status', 'Certificate saved. IPFS CID: ' . $cid );
    }
}
