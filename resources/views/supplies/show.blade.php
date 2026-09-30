@extends('layouts.app')
@section('title', $supply->description)

@section('content')
<div class="page-head">
    <div>
        <div class="page-title">{{ $supply->description }}</div>
        <div class="page-sub">{{ $supply->product_code }} · {{ $supply->category->name ?? '—' }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('supplies.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
        <a href="{{ route('stock.create', $supply) }}" class="btn btn-outline-success"><i class="bi bi-plus-circle"></i> Add Stock</a>
        <a href="{{ route('supplies.edit', $supply) }}" class="btn btn-primary">Edit</a>
    </div>
</div>

<div class="row g-3 mb-3">
    {{-- Details --}}
    <div class="col-lg-8">
        <div class="dpanel">
            <div class="dpanel-title mb-3">Item Details</div>
            <div class="detail-grid">
                <div>
                    <div class="detail-label">Product Code</div>
                    <div class="detail-value">{{ $supply->product_code ?? '—' }}</div>
                </div>
                <div>
                    <div class="detail-label">Stock No.</div>
                    <div class="detail-value">{{ $supply->stock_no ?? '—' }}</div>
                </div>
                <div>
                    <div class="detail-label">Category</div>
                    <div class="detail-value">{{ $supply->category->name ?? '—' }}</div>
                </div>
                <div>
                    <div class="detail-label">Unit of Measure</div>
                    <div class="detail-value">{{ $supply->unit }}</div>
                </div>
                <div>
                    <div class="detail-label">Unit Value</div>
                    <div class="detail-value">₱{{ number_format($supply->unit_value, 2) }}</div>
                </div>
                <div>
                    <div class="detail-label">Minimum Stock</div>
                    <div class="detail-value">{{ $supply->minimum_stock }} {{ $supply->unit }}</div>
                </div>
                <div>
                    <div class="detail-label">Expiry</div>
                    <div class="detail-value">
                        @if($supply->tracksExpiry() && $supply->expiration_date)
                            {{ $supply->expiration_date->format('M d, Y') }}
                        @else
                            <span class="text-muted">Not tracked</span>
                        @endif
                    </div>
                </div>
                <div>
                    <div class="detail-label">Last Updated</div>
                    <div class="detail-value">{{ $supply->updated_at?->format('M d, Y') }}</div>
                </div>
            </div>

            @if($supply->remarks)
                <div class="mt-3">
                    <div class="detail-label">Remarks</div>
                    <div class="detail-value">{{ $supply->remarks }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- Stock summary --}}
    <div class="col-lg-4">
        <div class="dpanel">
            <div class="dpanel-title mb-3">Stock</div>

            <div class="mb-3">
                <div class="detail-label">Balance Per Card</div>
                <div class="hero-value" style="font-size:2rem; color: var(--ink);">{{ $supply->balance_per_card }}</div>
                <div class="cell-sub">{{ $supply->unit }}</div>
            </div>

            <div class="mb-3">
                <div class="detail-label">On Hand Per Count</div>
                <div class="detail-value">{{ $supply->on_hand_per_count }} {{ $supply->unit }}</div>
            </div>

            <div>
                <div class="detail-label mb-1">Status</div>
                @switch($supply->stockStatus())
                    @case('out')
                        <span class="badge" style="background: var(--danger);">Out of stock</span>
                        @break
                    @case('low')
                        <span class="badge bg-warning text-dark">Low</span>
                        @break
                    @default
                        <span class="badge bg-success">Available</span>
                @endswitch
            </div>
        </div>
    </div>
</div>

{{-- Batches in stock --}}
<div class="dpanel mb-3">
    <div class="dpanel-head">
        <div class="dpanel-title">Batches in Stock</div>
        <span class="activity-date">{{ $activeBatches->sum('remaining_quantity') }} {{ $supply->unit }} across {{ $activeBatches->count() }} {{ Str::plural('batch', $activeBatches->count()) }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Batch</th>
                    <th class="text-nowrap">Date Received</th>
                    <th>Delivered by</th>
                    <th class="text-nowrap">Ref. No.</th>
                    <th class="text-center">Received</th>
                    <th class="text-center">Remaining</th>
                    <th class="text-nowrap">Expiry</th>
                </tr>
            </thead>
            <tbody>
                @forelse($activeBatches as $i => $batch)
                    <tr>
                        <td class="text-muted small">Batch {{ $i + 1 }}</td>
                        <td class="text-nowrap">{{ $batch->date_received->format('M d, Y') }}</td>
                        <td>{{ $batch->delivered_by ?? '—' }}</td>
                        <td class="cell-sub">{{ $batch->reference_no ?? '—' }}</td>
                        <td class="text-center text-muted">{{ $batch->quantity }} {{ $supply->unit }}</td>
                        <td class="text-center fw-semibold">{{ $batch->remaining_quantity }} {{ $supply->unit }}</td>
                        <td class="text-nowrap">
                            @if($batch->expiration_date)
                                <div class="small">{{ $batch->expiration_date->format('M d, Y') }}</div>
                                @switch($batch->expiryStatus())
                                    @case('expired')
                                        <span class="badge bg-dark">Expired</span>
                                        @break
                                    @case('expiring')
                                        <span class="badge bg-warning text-dark">Expiring soon</span>
                                        @break
                                    @default
                                        <span class="badge bg-success">Safe</span>
                                @endswitch
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="dash-empty">No batches with stock. Add a delivery to record one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="row g-3">
    {{-- Receiving history --}}
    <div class="col-lg-6">
        <div class="dpanel">
            <div class="dpanel-head">
                <div class="dpanel-title">Receiving History</div>
                <a href="{{ route('stock.create', $supply) }}" class="dpanel-link">Add stock</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-nowrap">Date</th>
                            <th class="text-center">Qty</th>
                            <th>Delivered by</th>
                            <th class="text-nowrap">Batch Expiry</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($entries as $entry)
                        <tr>
                            <td class="text-nowrap">{{ $entry->date_received->format('M d, Y') }}</td>
                            <td class="text-center text-success fw-semibold">+{{ $entry->quantity }} {{ $supply->unit }}</td>
                            <td>{{ $entry->delivered_by ?? '—' }}</td>
                            <td class="text-muted small text-nowrap">{{ $entry->expiration_date?->format('M d, Y') ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="dash-empty">No deliveries recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Request history --}}
    <div class="col-lg-6">
        <div class="dpanel">
            <div class="dpanel-head">
                <div class="dpanel-title">Request History</div>
                <a href="{{ route('requests.index') }}" class="dpanel-link">All requests</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-nowrap">Request No.</th>
                            <th>Personnel</th>
                            <th class="text-center">Qty</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requestLines as $line)
                            @php $req = $line->request; @endphp
                            <tr class="clickable-row" data-href="{{ route('requests.show', $req) }}">
                                <td><span class="req-no">{{ $req->requestNo() }}</span></td>
                                <td>{{ $req->personnel->name ?? '—' }}</td>
                                <td class="text-center">{{ $line->quantity }}</td>
                                <td><span class="req-badge req-{{ $req->status }}">{{ ucfirst($req->status) }}</span></td>
                            </tr>
                        @empty
                        <tr><td colspan="4" class="dash-empty">This item hasn't been requested yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.clickable-row').forEach(function (row) {
        row.addEventListener('click', function () {
            window.location = row.dataset.href;
        });
    });
</script>
@endpush