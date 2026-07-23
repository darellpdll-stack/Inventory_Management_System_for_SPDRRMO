@extends('layouts.app')
@section('title', 'Personnel Profile')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <a href="{{ route('personnel.index') }}" class="btn btn-sm btn-light mb-3">← Back to personnel</a>

        {{-- Profile info block --}}
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="d-flex align-items-start gap-3">
                    @if($person->photo)
                        <img src="{{ asset('storage/' . $person->photo) }}" alt="{{ $person->name }}"
                             class="rounded-circle" style="width:80px;height:80px;object-fit:cover;">
                    @else
                        <div class="rounded-circle d-inline-flex align-items-center justify-content-center"
                             style="width:80px;height:80px;background:#eea316;color:#082c5c;font-weight:700;font-size:1.8rem;">
                            {{ strtoupper(substr($person->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="flex-grow-1">
                        <h4 class="fw-bold mb-0">{{ $person->name }}</h4>
                        <div class="text-muted mb-2">{{ $person->position ?? '—' }}</div>
                        <div class="row small">
                            <div class="col-md-6 mb-1"><strong>ID:</strong> {{ $person->employee_id ?? '—' }}</div>
                            <div class="col-md-6 mb-1"><strong>Contact:</strong> {{ $person->contact_number ?? '—' }}</div>
                            <div class="col-md-6 mb-1"><strong>Department:</strong> {{ $person->department ?? '—' }}</div>
                            <div class="col-md-6 mb-1"><strong>Address:</strong> {{ $person->address ?? '—' }}</div>
                        </div>
                    </div>
                    @if(Auth::user()->role === 'admin')
                    <div>
                        <a href="{{ route('personnel.edit', $person) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('personnel.destroy', $person) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this personnel?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Accountable property (grouped, with category filter) --}}
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Accountable Property</h6>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-secondary">{{ $grouped->sum('qty') }} unit(s)</span>
                        @if($personCategories->count() > 0)
                        <form method="GET" class="d-inline">
                            <select name="category" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                @foreach($personCategories as $cat)
                                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </form>
                        @endif
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Description</th>
                                <th>Category</th>
                                <th class="text-center">Qty</th>
                                <th>Property Nos.</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($grouped as $g)
                            <tr>
                                <td>{{ $g->description }}</td>
                                <td>{{ $g->category }}</td>
                                <td class="text-center">{{ $g->qty }} {{ $g->unit }}</td>
                                <td class="text-muted small">{{ $g->range }}</td>
                                <td>
                                    @if($g->type === 'semi-expendable')
                                        <span class="badge bg-primary">Semi-exp</span>
                                    @else
                                        <span class="badge bg-secondary">Expendable</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No property assigned to this person.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection