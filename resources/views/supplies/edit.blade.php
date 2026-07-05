@extends('layouts.app')
@section('title', 'Edit Supply Item')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <h4 class="fw-bold mb-3">Edit Supply Item</h4>
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('supplies.update', $supply) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $supply->category_id == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Item Name</label>
                        <input type="text" name="item_name" value="{{ old('item_name', $supply->item_name) }}"
                               class="form-control @error('item_name') is-invalid @enderror" required>
                        @error('item_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Current Stock</label>
                            <input type="number" name="current_stock" value="{{ old('current_stock', $supply->current_stock) }}"
                                   class="form-control @error('current_stock') is-invalid @enderror" min="0" required>
                            @error('current_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unit</label>
                            <input type="text" name="unit" value="{{ old('unit', $supply->unit) }}"
                                   class="form-control @error('unit') is-invalid @enderror" required>
                            @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Minimum Stock <span class="text-muted">(alert level)</span></label>
                        <input type="number" name="minimum_stock" value="{{ old('minimum_stock', $supply->minimum_stock) }}"
                               class="form-control @error('minimum_stock') is-invalid @enderror" min="0" required>
                        @error('minimum_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                    <label class="form-label">Expiration Date <span class="text-muted">(leave blank if it doesn't expire)</span></label>
                    <input type="date" name="expiration_date"
                        value="{{ old('expiration_date', optional($supply->expiration_date)->format('Y-m-d')) }}"
                        class="form-control @error('expiration_date') is-invalid @enderror">
                    @error('expiration_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                    <button class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('supplies.index') }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection