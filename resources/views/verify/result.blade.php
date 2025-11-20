@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Verification Result</h1>

    <div class="card">
        <div class="card-body">
            <h3>@if($status) <span style="color:green">{{ $message }}</span> @else <span style="color:red">{{ $message }}</span> @endif</h3>
            @if(! empty($certificate))
                <p>Student: {{ $certificate->student_name }}</p>
                <p>Course: {{ $certificate->course }}</p>
                <p>Issue date: {{ $certificate->issue_date }}</p>
                <p>IPFS: @if($certificate->ipfs_cid)<a href="https://ipfs.io/ipfs/{{ $certificate->ipfs_cid }}" target="_blank">{{ $certificate->ipfs_cid }}</a>@endif</p>
                <p>Blockchain tx: {{ $certificate->blockchain_tx ?? '—' }}</p>
            @endif
        </div>
    </div>
</div>
@endsection
