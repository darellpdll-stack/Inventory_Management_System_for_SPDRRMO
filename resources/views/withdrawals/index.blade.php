@extends('layouts.app')
@section('title', 'Withdrawals')

@section('content')
<div class="page-head">
    <div>
        <div class="page-title">Withdrawals</div>
        <div class="page-sub">Supplies released through approved requests.</div>
    </div>
    <a href="{{ route('requests.index') }}" class="btn btn-outline-primary"><i class="bi bi-inbox"></i> Go to Requests</a>
</div>

<div class="card shadow-sm">
    <form method="GET" class="list-toolbar">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                   placeholder="Search person, item, or request no…">
        </div>
        <select name="category" class="form-select" style="width:auto;" onchange="this.form.submit()">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
    </form>

    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-nowrap">Request No.</th>
                    <th class="text-nowrap">Date Released</th>
                    <th>Items</th>
                    <th class="text-nowrap">Requested by</th>
                    <th class="text-nowrap">Received by</th>
                    <th class="text-nowrap">Released by</th>
                    <th>Remarks</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($releases as $release)
                    <tr>
                        <td class="text-nowrap">
                            <a href="{{ route('requests.show', $release->supply_request_id) }}" class="req-no">
                                {{ $release->request?->requestNo() ?? '—' }}
                            </a>
                        </td>
                        <td class="text-nowrap">{{ $release->date_released->format('M d, Y') }}</td>
                        <td>
                            @foreach($release->items as $line)
                                <div class="cell-main">
                                    {{ $line->supplyItem->description ?? '—' }}
                                    <span class="cell-sub">— {{ $line->quantity }} {{ $line->supplyItem->unit ?? '' }}</span>
                                </div>
                            @endforeach
                        </td>
                        <td class="text-nowrap">{{ $release->request?->personnel?->name ?? '—' }}</td>
                        <td class="text-nowrap">{{ $release->received_by ?? '—' }}</td>
                        <td class="text-nowrap">{{ $release->releasedBy->name ?? '—' }}</td>
                        <td class="cell-sub">{{ $release->remarks ?? '—' }}</td>
                        <td class="text-end">
                            @if(Auth::user()->role === 'admin' && $release->items->sum(fn ($l) => $l->returnableQuantity()) > 0)
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                        data-bs-toggle="modal" data-bs-target="#returnModal{{ $release->id }}"
                                        title="Record return">
                                    <i class="bi bi-arrow-return-left"></i>
                                </button>
                            @endif
                            <a href="{{ route('withdrawals.receipt', $release) }}" target="_blank"
                               class="btn btn-sm btn-outline-primary" title="Print receipt">
                                <i class="bi bi-printer"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="dash-empty">No releases yet. Approve a request and release its items.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@foreach($releases as $release)
    @continue(Auth::user()->role !== 'admin' || $release->items->sum(fn ($l) => $l->returnableQuantity()) < 1)

    <div class="modal fade" id="returnModal{{ $release->id }}" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <form method="POST" action="{{ route('returns.store', $release) }}" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Record Return — {{ $release->request?->requestNo() ?? '' }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Date returned</label>
                            <input type="date" name="date_returned" class="form-control"
                                   value="{{ now()->toDateString() }}"
                                   min="{{ $release->date_released->toDateString() }}"
                                   max="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Returned by</label>
                            <input type="text" name="returned_by" class="form-control"
                                   value="{{ $release->request?->personnel?->name }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Remarks</label>
                            <input type="text" name="remarks" class="form-control" placeholder="Optional">
                        </div>
                    </div>

                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Item</th>
                                <th class="text-nowrap">Released</th>
                                <th class="text-nowrap">Still out</th>
                                <th style="width:120px;">Return qty</th>
                                <th style="width:150px;">Condition</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($release->items as $i => $line)
                                @php
                                    $returnable = $line->returnableQuantity();
                                    $tracks = $line->supplyItem?->tracksExpiry();
                                @endphp
                                <tr>
                                    <td>
                                        {{ $line->supplyItem->description ?? '—' }}
                                        @if($returnable > 0 && $tracks)
                                            <div class="return-expiry mt-2">
                                                <label class="form-label small mb-1">Expiry on returned stock</label>
                                                <input type="date" name="lines[{{ $i }}][expiration_date]"
                                                       class="form-control form-control-sm">
                                            </div>
                                        @endif
                                    </td>
                                    <td>{{ $line->quantity }}</td>
                                    <td>{{ $returnable }}</td>

                                    @if($returnable > 0)
                                        <td>
                                            <input type="hidden" name="lines[{{ $i }}][release_item_id]" value="{{ $line->id }}">
                                            <input type="number" name="lines[{{ $i }}][quantity]"
                                                   class="form-control form-control-sm"
                                                   value="0" min="0" max="{{ $returnable }}">
                                        </td>
                                        <td>
                                            <select name="lines[{{ $i }}][condition]"
                                                    class="form-select form-select-sm return-condition">
                                                <option value="usable">Usable</option>
                                                <option value="damaged">Damaged</option>
                                            </select>
                                        </td>
                                    @else
                                        <td colspan="2" class="cell-sub">Fully returned</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Record Return</button>
                </div>
            </form>
        </div>
    </div>
@endforeach

<div class="mt-3">{{ $releases->links() }}</div>

<script>
document.querySelectorAll('.return-condition').forEach(function (sel) {
    sel.addEventListener('change', function () {
        var exp = this.closest('tr').querySelector('.return-expiry');
        if (!exp) return;
        var damaged = this.value !== 'usable';
        exp.classList.toggle('d-none', damaged);
        if (damaged) exp.querySelector('input').value = '';
    });
});
</script>
@endsection