<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Certificate') }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body { background: #f8f9fa; }
        .gap-2 { gap: .5rem; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">{{ config('app.name', 'Certificate') }}</a>
            <div class="d-flex">
                @if(Route::has('login'))
                    @auth
                        <a class="btn btn-outline-primary me-2" href="{{ route('issuer.dashboard') }}">Dashboard</a>
                        <form method="POST" action="{{ route('logout') }}" class="d-inline">
                            @csrf
                            <button class="btn btn-link">Logout</button>
                        </form>
                    @else
                        <a class="btn btn-outline-primary me-2" href="{{ route('login') }}">Login</a>
                        @if(Route::has('register'))
                            <a class="btn btn-primary" href="{{ route('register') }}">Register</a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
    </nav>

    <main class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <h1 class="mb-3">On-Chain Certificate Issuance</h1>
                <p class="lead mb-4">
                    Generate branded PDF certificates, pin them to IPFS, and record a
                    tamper-evident hash on the blockchain for anyone to verify.
                </p>

                <div class="d-flex justify-content-center gap-2 mb-3">
                    <a class="btn btn-lg btn-success me-2" href="{{ route('verify.form') }}">Verify a Certificate</a>
                    @auth
                        <a class="btn btn-lg btn-primary" href="{{ route('issuer.dashboard') }}">Go to Dashboard</a>
                    @else
                        <a class="btn btn-lg btn-primary" href="{{ route('login') }}">Issuer Login</a>
                    @endauth
                </div>

                <p class="text-muted">
                    Issuers can create certificates in a few clicks. Students and
                    employers can independently verify authenticity using the original
                    PDF or its SHA-256 hash.
                </p>
            </div>
        </div>
    </main>
</body>
</html>
