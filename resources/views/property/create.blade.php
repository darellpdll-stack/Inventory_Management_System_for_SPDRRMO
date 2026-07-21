@extends('layouts.app')
@section('title', 'Add Property')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <h4 class="fw-bold mb-3">Add Property Item</h4>
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('property.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                <option value="">Select type</option>
                                <option value="semi-expendable" {{ old('type') == 'semi-expendable' ? 'selected' : '' }}>Semi-expendable</option>
                                <option value="expendable" {{ old('type') == 'expendable' ? 'selected' : '' }}>Expendable</option>
                            </select>
                            @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">Select category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Accountable Person <span class="text-muted">(optional)</span></label>
                        <select name="personnel_id" class="form-select @error('personnel_id') is-invalid @enderror">
                            <option value="">— None —</option>
                            @foreach($personnel as $person)
                                <option value="{{ $person->id }}" {{ old('personnel_id') == $person->id ? 'selected' : '' }}>{{ $person->name }}</option>
                            @endforeach
                        </select>
                        @error('personnel_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" value="{{ old('description') }}"
                               class="form-control @error('description') is-invalid @enderror" required>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Property No. <span class="text-muted">(optional)</span></label>
                            <input type="text" name="property_no" value="{{ old('property_no') }}"
                                   class="form-control @error('property_no') is-invalid @enderror" placeholder="e.g. 17-1806">
                            @error('property_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Unit of Measure</label>
                            <input type="text" name="unit" value="{{ old('unit', 'unit') }}"
                                   class="form-control @error('unit') is-invalid @enderror" required>
                            @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">On Hand (Qty)</label>
                            <input type="number" name="on_hand_per_count" value="{{ old('on_hand_per_count', 1) }}"
                                   class="form-control @error('on_hand_per_count') is-invalid @enderror" min="0" required>
                            @error('on_hand_per_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unit Value (₱) <span class="text-muted">(optional)</span></label>
                            <input type="number" step="0.01" name="unit_value" value="{{ old('unit_value') }}"
                                   class="form-control @error('unit_value') is-invalid @enderror" min="0">
                            @error('unit_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Remarks <span class="text-muted">(optional)</span></label>
                            <input type="text" name="remarks" value="{{ old('remarks') }}" class="form-control">
                        </div>
                    </div>
                    <button class="btn btn-primary">Add Property</button>
                    <a href="{{ route('property.index') }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection