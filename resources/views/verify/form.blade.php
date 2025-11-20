@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Verify Certificate</h1>
    <form method="POST" action="{{ route('verify.upload') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label>Upload PDF</label>
            <input type="file" name="pdf" required />
        </div>
        <button class="btn btn-primary">Verify Upload</button>
    </form>

    <hr />
    <form method="POST" action="{{ route('verify.hash') }}">
        @csrf
        <div class="form-group">
            <label>Or paste SHA-256 hash</label>
            <input type="text" name="hash" class="form-control" />
        </div>
        <button class="btn btn-secondary">Verify Hash</button>
    </form>
</div>
@endsection
