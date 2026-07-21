@extends('layouts.app')
@section('title', 'Generate Property Report')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <h4 class="fw-bold mb-3">Generate Property Report</h4>
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <p class="text-muted small">Select a category and type to generate a printable inventory report.</p>
                <form method="GET" action="{{ route('property.report') }}" target="_blank">
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                            <option value="semi-expendable">Semi-expendable</option>
                            <option value="expendable">Expendable</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select category</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="btn btn-primary">Generate Report</button>
                    <a href="{{ route('property.index') }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection