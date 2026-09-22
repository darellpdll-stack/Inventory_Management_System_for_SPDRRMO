@extends('layouts.app')
@section('title', 'Reports')

@section('content')
<h4 class="fw-bold mb-3">Reports</h4>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-1"><i class="bi bi-box-seam me-1"></i> Supplies Report</h6>
                <p class="text-muted small mb-3">Report of Physical Count of Inventories, per supply category.</p>
                <a href="{{ route('supplies.report.options') }}" class="btn btn-primary btn-sm">Generate</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-1"><i class="bi bi-hdd-stack me-1"></i> Property Report</h6>
                <p class="text-muted small mb-3">Inventory of semi-expendable or expendable property, per category.</p>
                <a href="{{ route('property.report.options') }}" class="btn btn-primary btn-sm">Generate</a>
            </div>
        </div>
    </div>
</div>

@if(Auth::user()->role === 'admin')
<div class="card shadow-sm mt-3">
    <div class="card-body p-4 d-flex justify-content-between align-items-center">
        <div>
            <h6 class="fw-bold mb-1"><i class="bi bi-gear me-1"></i> Report Settings</h6>
            <p class="text-muted small mb-0">Officer names and titles that appear on printed reports.</p>
        </div>
        <a href="{{ route('settings.edit') }}" class="btn btn-outline-primary btn-sm">Edit</a>
    </div>
</div>
@endif
@endsection