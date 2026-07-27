@extends('layouts.app')
@section('title', 'Report Settings')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <h4 class="fw-bold mb-3">Report Settings</h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <p class="text-muted small mb-4">These appear on generated reports. Update them when the accountable officer changes.</p>
                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Accountable Officer — Name</label>
                        <input type="text" name="officer_name" value="{{ old('officer_name', $settings['officer_name']) }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Accountable Officer — Title / Position</label>
                        <input type="text" name="officer_title" value="{{ old('officer_title', $settings['officer_title']) }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Office Name</label>
                        <input type="text" name="office_name" value="{{ old('office_name', $settings['office_name']) }}" class="form-control" required>
                    </div>
                    <hr class="my-4">
                    <h6 class="fw-bold mb-3">Property Report Signatories</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prepared by — Name</label>
                            <input type="text" name="property_prepared_name" value="{{ old('property_prepared_name', $settings['property_prepared_name']) }}" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prepared by — Title</label>
                            <input type="text" name="property_prepared_title" value="{{ old('property_prepared_title', $settings['property_prepared_title']) }}" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Approved by — Name</label>
                            <input type="text" name="property_approved_name" value="{{ old('property_approved_name', $settings['property_approved_name']) }}" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Approved by — Title</label>
                            <input type="text" name="property_approved_title" value="{{ old('property_approved_title', $settings['property_approved_title']) }}" class="form-control" required>
                        </div>
                    </div>
                    <button class="btn btn-primary">Save Settings</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection