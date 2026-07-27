@extends('layouts.app')
@section('title', 'Generate Supplies Report')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <h4 class="fw-bold mb-3">Generate Supplies Report</h4>
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <p class="text-muted small">Select a category to generate its Report of Physical Count of Inventories.</p>
                <form method="GET" action="{{ route('supplies.report') }}" target="_blank">
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
                    <a href="{{ route('supplies.index') }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection