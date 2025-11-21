<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Certificate;

class StudentController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $query = Certificate::query();
        $query->where('student_name', $user->name);
        if (! empty($user->wallet ?? null)) {
            $query->orWhere('student_wallet', $user->wallet);
        }

        $certs = $query->orderBy('created_at', 'desc')->get();

        return view('student.certificates', compact('certs'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $cert = Certificate::findOrFail($id);

        $allowed = ($cert->student_name === $user->name) || (! empty($user->wallet ?? null) && $cert->student_wallet === $user->wallet);
        if (! $allowed) {
            abort(403);
        }

        return view('student.show', compact('cert'));
    }

    public function download($id)
    {
        $user = Auth::user();
        $cert = Certificate::findOrFail($id);

        $allowed = ($cert->student_name === $user->name) || (! empty($user->wallet ?? null) && $cert->student_wallet === $user->wallet);
        if (! $allowed) {
            abort(403);
        }

        if (! $cert->pdf_path || ! Storage::exists($cert->pdf_path)) {
            abort(404);
        }

        return Storage::download($cert->pdf_path, basename($cert->pdf_path));
    }

    // Public download endpoint used from verification pages.
    // Anyone with the link can download the original PDF.
    public function publicDownload($id)
    {
        $cert = Certificate::findOrFail($id);

        if (! $cert->pdf_path || ! Storage::exists($cert->pdf_path)) {
            abort(404);
        }

        return Storage::download($cert->pdf_path, basename($cert->pdf_path));
    }
}
