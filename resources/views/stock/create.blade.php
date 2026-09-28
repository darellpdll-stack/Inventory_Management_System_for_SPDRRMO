@extends('layouts.app')
@section('title', 'Add Stock')

@section('content')
<div class="page-head">
    <div>
        <div class="page-title">Add Stock</div>
        <div class="page-sub">{{ $supply->description }} · {{ $supply->category->name ?? '—' }}</div>
    </div>
    <a href="{{ route('supplies.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back to Supplies</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row g-3">
    <div class="col-lg-5">
        <div class="dpanel">
            <div class="dpanel-title mb-3">New Delivery</div>

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <form method="POST" action="{{ route('stock.store', $supply) }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Date Received</label>
                    <input type="date" name="date_received" class="form-control"
                           value="{{ old('date_received', now()->toDateString()) }}"
                           max="{{ now()->toDateString() }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Quantity Received</label>
                    <input type="number" name="quantity" class="form-control" min="1"
                           value="{{ old('quantity') }}" required>
                    <div class="form-text">Current balance: {{ $supply->balance_per_card }} {{ $supply->unit }}</div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Delivered by <span class="text-muted">(optional)</span></label>
                    <input type="text" name="delivered_by" class="form-control"
                           value="{{ old('delivered_by') }}" placeholder="Supplier or delivery person">
                </div>
                <div class="mb-3">
                    <label class="form-label">Reference No. <span class="text-muted">(optional)</span></label>
                    <input type="text" name="reference_no" class="form-control"
                           value="{{ old('reference_no') }}" placeholder="DR or PO number">
                </div>
                <div class="mb-3">
                    <label class="form-label">Batch Expiry <span class="text-muted">(optional)</span></label>
                    <input type="date" name="expiration_date" class="form-control"
                           value="{{ old('expiration_date') }}" min="{{ now()->addDay()->toDateString() }}">
                </div>
                <div class="mb-4">
                    <label class="form-label">Remarks <span class="text-muted">(optional)</span></label>
                    <input type="text" name="remarks" class="form-control" value="{{ old('remarks') }}">
                </div>
                <button class="btn btn-primary w-100">Add Stock</button>
            </form>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="dpanel">
            <div class="dpanel-head">
                <div class="dpanel-title">Receiving History</div>
                <span class="activity-date">{{ $entries->count() }} {{ Str::plural('delivery', $entries->count()) }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-nowrap">Date</th>
                            <th class="text-center">Qty</th>
                            <th>Delivered by</th>
                            <th class="text-nowrap">Ref. No.</th>
                            <th class="text-nowrap">Batch Expiry</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                        <tr>
                            <td class="text-nowrap">{{ $entry->date_received->format('M d, Y') }}</td>
                            <td class="text-center">+{{ $entry->quantity }}</td>
                            <td>{{ $entry->delivered_by ?? '—' }}</td>
                            <td class="text-muted small">{{ $entry->reference_no ?? '—' }}</td>
                            <td class="text-muted small text-nowrap">{{ $entry->expiration_date?->format('M d, Y') ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="dash-empty">No deliveries recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection