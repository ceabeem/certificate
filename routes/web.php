<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\IssuerController;
use App\Http\Controllers\VerificationController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\File;

// Authentication routes (simple local auth for dev)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Issuer routes (requires auth middleware in your app)
Route::middleware(['auth'])->group(function () {
    Route::get('/issuer/dashboard', [IssuerController::class, 'dashboard'])->name('issuer.dashboard');
    Route::get('/issuer/upload', [IssuerController::class, 'showUpload'])->name('issuer.upload.show');
    Route::post('/issuer/upload', [IssuerController::class, 'upload'])->name('issuer.upload');

    // Student area (authenticated students can view their certificates)
    Route::get('/student/certificates', [StudentController::class, 'index'])->name('student.certificates');
    Route::get('/student/certificates/{id}', [StudentController::class, 'show'])->name('student.certificates.show');
    Route::get('/student/certificates/{id}/download', [StudentController::class, 'download'])->name('student.certificates.download');
});

// Public download route so verifiers can fetch the original PDF
Route::get('/certificates/{id}/download', [StudentController::class, 'publicDownload'])->name('certificates.public.download');

// Verification routes (public)
Route::get('/verify', [VerificationController::class, 'showForm'])->name('verify.form');
Route::post('/verify/upload', [VerificationController::class, 'verifyByUpload'])->name('verify.upload');
Route::post('/verify/hash', [VerificationController::class, 'verifyByHash'])->name('verify.hash');
// Allow verification by GET for links or QR codes: /verify/{sha}
Route::get('/verify/{sha}', [VerificationController::class, 'verifyByHashGet'])->name('verify.hash.get');

// QR PNG endpoint for certificate verification links (e.g. /certificate/{sha}/qr.png)
Route::get('/certificate/{sha}/qr.png', [VerificationController::class, 'qr'])->name('certificate.qr');

// Serve docs files from the repository `docs/` directory (returns raw Markdown)
Route::get('/docs/{path}', function ($path) {
    // Only allow relative paths within docs folder to avoid traversal
    if (strpos($path, '..') !== false) {
        abort(404);
    }

    $file = base_path('docs/' . $path);
    if (! File::exists($file)) {
        abort(404);
    }

    $contents = File::get($file);
    return response($contents, 200)->header('Content-Type', 'text/markdown');
})->where('path', '.*');

