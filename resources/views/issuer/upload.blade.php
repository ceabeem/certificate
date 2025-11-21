@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">Create Certificate</h1>

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('issuer.upload') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label">Student name</label>
                    <input type="text" name="student_name" class="form-control" required />
                </div>

                <div class="mb-3">
                    <label class="form-label">Student wallet (optional)</label>
                    <input type="text" name="student_wallet" class="form-control" placeholder="0x..." />
                    <div class="form-text">
                        If provided, this wallet can be used to match on-chain ownership.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Course name</label>
                    <input type="text" name="course" class="form-control" placeholder="e.g. Blockchain Fundamentals" />
                </div>

                <div class="mb-3">
                    <label class="form-label">Grade / result (optional)</label>
                    <input type="text" name="grade" class="form-control" placeholder="e.g. A, Pass, 85%" />
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Completed date</label>
                        <input type="date" name="completed_date" class="form-control" />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Issue date</label>
                        <input type="date" name="issue_date" class="form-control" />
                    </div>
                </div>

                <button class="btn btn-primary mt-2">Generate &amp; Save</button>
            </form>

            <p class="mt-3 text-muted">
                The system will generate a PDF certificate with these details,
                upload it to IPFS, and prepare a payload for blockchain publication.
            </p>
        </div>
    </div>
</div>
@endsection
