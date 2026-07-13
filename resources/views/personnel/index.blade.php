@extends('layouts.app')
@section('title', 'Personnel')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Personnel</h4>
    <a href="{{ route('personnel.create') }}" class="btn btn-primary">+ Add Personnel</a>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-6">
        <input type="text" name="search" value="{{ request('search') }}"
               class="form-control" placeholder="Search by name...">
    </div>
    <div class="col-md-4">
        <select name="department" class="form-select" onchange="this.form.submit()">
            <option value="">All Departments</option>
            @foreach($departments as $dept)
                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                    {{ $dept }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <button class="btn btn-outline-secondary w-100">Search</button>
    </div>
</form>

<div class="row g-3">
    @forelse($personnel as $person)
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('personnel.show', $person) }}" class="text-decoration-none">
            <div class="card shadow-sm h-100 text-center">
                <div class="card-body">
                    @if($person->photo)
                        <img src="{{ asset('storage/' . $person->photo) }}" alt="{{ $person->name }}"
                             class="rounded-circle mb-2" style="width:72px;height:72px;object-fit:cover;">
                    @else
                        <div class="rounded-circle mb-2 d-inline-flex align-items-center justify-content-center"
                             style="width:72px;height:72px;background:#e8930c;color:#0b2126;font-weight:700;font-size:1.5rem;">
                            {{ strtoupper(substr($person->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="fw-semibold text-dark">{{ $person->name }}</div>
                    <div class="small text-muted">{{ $person->position ?? '—' }}</div>
                </div>
            </div>
        </a>
    </div>
    @empty
    <div class="col-12">
        <div class="card shadow-sm"><div class="card-body text-center text-muted py-4">No personnel yet.</div></div>
    </div>
    @endforelse
</div>

<div class="mt-3">{{ $personnel->links() }}</div>
@endsection