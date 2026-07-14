@extends('layouts.app')
@section('title', 'Withdrawals')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Withdrawals</h4>
    <a href="{{ route('withdrawals.create') }}" class="btn btn-primary">+ New Withdrawal</a>
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
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search person or item...">
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
                    <th>No.</th>
                    <th>Unit &amp; Quantity</th>
                    <th>Item Description</th>
                    <th>Withdrawn By</th>
                    <th>Date Withdrawn</th>
                    <th>Date Returned</th>
                    <th>Remark</th>
                </tr>
            </thead>
            <tbody>
                @php $rowNo = 1; @endphp
                @forelse($withdrawals as $w)
                    @foreach($w->items as $line)
                    <tr>
                        <td>{{ $rowNo++ }}</td>
                        <td>{{ $line->quantity }} {{ $line->supplyItem->unit ?? '' }}</td>
                        <td>{{ $line->supplyItem->description ?? '—' }}</td>
                        <td>{{ $w->withdrawn_by }}</td>
                        <td>{{ $w->date_withdrawn->format('M d, Y') }}</td>
                        <td>{{ $w->date_returned ? $w->date_returned->format('M d, Y') : '—' }}</td>
                        <td>{{ $w->remark ?? '—' }}</td>
                    </tr>
                    @endforeach
                @empty
                    <tr><td colspan="7" class="text-center text-muted py-3">No withdrawals recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $withdrawals->links() }}</div>
@endsection