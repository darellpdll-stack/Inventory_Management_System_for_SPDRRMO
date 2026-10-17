@extends('layouts.app')
@section('title', $count->countNo())

@section('content')
@php
    $isAdmin = Auth::user()->role === 'admin';
    $expectedTotal = $expected->count();
    $foundTotal = $found->count();
    $progress = $expectedTotal ? round($foundTotal / $expectedTotal * 100) : 0;

    // one row per item rather than per unit, or a few hundred units becomes a wall
    $expectedPerItem = $expected->groupBy(fn ($u) => $u->item->id)->map->count();

    $missingByItem = $missingUnaccounted
        ->groupBy(fn ($u) => $u->item->id)
        ->map(fn ($units) => (object) [
            'item' => $units->first()->item,
            'numbers' => $units->pluck('number')->values(),
            'expected' => $expectedPerItem[$units->first()->item->id] ?? $units->count(),
        ])
        ->values();

    $borrowedByItem = $missingBorrowed
        ->groupBy(fn ($u) => $u->item->id)
        ->map(fn ($units) => (object) [
            'item' => $units->first()->item,
            'numbers' => $units->pluck('number')->values(),
        ])
        ->values();
@endphp

<div class="page-head">
    <div>
        <div class="page-title">{{ $count->countNo() }}</div>
        <div class="page-sub">
            Physical count · {{ $count->count_date->format('M d, Y') }} ·
            started by {{ $count->startedBy->name ?? '—' }}
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('counts.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>

        @if($isAdmin && $count->isOpen())
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#abandonModal">
                <i class="bi bi-x-circle"></i> Abandon
            </button>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#closeModal">
                <i class="bi bi-check2-circle"></i> Close Count
            </button>
        @endif

        {{-- an abandoned count was never finished, so it is never applied --}}
        @if($isAdmin && $count->isClosed())
            <form method="POST" action="{{ route('counts.apply', $count) }}"
                  onsubmit="return confirm('This overwrites the recorded on-hand quantity for every item counted. Continue?');">
                @csrf
                <button class="btn btn-outline-primary">
                    <i class="bi bi-pencil-square"></i> Apply to Records
                </button>
            </form>
        @endif
    </div>
</div>

{{-- Scan box — the whole point of the page while a count is running --}}
@if($count->isOpen())
    <form method="POST" action="{{ route('counts.scan', $count) }}" class="card shadow-sm mb-3">
        @csrf
        <div class="card-body py-3">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <i class="bi bi-upc-scan fs-4 text-primary"></i>
                <div class="flex-grow-1" style="min-width: 220px;">
                    <input type="text" name="property_no" class="form-control form-control-lg"
                           placeholder="Scan a tag…" autocomplete="off" autofocus>
                </div>
                <button class="btn btn-primary btn-lg">Count</button>
            </div>
            <div class="text-muted small mt-2">
                Each tag counts once. A tag the records don't recognise is still recorded, as unexpected.
            </div>
        </div>
    </form>
@elseif($count->isAbandoned())
    <div class="alert alert-warning">
        <strong>This count was abandoned</strong> on {{ $count->closed_at?->format('M d, Y g:i A') }}
        by {{ $count->closedBy->name ?? '—' }}. It was never finished, so what follows is only
        a partial record of how far the counting got — not a result. Nothing here was written
        to the property records.
    </div>
@else
    <div class="alert alert-secondary">
        This count was closed {{ $count->closed_at?->format('M d, Y g:i A') }}
        by {{ $count->closedBy->name ?? '—' }}. The results below are a fixed record.
    </div>
@endif

{{-- Tick list — counting without a scanner --}}
@if($count->isOpen() && $missingByItem->count())
<div class="dpanel mb-3">
    <form method="POST" action="{{ route('counts.tick', $count) }}">
        @csrf
        <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
            <div>
                <div class="dpanel-title">Tick What You Find</div>
                <div class="cell-sub">
                    No scanner needed. Tick the units you can see, then save.
                </div>
            </div>
            <div class="d-flex gap-2 align-items-center flex-wrap">
                <div class="search-box" style="max-width: 220px;">
                    <i class="bi bi-search"></i>
                    <input type="text" id="tickFilter" class="form-control" placeholder="Filter items…">
                </div>
                <button class="btn btn-primary"><i class="bi bi-check2-all"></i> Save Ticks</button>
            </div>
        </div>

        <div style="max-height: 360px; overflow-y: auto;">
            @foreach($missingByItem as $row)
                <div class="timeline-item tick-item"
                     data-name="{{ Str::lower($row->item->description . ' ' . ($row->item->category->name ?? '')) }}">
                    <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                        <div>
                            <div class="cell-main">{{ $row->item->description }}</div>
                            <div class="timeline-by">
                                {{ $row->item->category->name ?? '—' }} ·
                                {{ $row->numbers->count() }} still to find
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-light tick-all text-nowrap">All</button>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        @foreach($row->numbers as $number)
                            <div class="form-check form-check-inline me-0">
                                <input class="form-check-input tick-box" type="checkbox"
                                       name="numbers[]" value="{{ $number }}"
                                       id="tick{{ $loop->parent->index }}_{{ $loop->index }}">
                                <label class="form-check-label small" for="tick{{ $loop->parent->index }}_{{ $loop->index }}">
                                    {{ $number }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </form>
</div>
@endif

{{-- Progress --}}
<div class="dpanel mb-3">
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <div class="dpanel-title">Progress</div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <span class="req-badge req-completed">{{ $foundTotal }} found</span>
            <span class="req-badge req-approved">{{ $missingBorrowed->count() }} borrowed</span>
            <span class="req-badge req-declined">{{ $missingUnaccounted->count() }} unaccounted</span>
            @if($unexpected->count())
                <span class="req-badge req-pending">{{ $unexpected->count() }} unexpected</span>
            @endif
            @if($foundButBorrowed->count())
                <span class="req-badge req-pending">{{ $foundButBorrowed->count() }} conflicting</span>
            @endif
        </div>
    </div>

    <div class="progress" style="height: 10px;">
        <div class="progress-bar bg-success" style="width: {{ $progress }}%"></div>
    </div>
    <div class="cell-sub mt-2">
        {{ $foundTotal }} of {{ $expectedTotal }} property units accounted for by scanning.
    </div>
</div>

<div class="row g-3">
    {{-- Not yet found --}}
    <div class="col-lg-7">
        <div class="dpanel mb-3">
            <div class="dpanel-title mb-1">Unaccounted For</div>
            <div class="cell-sub mb-3">
                In the records, not scanned, and not out on a Borrower's Form.
            </div>

            <div style="max-height: 420px; overflow-y: auto;">
            @forelse($missingByItem as $row)
                <div class="timeline-item">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="flex-grow-1">
                            <div class="cell-main">{{ $row->item->description }}</div>
                            <div class="timeline-by">
                                {{ $row->item->category->name ?? '—' }} ·
                                held by {{ $row->item->personnel->name ?? 'no one recorded' }}
                            </div>
                            <div class="timeline-by mt-1">{{ $row->numbers->implode(', ') }}</div>
                        </div>
                        <span class="req-badge req-declined text-nowrap">
                            {{ $row->numbers->count() }} of {{ $row->expected }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="dash-empty">
                    @if($foundTotal)
                        Nothing unaccounted for.
                    @else
                        Nothing scanned yet.
                    @endif
                </div>
            @endforelse
            </div>
        </div>

        {{-- Out on loan --}}
        @if($missingBorrowed->count())
        <div class="dpanel mb-3">
            <div class="dpanel-title mb-1">Absent but Explained</div>
            <div class="cell-sub mb-3">Out on a Borrower's Form, so not expected on the shelf.</div>

            <div style="max-height: 300px; overflow-y: auto;">
            @foreach($borrowedByItem as $row)
                @php $borrowed = $borrowedNumbers->get($row->numbers->first()); @endphp
                <div class="timeline-item">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="flex-grow-1">
                            <div class="cell-main">{{ $row->item->description }}</div>
                            <div class="timeline-by">
                                with {{ $borrowed->borrowing->personnel->name ?? '—' }}
                                @if($borrowed?->borrowing)
                                    ·
                                    <a href="{{ route('borrowings.show', $borrowed->borrowing) }}" class="req-no">
                                        {{ $borrowed->borrowing->borrowNo() }}
                                    </a>
                                @endif
                            </div>
                            <div class="timeline-by mt-1">{{ $row->numbers->implode(', ') }}</div>
                        </div>
                        <span class="req-badge req-approved text-nowrap">{{ $row->numbers->count() }} out</span>
                    </div>
                </div>
            @endforeach
            </div>
        </div>
        @endif

        {{-- Found, but the records say it is lent out --}}
        @if($foundButBorrowed->count())
        <div class="dpanel mb-3">
            <div class="dpanel-title mb-1">Found but Recorded as Borrowed</div>
            <div class="cell-sub mb-3">
                Scanned on the shelf although a Borrower's Form says someone has them.
                Most likely returned without being recorded.
            </div>

            @foreach($foundButBorrowed as $scan)
                @php $borrowed = $borrowedNumbers->get($scan->property_no); @endphp
                <div class="timeline-item">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div>
                            <div class="cell-main">
                                {{ $scan->propertyItem->description ?? '—' }}
                                <span class="cell-sub">· {{ $scan->property_no }}</span>
                            </div>
                            <div class="timeline-by">
                                recorded as with {{ $borrowed->borrowing->personnel->name ?? '—' }}
                                @if($borrowed?->borrowing)
                                    ·
                                    <a href="{{ route('borrowings.show', $borrowed->borrowing) }}" class="req-no">
                                        {{ $borrowed->borrowing->borrowNo() }}
                                    </a>
                                @endif
                            </div>
                        </div>
                        <span class="req-badge req-pending text-nowrap">conflict</span>
                    </div>
                </div>
            @endforeach
        </div>
        @endif

        {{-- Unexpected --}}
        @if($unexpected->count())
        <div class="dpanel">
            <div class="dpanel-title mb-1">Unexpected</div>
            <div class="cell-sub mb-3">Scanned, but these numbers are not in the property records.</div>

            @foreach($unexpected as $scan)
                <div class="timeline-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="cell-main">{{ $scan->property_no }}</div>
                            <div class="timeline-by">
                                scanned {{ $scan->created_at->format('M d, g:i A') }}
                                by {{ $scan->scannedBy->name ?? '—' }}
                            </div>
                        </div>
                        @if($count->isOpen())
                            <form method="POST" action="{{ route('counts.scans.destroy', [$count, $scan]) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Remove this scan">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Scan log --}}
    <div class="col-lg-5">
        <div class="dpanel">
            <div class="dpanel-title mb-1">Scanned</div>
            <div class="cell-sub mb-3">Newest first. {{ $scans->count() }} in total.</div>

            <div style="max-height: 380px; overflow-y: auto;">
                @forelse($scans as $scan)
                    <div class="timeline-item">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <div class="cell-main">{{ $scan->property_no }}</div>
                                <div class="timeline-by">
                                    {{ $scan->propertyItem->description ?? 'not in the records' }}
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if($scan->isUnexpected())
                                    <span class="req-badge req-pending">?</span>
                                @endif
                                @if($count->isOpen())
                                    <form method="POST" action="{{ route('counts.scans.destroy', [$count, $scan]) }}">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-light" title="Remove this scan">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="dash-empty">Nothing scanned yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Close --}}
@if($isAdmin && $count->isOpen())
<div class="modal fade" id="closeModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('counts.close', $count) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Close {{ $count->countNo() }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small mb-3">
                    {{ $foundTotal }} of {{ $expectedTotal }} units were found.
                    {{ $missingUnaccounted->count() }} are unaccounted for.
                    Once closed, no more tags can be scanned into this count.
                </p>
                <label class="form-label">Remarks <span class="cell-sub">(optional)</span></label>
                <input type="text" name="remarks" class="form-control"
                       value="{{ $count->remarks }}" maxlength="255">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Close Count</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Abandon --}}
@if($isAdmin && $count->isOpen())
<div class="modal fade" id="abandonModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('counts.abandon', $count) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Abandon {{ $count->countNo() }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="small mb-2">
                    Use this when the counting will not be finished — it was interrupted, or
                    started by mistake. The {{ $scans->count() }}
                    {{ \Illuminate\Support\Str::plural('tag', $scans->count()) }} already
                    counted stay on record, but they will not be treated as a result and can
                    never be written to the property records.
                </p>
                <p class="small text-muted mb-3">
                    If the counting is only paused, there is nothing to do — leave it in progress
                    and come back to it. Nothing is lost.
                </p>
                <label class="form-label">Why? <span class="cell-sub">(optional)</span></label>
                <input type="text" name="remarks" class="form-control" maxlength="255"
                       placeholder="e.g. Ran out of time, starting over next week">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Keep Counting</button>
                <button class="btn btn-danger">Abandon Count</button>
            </div>
        </form>
    </div>
</div>
@endif

<script>
(function () {
    // "All" ticks every unit of one item
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.tick-all');
        if (!btn) return;

        var row = btn.closest('.tick-item');
        var boxes = row.querySelectorAll('.tick-box');
        var allOn = Array.prototype.every.call(boxes, function (b) { return b.checked; });

        boxes.forEach(function (b) { b.checked = !allOn; });
    });

    // filter the tick list by item name
    document.addEventListener('input', function (e) {
        if (e.target.id !== 'tickFilter') return;

        var term = e.target.value.trim().toLowerCase();
        document.querySelectorAll('.tick-item').forEach(function (row) {
            row.classList.toggle('d-none', term !== '' && !(row.dataset.name || '').includes(term));
        });
    });
})();
</script>
@endsection
