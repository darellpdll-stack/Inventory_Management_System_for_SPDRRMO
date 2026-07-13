@extends('layouts.app')
@section('title', 'Edit Personnel')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <h4 class="fw-bold mb-3">Edit Personnel</h4>
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('personnel.update', $person) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Photo</label>
                        @if($person->photo)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $person->photo) }}" alt="{{ $person->name }}"
                                     class="rounded" style="width:64px;height:64px;object-fit:cover;">
                                <span class="small text-muted ms-2">Current photo — upload a new one to replace it</span>
                            </div>
                        @endif
                        <input type="file" name="photo" accept="image/*"
                               class="form-control @error('photo') is-invalid @enderror">
                        @error('photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" value="{{ old('name', $person->name) }}"
                                   class="form-control @error('name') is-invalid @enderror" required>
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Position / Designation</label>
                            <input type="text" name="position" value="{{ old('position', $person->position) }}" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employee ID</label>
                            <input type="text" name="employee_id" value="{{ old('employee_id', $person->employee_id) }}" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Contact Number</label>
                            <input type="text" name="contact_number" value="{{ old('contact_number', $person->contact_number) }}" class="form-control">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department / Office</label>
                            <input type="text" name="department" value="{{ old('department', $person->department) }}" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" value="{{ old('address', $person->address) }}" class="form-control">
                        </div>
                    </div>
                    <button class="btn btn-primary">Save Changes</button>
                    <a href="{{ route('personnel.index') }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection