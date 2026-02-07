<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Smart Auction</title>
    <link rel="icon" href="{{ asset('Picsart_26-01-31_01-34-00-767.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-shell">
    @php
        $currentAuction = app(\App\Support\AuctionContext::class)->current(request());
    @endphp
    <nav class="navbar navbar-expand-lg navbar-dark app-nav">
        <div class="container-fluid px-3 px-lg-4">
            <a class="navbar-brand fw-semibold d-flex align-items-center gap-2" href="{{ route('home') }}">
                <img src="{{ asset('Picsart_26-01-31_01-34-00-767.png') }}" alt="Smart Auction" width="36" height="36" class="rounded-circle">
                <span>Smart Auction</span>
            </a>
            <div class="app-nav-buttons d-flex flex-wrap align-items-center gap-2 me-auto">
                <a class="btn btn-light btn-sm {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Home</a>
                <a class="btn btn-light btn-sm {{ request()->routeIs('subjects.*') ? 'active' : '' }}" href="{{ route('subjects.index') }}">Sellers</a>
                <a class="btn btn-light btn-sm {{ request()->routeIs('auction.*') ? 'active' : '' }}" href="{{ route('auction.index') }}">Table</a>
                <a class="btn btn-light btn-sm {{ request()->routeIs('receipt') ? 'active' : '' }}" href="{{ route('receipt') }}">Receipt</a>
                <a class="btn btn-light btn-sm {{ request()->routeIs('after') ? 'active' : '' }}" href="{{ route('after') }}">After</a>
                <a class="btn btn-light btn-sm {{ request()->routeIs('report') ? 'active' : '' }}" href="{{ route('report') }}">Report</a>
                <a class="btn btn-light btn-sm {{ request()->routeIs('analysis') ? 'active' : '' }}" href="{{ route('analysis') }}">Analysis</a>
            </div>
            <div class="app-nav-actions d-flex flex-wrap align-items-center gap-2">
                <span class="badge bg-light text-dark">
                    Auction: {{ $currentAuction?->code ?? 'none' }}
                </span>
                <a class="btn btn-outline-light btn-sm" href="{{ route('database.export') }}">Export DB</a>
                <form class="d-inline-flex" method="POST" action="{{ route('database.import') }}" enctype="multipart/form-data" data-import-form>
                    @csrf
                    <input class="d-none" type="file" name="database_file" accept=".sqlite,.db,.sqlite3" data-import-input>
                    <button class="btn btn-outline-light btn-sm" type="button" data-import-trigger>Import DB</button>
                </form>
                @auth
                <form method="POST" action="{{ route('logout') }}" class="ms-lg-2">
                    @csrf
                    <button class="btn btn-outline-light btn-sm" type="submit">Logout</button>
                </form>
                @endauth
            </div>
        </div>
    </nav>

    <main class="container-fluid px-3 px-lg-4 py-4" autocomplete="off">
        @if (session('status'))
            <div class="alert alert-success shadow-sm" role="alert">
                {{ session('status') }}
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
