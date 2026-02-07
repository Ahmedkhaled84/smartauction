@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-lg-5">
                <div class="row align-items-center g-4">
                    <div class="col-12 col-lg-6">
                        <h1 class="display-6 fw-semibold mb-3">Welcome to Smart Auction</h1>
                        <p class="text-muted mb-4">
                            Manage sellers, track items, and keep your auction table in sync.
                            Use the navigation bar to jump to Sellers or the Smart Auction table.
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            <a class="btn btn-primary" href="{{ route('subjects.index') }}">Go to Sellers</a>
                            <a class="btn btn-outline-primary" href="{{ route('auction.index') }}">Open Table</a>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="terminal-shell">
                            <div class="terminal-header">
                                <span class="dot dot-red"></span>
                                <span class="dot dot-yellow"></span>
                                <span class="dot dot-green"></span>
                                <span class="terminal-title">smart-auction</span>
                            </div>
                            <div class="terminal-body">
                                <div class="terminal-line"><span class="prompt">$</span> status</div>
                                <div class="terminal-line muted">Sellers: ready</div>
                                <div class="terminal-line muted">Table: online</div>
                                @if ($currentAuction)
                                    <div class="terminal-line success">Current auction: {{ $currentAuction->code }}</div>
                                @else
                                    <div class="terminal-line muted">Current auction: none</div>
                                @endif
                                <div class="terminal-output" data-terminal-output></div>
                                <form class="terminal-input-row" data-terminal-form onsubmit="return false;">
                                    <span class="prompt">$</span>
                                    <input class="terminal-input" data-terminal-input type="text" placeholder="Type a command..." autocomplete="off">
                                </form>
                                <div class="terminal-hint">Commands: new auction, load &lt;code&gt;, show codes</div>
                                <form class="terminal-new-auction d-none" data-new-auction-form onsubmit="return false;">
                                    <div class="terminal-line"><span class="prompt">$</span> start date</div>
                                    <input class="terminal-date" type="date" data-start-date required>
                                    <div class="terminal-line"><span class="prompt">$</span> end date</div>
                                    <input class="terminal-date" type="date" data-end-date required>
                                    <button class="btn btn-sm btn-primary mt-2" type="submit">Create auction</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
