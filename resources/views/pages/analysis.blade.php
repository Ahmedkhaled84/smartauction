@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-xl-10">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
                    <div>
                        <h2 class="h4 fw-semibold mb-1">Analysis</h2>
                        <p class="text-muted mb-0">Placeholder page – analytics and charts.</p>
                    </div>
                    <span class="badge bg-primary-subtle text-primary">Coming Soon</span>
                </div>
                <div class="row g-4">
                    <div class="col-12 col-lg-6">
                        <div class="border rounded-3 p-4 bg-light">
                            <div class="text-muted">Chart placeholder</div>
                            <div class="mt-3" style="height: 180px; background: repeating-linear-gradient(135deg, rgba(31,75,153,0.08), rgba(31,75,153,0.08) 10px, rgba(255,255,255,0.6) 10px, rgba(255,255,255,0.6) 20px); border-radius: 12px;"></div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <div class="border rounded-3 p-4 bg-light">
                            <div class="text-muted">Chart placeholder</div>
                            <div class="mt-3" style="height: 180px; background: repeating-linear-gradient(45deg, rgba(31,75,153,0.08), rgba(31,75,153,0.08) 10px, rgba(255,255,255,0.6) 10px, rgba(255,255,255,0.6) 20px); border-radius: 12px;"></div>
                        </div>
                    </div>
                </div>
                <div class="border rounded-3 p-4 mt-4">
                    <div class="text-muted">Insights preview</div>
                    <div class="mt-3">KPIs and insights will be added here.</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
