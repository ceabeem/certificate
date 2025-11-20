@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Your Certificates</h1>
    <table class="table">
        <thead><tr><th>ID</th><th>Student</th><th>Course</th><th>Issue Date</th><th>IPFS</th><th>Actions</th></tr></thead>
        <tbody>
        @foreach($certs as $c)
            <tr>
                <td>{{ $c->id }}</td>
                <td>{{ $c->student_name }}</td>
                <td>{{ $c->course }}</td>
                <td>{{ $c->issue_date }}</td>
                <td>@if($c->ipfs_cid)<a href="https://ipfs.io/ipfs/{{ $c->ipfs_cid }}" target="_blank">{{ $c->ipfs_cid }}</a>@endif</td>
                <td>
                    <a class="btn btn-sm btn-primary" href="{{ route('student.certificates.show', $c->id) }}">View</a>
                    @if($c->pdf_path)
                        <a class="btn btn-sm btn-secondary" href="{{ route('student.certificates.download', $c->id) }}">Download</a>
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection
