@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Login</h1>
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required />
        </div>
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required />
        </div>
        <div class="form-group">
            <label><input type="checkbox" name="remember" /> Remember me</label>
        </div>
        <button class="btn btn-primary">Login</button>
    </form>
    <p>Don't have an account? <a href="{{ route('register') }}">Register</a></p>
</div>
@endsection
