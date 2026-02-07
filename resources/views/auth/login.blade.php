<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login · Smart Auction</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell app-auth">
    <main class="container d-flex align-items-center justify-content-center min-vh-100 py-5">
        <div class="card auth-card shadow-lg border-0">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <img src="{{ asset('Picsart_26-01-31_01-34-00-767.png') }}" alt="Smart Auction" width="44" height="44" class="rounded-circle">
                    <h1 class="h4 fw-semibold mb-0">Welcome back</h1>
                </div>
                <p class="text-muted mb-4">Sign in to access Smart Auction.</p>

                <form method="POST" action="{{ route('login.submit') }}" autocomplete="off">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="name">Username</label>
                        <input class="form-control" id="name" name="name" value="{{ old('name') }}" required autofocus>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-control" id="password" name="password" type="password" required>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <button class="btn btn-primary w-100" type="submit">Login</button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>
