@extends('layouts.app')
@section('title', 'Personnel Profile')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <a href="{{ route('personnel.index') }}" class="btn btn-sm btn-light mb-3">← Back to personnel</a>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    @if($person->photo)
                        <img src="{{ asset('storage/' . $person->photo) }}" alt="{{ $person->name }}"
                             class="rounded-circle" style="width:80px;height:80px;object-fit:cover;">
                    @else
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center"
                             style="width:80px;height:80px;background:#e8930c;color:#0b2126;font-weight:700;font-size:1.8rem;">
                            {{ strtoupper(substr($person->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex-grow-1">
                        <h4 class="fw-bold mb-0">{{ $person->name }}</h4>
                        <div class="text-muted mb-2">{{ $person->position ?? '—' }}</div>
                        <div class="row small">
                            <div class="col-md-6">ID: {{ $person->employee_id ?? '—' }}</div>
                            <div class="col-md-6">Contact: {{ $person->contact_number ?? '—' }}</div>
                            <div class="col-md-6">Department: {{ $person->department ?? '—' }}</div>
                            <div class="col-md-6">Address: {{ $person->address ?? '—' }}</div>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('personnel.edit', $person) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('personnel.destroy', $person) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this personnel?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Assigned items — structure ready, to be wired to Property later --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold mb-0">Assigned Items</h6>
                    <select class="form-select form-select-sm" style="width:auto;" disabled>
                        <option>All categories</option>
                    </select>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Description</th>
                                <th>Property no.</th>
                                <th>Unit of measure</th>
                                <th class="text-center">On hand per count</th>
                                <th>Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td colspan="5" class="text-center text-muted py-3">
                                No assigned items yet.
                            </td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection