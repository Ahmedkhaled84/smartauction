@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
                    <div>
                        <h2 class="h4 fw-semibold mb-1">After Page</h2>
                        <p class="text-muted mb-0">Placeholder page – summary after the auction ends.</p>
                    </div>
                    <span class="badge bg-primary-subtle text-primary">Coming Soon</span>
                </div>
                <div class="row g-4">
                    <div class="col-12 col-lg-6">
                        <div class="border rounded-3 p-4 bg-light">
                            <div class="text-muted">Summary block preview</div>
                            <div class="mt-3">Totals, notes, and closing actions will appear here.</div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="border rounded-3 p-4">
                            <div class="text-muted">Actions</div>
                            <div class="mt-3 d-grid gap-2">
                                <button class="btn btn-outline-secondary" type="button" disabled>Download summary</button>
                                <button class="btn btn-outline-secondary" type="button" disabled>Send report</button>
                                <button class="btn btn-outline-secondary" type="button" disabled>Archive auction</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
