@extends('layouts.app')
@section('title', 'Property')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Property Inventory</h4>
    <div>
        <a href="{{ route('property.report.options') }}" class="btn btn-outline-primary">Generate Report</a>
        <a href="{{ route('property.create') }}" class="btn btn-primary">+ Add Property</a>
    </div>
</div>
<form method="GET" class="row g-2 mb-3">
    <div class="col-md-3">
        <select name="type" class="form-select" onchange="this.form.submit()">
            <option value="">All Types</option>
            <option value="semi-expendable" {{ request('type') == 'semi-expendable' ? 'selected' : '' }}>Semi-expendable</option>
            <option value="expendable" {{ request('type') == 'expendable' ? 'selected' : '' }}>Expendable</option>
        </select>
    </div>
    <div class="col-md-3">
        <select name="category" class="form-select" onchange="this.form.submit()">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search description or property no...">
    </div>
    <div class="col-md-2">
        <button class="btn btn-outline-secondary w-100">Search</button>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Accountable Person</th>
                    <th>Description</th>
                    <th>Property No.</th>
                    <th>Unit</th>
                    <th class="text-center">On Hand</th>
                    <th>Type</th>
                    <th>Remarks</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
    @forelse($items as $item)
    <tr>
        <td>{{ $item->personnel->name ?? '—' }}</td>
        <td>{{ $item->description }}</td>
        <td class="text-muted small">{{ $item->propertyNoRange() }}</td>
        <td>{{ $item->unit }}</td>
        <td class="text-center">{{ $item->on_hand_per_count }}</td>
        <td>
            @if($item->type === 'semi-expendable')
                <span class="badge bg-primary">Semi-exp</span>
            @else
                <span class="badge bg-secondary">Expendable</span>
            @endif
        </td>
        <td>{{ $item->remarks ?? '—' }}</td>
        <td class="text-end">
            @if(Auth::user()->role === 'admin')
                <a href="{{ route('property.edit', $item) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                <form action="{{ route('property.destroy', $item) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Delete this property item?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                </form>
            @else
                <span class="text-muted small">—</span>
            @endif
        </td>
    </tr>
    @empty
    <tr><td colspan="8" class="text-center text-muted py-3">No property items found.</td></tr>
    @endforelse
</tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $items->links() }}</div>
@endsection