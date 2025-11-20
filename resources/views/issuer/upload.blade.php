@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Upload Certificate (PDF)</h1>
    <form method="POST" action="{{ route('issuer.upload') }}" enctype="multipart/form-data">
        @csrf
        <div class="form-group">
            <label>Student name</label>
            <input type="text" name="student_name" class="form-control" required />
        </div>
        <div class="form-group">
            <label>Student wallet (optional)</label>
            <input type="text" name="student_wallet" class="form-control" />
        </div>
        <div class="form-group">
            <label>Course</label>
            <input type="text" name="course" class="form-control" />
        </div>
        <div class="form-group">
            <label>Issue date</label>
            <input type="date" name="issue_date" class="form-control" />
        </div>
        <div class="form-group">
            <label>PDF</label>
            <input type="file" name="pdf"  required />
        </div>
        <button class="btn btn-primary">Upload</button>
    </form>
</div>
@endsection
