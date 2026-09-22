@extends('layouts.app')
@section('title', 'Supplies')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Supply Items</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('supplies.report.options') }}" class="btn btn-outline-primary">Generate Report</a>
        <a href="{{ route('supplies.create') }}" class="btn btn-primary">+ Add Item</a>
    </div>
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
                    <th>Code</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th class="text-center">Quantity</th>
                    <th>Status</th>
                    <th>Expiry</th>
                    <th>Updated</th>
                    <th class="text-nowrap">Last Requested</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td><span class="text-muted small">{{ $item->product_code }}</span></td>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->category->name ?? '—' }}</td>
                    <td class="text-center text-nowrap">{{ $item->balance_per_card }} {{ $item->unit }}</td>

                    <td class="text-nowrap">
                        @switch($item->stockStatus())
                            @case('out')
                                <span class="badge" style="background: var(--danger);">Out of stock</span>
                                @break
                            @case('low')
                                <span class="badge bg-warning text-dark">Low</span>
                                @break
                            @default
                                <span class="badge bg-success">Available</span>
                        @endswitch
                    </td>

                    <td class="text-nowrap">
                        @if($item->isMedical() && $item->expiration_date)
                            @php $exp = $item->expiryStatus(); @endphp
                            <div class="small">{{ $item->expiration_date->format('M d, Y') }}</div>
                            @if($exp === 'expired')
                                <span class="badge bg-dark">Expired</span>
                            @elseif($exp === 'expiring')
                                <span class="badge bg-warning text-dark">Expiring soon</span>
                            @else
                                <span class="badge bg-success">Safe</span>
                            @endif
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td class="text-nowrap"><span class="small text-muted">{{ $item->updated_at?->format('M d, Y') }}</span></td>

                    <td class="text-nowrap">
                        @php $lastReq = $item->latestRequestLine?->request; @endphp
                        <span class="small">{{ $lastReq->personnel->name ?? '—' }}</span>
                    </td>

                    <td class="text-end text-nowrap">
                        <a href="{{ route('supplies.edit', $item) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form action="{{ route('supplies.destroy', $item) }}" method="POST" class="d-inline"
                              onsubmit="return confirm('Delete this item?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted py-3">No supply items found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $items->links() }}</div>
@endsection