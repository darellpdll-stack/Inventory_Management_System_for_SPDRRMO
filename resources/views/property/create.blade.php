@extends('layouts.app')
@section('title', 'Add Property')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <h4 class="fw-bold mb-3">Add Property Item</h4>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('property.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select" required>
                                <option value="">Select type</option>
                                <option value="semi-expendable" {{ old('type') == 'semi-expendable' ? 'selected' : '' }}>Semi-expendable</option>
                                <option value="expendable" {{ old('type') == 'expendable' ? 'selected' : '' }}>Expendable</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Accountable Person <span class="text-muted">(optional)</span></label>
                        <select name="personnel_id" class="form-select">
                            <option value="">— None —</option>
                            @foreach($personnel as $person)
                                <option value="{{ $person->id }}" {{ old('personnel_id') == $person->id ? 'selected' : '' }}>{{ $person->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" value="{{ old('description') }}" class="form-control" required>
                    </div>
                        <<div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Starting Property No.</label>
                            <input type="text" name="property_no" value="{{ old('property_no') }}"
                                class="form-control" placeholder="e.g. 24-05-0090" required>
                            <div class="form-text">If quantity > 1, numbers count up from here.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" value="{{ old('quantity', 1) }}"
                                class="form-control" min="1" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unit of Measure</label>
                            <input type="text" name="unit" value="{{ old('unit', 'unit') }}" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unit Value (₱) <span class="text-muted">(optional)</span></label>
                            <input type="number" step="0.01" name="unit_value" value="{{ old('unit_value') }}" class="form-control" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks <span class="text-muted">(optional)</span></label>
                        <input type="text" name="remarks" value="{{ old('remarks') }}" class="form-control">
                    </div>

                    <button class="btn btn-primary">Save</button>
                    <a href="{{ route('property.index') }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection