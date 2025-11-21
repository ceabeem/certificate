@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Verify Certificate</h1>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Verify by PDF upload</h5>
                    <p class="card-text text-muted">Upload the original certificate PDF you received.</p>
                    <form method="POST" action="{{ route('verify.upload') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Certificate PDF</label>
                            <input type="file" name="pdf" class="form-control" required />
                        </div>
                        <button class="btn btn-primary">Verify Upload</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Verify by SHA-256 hash</h5>
                    <p class="card-text text-muted">Paste the SHA-256 hash of the certificate file.</p>
                    <form method="POST" action="{{ route('verify.hash') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">SHA-256 hash</label>
                            <input type="text" name="hash" class="form-control" placeholder="e.g. a3f5..." />
                        </div>
                        <button class="btn btn-secondary">Verify Hash</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <p class="text-muted">
        Verification checks that the PDF's hash matches a stored certificate and, when available,
        confirms that the corresponding record has been published on-chain.
    </p>
</div>
@endsection
