@extends('layouts.app')
@section('title', 'Edit Property')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <h4 class="fw-bold mb-3">Edit Property Item</h4>
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('property.update', $property) }}">
                    @csrf @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select" required>
                                <option value="semi-expendable" {{ $property->type == 'semi-expendable' ? 'selected' : '' }}>Semi-expendable</option>
                                <option value="expendable" {{ $property->type == 'expendable' ? 'selected' : '' }}>Expendable</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $property->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Accountable Person <span class="text-muted">(optional)</span></label>
                        <select name="personnel_id" class="form-select">
                            <option value="">— None —</option>
                            @foreach($personnel as $person)
                                <option value="{{ $person->id }}" {{ $property->personnel_id == $person->id ? 'selected' : '' }}>{{ $person->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" value="{{ old('description', $property->description) }}"
                               class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Starting Property No.</label>
                            <input type="text" name="property_no" value="{{ old('property_no', $property->property_no) }}"
                                   class="form-control" placeholder="e.g. 24-05-0090" required>
                            <div class="form-text">If quantity > 1, numbers count up from here.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Quantity</label>
                            <input type="number" name="quantity" value="{{ old('quantity', $property->quantity) }}"
                                   class="form-control" min="1" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unit of Measure</label>
                            <input type="text" name="unit" value="{{ old('unit', $property->unit) }}" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unit Value (₱) <span class="text-muted">(optional)</span></label>
                            <input type="number" step="0.01" name="unit_value" value="{{ old('unit_value', $property->unit_value) }}"
                                   class="form-control" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks <span class="text-muted">(optional)</span></label>
                        <input type="text" name="remarks" value="{{ old('remarks', $property->remarks) }}" class="form-control">
                    </div>
                    <button class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('property.index') }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection