@extends('layouts.app')
@section('title', 'Supplies')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Supply Items</h4>
    <a href="{{ route('supplies.create') }}" class="btn btn-primary">+ Add Item</a>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <select name="category" class="form-select" onchange="this.form.submit()">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-5">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search description or product code...">
    </div>
    <div class="col-md-3">
        <button class="btn btn-outline-secondary w-100">Search</button>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Description</th>
                    <th>Product Code</th>
                    <th>Category</th>
                    <th>Unit</th>
                    <th class="text-center">Balance</th>
                    <th>Expiry</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td><span class="text-muted small">{{ $item->product_code }}</span></td>
                    <td>{{ $item->category->name ?? '—' }}</td>
                    <td>{{ $item->unit }}</td>
                    <td class="text-center">
                        {{ $item->balance_per_card }}
                        @if($item->isLowStock())
                            <span class="badge bg-danger ms-1">Low</span>
                        @endif
                    </td>
                    @php $exp = $item->expiryStatus(); @endphp
                    <td>
                        @if($item->expiration_date)
                            {{ $item->expiration_date->format('M d, Y') }}
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>
                    <td>
                        @if($exp === 'none')
                            <span class="text-muted">—</span>
                        @elseif($exp === 'expired')
                            <span class="badge bg-dark">Expired</span>
                        @elseif($exp === 'expiring')
                            <span class="badge bg-warning text-dark">Expiring Soon</span>
                        @else
                            <span class="badge bg-success">Safe</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <a href="{{ route('supplies.edit', $item) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('supplies.destroy', $item) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this item?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted py-3">No supply items found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $items->links() }}</div>
@endsection