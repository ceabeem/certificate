<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Certificate;
use App\Services\IpfsService;
use App\Services\BlockchainService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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
        // Allow any file type for certificates (not limited to PDF). Max size 50MB by default.
        $request->validate([
            'pdf' => 'required|file|max:51200',
            'student_name' => 'required|string',
            'student_wallet' => 'nullable|string',
            'course' => 'nullable|string',
            'issue_date' => 'nullable|date',
        ]);

        $file = $request->file('pdf');
        $path = $file->store('certificates');
        $localPath = Storage::path($path);

        // compute sha256
        $sha = hash_file('sha256', $localPath);

        // upload to IPFS
        $ipfsResult = $this->ipfs->uploadFile($localPath);
        $cid = $ipfsResult['cid'] ?? null;

        $cert = Certificate::create([
            'student_name' => $request->input('student_name'),
            'student_wallet' => $request->input('student_wallet'),
            'course' => $request->input('course'),
            'issue_date' => $request->input('issue_date'),
            'pdf_path' => $path,
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
