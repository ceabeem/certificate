@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Verification Result</h1>

    <div class="row">
        <div class="col-md-8 mb-3">
            <div class="card">
                <div class="card-body">
                    @if($status)
                        <h3 class="text-success mb-3">{{ $message }}</h3>
                    @else
                        <h3 class="text-danger mb-3">{{ $message }}</h3>
                    @endif

                    @if(! empty($certificate))
                        <h5 class="mb-3">Certificate details</h5>
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Certificate ID</dt>
                            <dd class="col-sm-8">{{ $certificate->certificate_code ?? $certificate->id }}</dd>

                            <dt class="col-sm-4">Student</dt>
                            <dd class="col-sm-8">{{ $certificate->student_name }}</dd>

                            <dt class="col-sm-4">Course</dt>
                            <dd class="col-sm-8">{{ $certificate->course ?? '—' }}</dd>

                            <dt class="col-sm-4">Grade / result</dt>
                            <dd class="col-sm-8">{{ $certificate->grade ?? '—' }}</dd>

                            <dt class="col-sm-4">Completed date</dt>
                            <dd class="col-sm-8">{{ $certificate->completed_date ?? '—' }}</dd>

                            <dt class="col-sm-4">Issue date</dt>
                            <dd class="col-sm-8">{{ $certificate->issue_date ?? '—' }}</dd>

                            <dt class="col-sm-4">IPFS CID</dt>
                            <dd class="col-sm-8">
                                @if($certificate->ipfs_cid)
                                    <a href="https://ipfs.io/ipfs/{{ $certificate->ipfs_cid }}" target="_blank">{{ $certificate->ipfs_cid }}</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </dd>
                        </dl>
                    @endif
                </div>
            </div>
        </div>

        @if(! empty($certificate))
        <div class="col-md-4 mb-3">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">On-chain status</h5>
                    @if($certificate->blockchain_tx)
                        <p>
                            <span class="badge bg-success">Published</span>
                        </p>
                        <p class="mb-1"><strong>Transaction hash</strong></p>
                        <p class="small mb-2">{{ $certificate->blockchain_tx }}</p>
                        <a href="https://amoy.polygonscan.com/tx/{{ $certificate->blockchain_tx }}" target="_blank" class="btn btn-sm btn-outline-primary">View on explorer</a>
                    @else
                        <p>
                            <span class="badge bg-secondary">Not yet published</span>
                        </p>
                        <p class="text-muted small mb-0">Certificate exists in the database but has not been submitted on-chain yet.</p>
                    @endif

                    @if($certificate->pdf_path)
                        <hr>
                        <h6>Original PDF</h6>
                        <a href="{{ route('certificates.public.download', $certificate->id) }}" class="btn btn-sm btn-primary">Download PDF</a>
                    @endif

                    <hr>
                    <h6>Share verification</h6>
                    <p class="text-muted small mb-1">Share this link or QR so others can verify:</p>
                    <code class="small d-block mb-2">{{ url('/verify/' . $certificate->sha256_hash) }}</code>

                    <div class="mt-2 text-center">
                        <img src="{{ route('certificate.qr', $certificate->sha256_hash) }}" alt="Verification QR" style="max-width: 150px; height: auto;">
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
