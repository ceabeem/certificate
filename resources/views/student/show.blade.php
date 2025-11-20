@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Certificate #{{ $cert->id }}</h1>
    <p>Student: {{ $cert->student_name }}</p>
    <p>Course: {{ $cert->course }}</p>
    <p>Issue date: {{ $cert->issue_date }}</p>
    <p>IPFS: @if($cert->ipfs_cid)<a href="https://ipfs.io/ipfs/{{ $cert->ipfs_cid }}" target="_blank">{{ $cert->ipfs_cid }}</a>@endif</p>
    <p>Blockchain tx: {{ $cert->blockchain_tx ?? '—' }}</p>
    @if($cert->pdf_path)
        <a class="btn btn-primary" href="{{ route('student.certificates.download', $cert->id) }}">Download PDF</a>
    @endif
</div>
@endsection
