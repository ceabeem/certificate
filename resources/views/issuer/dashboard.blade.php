@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Your Issued Certificates</h1>
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    <a href="{{ route('issuer.upload.show') }}" class="btn btn-primary">Upload new certificate</a>
    <hr />
    <table class="table">
        <thead><tr><th>ID</th><th>Student</th><th>Course</th><th>Issue Date</th><th>IPFS</th><th>Tx</th></tr></thead>
        <tbody>
        @foreach($certs as $c)
            <tr>
                <td>{{ $c->id }}</td>
                <td>{{ $c->student_name }}</td>
                <td>{{ $c->course }}</td>
                <td>{{ $c->issue_date }}</td>
                <td>@if($c->ipfs_cid)<a href="https://ipfs.io/ipfs/{{ $c->ipfs_cid }}" target="_blank">{{ $c->ipfs_cid }}</a>@endif</td>
                <td>{{ $c->blockchain_tx ?? '—' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
