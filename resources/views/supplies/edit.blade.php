@extends('layouts.app')
@section('title', 'Edit Supply Item')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <h4 class="fw-bold mb-3">Edit Supply Item</h4>
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('supplies.update', $supply) }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Category</label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ $supply->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description (Item Name)</label>
                        <input type="text" name="description" value="{{ old('description', $supply->description) }}"
                               class="form-control @error('description') is-invalid @enderror" required>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Product Code</label>
                            <input type="text" name="product_code" value="{{ old('product_code', $supply->product_code) }}"
                                   class="form-control @error('product_code') is-invalid @enderror" required>
                            @error('product_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stock No. <span class="text-muted">(optional)</span></label>
                            <input type="text" name="stock_no" value="{{ old('stock_no', $supply->stock_no) }}"
                                   class="form-control @error('stock_no') is-invalid @enderror">
                            @error('stock_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Unit of Measure</label>
                            <input type="text" name="unit" value="{{ old('unit', $supply->unit) }}"
                                   class="form-control @error('unit') is-invalid @enderror" required>
                            @error('unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Unit Value (₱)</label>
                            <input type="number" step="0.01" name="unit_value" value="{{ old('unit_value', $supply->unit_value) }}"
                                   class="form-control @error('unit_value') is-invalid @enderror" min="0" required>
                            @error('unit_value') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Balance Per Card (Qty)</label>
                            <input type="number" name="balance_per_card" value="{{ old('balance_per_card', $supply->balance_per_card) }}"
                                   class="form-control @error('balance_per_card') is-invalid @enderror" min="0" required>
                            @error('balance_per_card') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">On Hand Per Count</label>
                            <input type="number" name="on_hand_per_count" value="{{ old('on_hand_per_count', $supply->on_hand_per_count) }}"
                                   class="form-control @error('on_hand_per_count') is-invalid @enderror" min="0">
                            @error('on_hand_per_count') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Minimum Stock <span class="text-muted">(alert)</span></label>
                            <input type="number" name="minimum_stock" value="{{ old('minimum_stock', $supply->minimum_stock) }}"
                                   class="form-control @error('minimum_stock') is-invalid @enderror" min="0" required>
                            @error('minimum_stock') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Expiration Date <span class="text-muted">(optional)</span></label>
                            <input type="date" name="expiration_date"
                                   value="{{ old('expiration_date', optional($supply->expiration_date)->format('Y-m-d')) }}"
                                   class="form-control @error('expiration_date') is-invalid @enderror">
                            @error('expiration_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks <span class="text-muted">(optional)</span></label>
                        <input type="text" name="remarks" value="{{ old('remarks', $supply->remarks) }}" class="form-control">
                    </div>
                    <button class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('supplies.index') }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection