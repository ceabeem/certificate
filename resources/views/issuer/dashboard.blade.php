@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Your Issued Certificates</h1>
        <a href="{{ route('issuer.upload.show') }}" class="btn btn-success">Create certificate</a>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Code</th>
                        <th>Student</th>
                        <th>Course</th>
                        <th>Grade</th>
                        <th>Issue date</th>
                        <th>IPFS</th>
                        <th>Blockchain tx</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($certs as $c)
                    <tr>
                        <td>{{ $c->id }}</td>
                        <td>{{ $c->certificate_code ?? '—' }}</td>
                        <td>{{ $c->student_name }}</td>
                        <td>{{ $c->course }}</td>
                        <td>{{ $c->grade ?? '—' }}</td>
                        <td>{{ $c->issue_date }}</td>
                        <td>
                            @if($c->ipfs_cid)
                                <a href="https://ipfs.io/ipfs/{{ $c->ipfs_cid }}" target="_blank">{{ Str::limit($c->ipfs_cid, 16) }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @if($c->blockchain_tx)
                                <span class="badge bg-success">Published</span>
                            @else
                                <span class="badge bg-secondary">Pending</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-3">No certificates issued yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
