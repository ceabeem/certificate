@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Register</h1>
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('register.attempt') }}">
        @csrf
        <div class="form-group">
            <label>Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required />
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required />
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required />
        </div>
        <div class="form-group">
            <label>Confirm Password</label>
            <input type="password" name="password_confirmation" class="form-control" required />
        </div>
        <button class="btn btn-primary">Register</button>
    </form>
</div>
@endsection
