@extends('layouts.app')
@section('title', 'Add Personnel')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <h4 class="fw-bold mb-3">Add Personnel</h4>
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('personnel.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                <label class="form-label">Photo <span class="text-muted">(optional)</span></label>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                    class="form-control @error('photo') is-invalid @enderror">
                <div class="form-text">Accepted: JPG, PNG, or WEBP · Max 2MB</div>
                @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" value="{{ old('name') }}"
                                   class="form-control @error('name') is-invalid @enderror" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Position / Designation</label>
                            <input type="text" name="position" value="{{ old('position') }}"
                                   class="form-control" placeholder="e.g. Administrative Officer IV">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employee ID</label>
                            <input type="text" name="employee_id" value="{{ old('employee_id') }}" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact_number" value="{{ old('contact_number') }}" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department / Office</label>
                            <input type="text" name="department" value="{{ old('department') }}" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" value="{{ old('address') }}" class="form-control">
                        </div>
                    </div>
                    <button class="btn btn-primary">Save Personnel</button>
                    <a href="{{ route('personnel.index') }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection