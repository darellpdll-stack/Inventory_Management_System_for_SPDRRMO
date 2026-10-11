@extends('layouts.app')
@section('title', $property->description)

@section('content')
@php
    $isAdmin = Auth::user()->role === 'admin';
    $numbers = $property->propertyNoList();
    $tagged = $property->property_no && count($numbers) && $numbers[0] !== '—';
    $outCount = $outByNumber->count();
    $totalValue = (float) $property->unit_value * (int) $property->quantity;
    $countMismatch = $property->on_hand_per_count !== $property->quantity;
@endphp

<div class="page-head">
    <div>
        <div class="page-title">{{ $property->description }}</div>
        <div class="page-sub">
            {{ $property->category->name ?? '—' }} ·
            {{ $property->type === 'semi-expendable' ? 'Semi-expendable' : 'Expendable' }} ·
            {{ $property->propertyNoRange() }}
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('property.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
        @if($isAdmin)
            <a href="{{ route('property.edit', $property) }}" class="btn btn-outline-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
        @endif
    </div>
</div>

{{-- Details, with the count figures folded in --}}
<div class="dpanel mb-3">
    <div class="dpanel-title mb-3">Item Details</div>
    <div class="detail-grid">
        <div>
            <div class="detail-label">Accountable Person</div>
            <div class="detail-value">{{ $property->personnel->name ?? '—' }}</div>
            <div class="cell-sub">{{ $property->personnel->position ?? '' }}</div>
        </div>
        <div>
            <div class="detail-label">Unit Value</div>
            <div class="detail-value">₱{{ number_format((float) $property->unit_value, 2) }}</div>
            <div class="cell-sub">per {{ $property->unit }}</div>
        </div>
        <div>
            <div class="detail-label">Total Value</div>
            <div class="detail-value">₱{{ number_format($totalValue, 2) }}</div>
            <div class="cell-sub">{{ $property->quantity }} × unit value</div>
        </div>
        <div>
            <div class="detail-label">Owned</div>
            <div class="detail-value">{{ $property->quantity }} {{ $property->unit }}</div>
            <div class="cell-sub">recorded on hand: {{ $property->on_hand_per_count }}</div>
        </div>
        <div>
            <div class="detail-label">Borrowed</div>
            <div class="detail-value">{{ $outCount }}</div>
            <div class="cell-sub">{{ $outCount ? "out on a Borrower's Form" : 'nothing is out' }}</div>
        </div>
        <div>
            <div class="detail-label">Expected on shelf</div>
            <div class="detail-value">{{ $property->availableQuantity() }}</div>
            <div class="cell-sub">what a physical count should find</div>
        </div>
        <div>
            <div class="detail-label">Remarks</div>
            <div class="detail-value">{{ $property->remarks ?? '—' }}</div>
        </div>
    </div>

    @if($countMismatch)
        <div class="alert alert-warning mt-3 mb-0 py-2 small">
            The recorded count of {{ $property->on_hand_per_count }} differs from the
            {{ $property->quantity }} owned by {{ $property->on_hand_per_count - $property->quantity }}.
        </div>
    @endif
</div>

{{-- Units --}}
<div class="dpanel mb-3">
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <div class="dpanel-title">Units</div>
            <div class="cell-sub">
                @if($tagged)
                    Green is on the shelf, blue is with a borrower.
                @else
                    This item has no property numbers, so units are tracked by count only.
                @endif
            </div>
        </div>
    </div>

    @if($tagged)
        <div class="d-flex flex-wrap gap-1" style="max-height: 220px; overflow-y: auto;">
            @foreach($numbers as $number)
                @php $unit = $outByNumber->get($number); @endphp
                <span class="req-badge {{ $unit ? 'req-for_release' : 'req-completed' }}"
                      @if($unit) title="{{ $unit->borrowing->personnel->name ?? '' }}" @endif>
                    {{ $number }}
                </span>
            @endforeach
        </div>

        @if($outCount)
            <div class="detail-label mt-3 mb-2">Currently with a borrower</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <tbody>
                        @foreach($outByNumber as $number => $unit)
                            <tr>
                                <td class="cell-main" style="width: 30%;">{{ $number }}</td>
                                <td>
                                    {{ $unit->borrowing->personnel->name ?? '—' }}
                                    @if($unit->borrowing?->date_released)
                                        <span class="cell-sub">· since {{ $unit->borrowing->date_released->format('M d, Y') }}</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($unit->borrowing)
                                        <a href="{{ route('borrowings.show', $unit->borrowing) }}" class="req-no">
                                            {{ $unit->borrowing->borrowNo() }}
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @else
        <div class="cell-sub">
            {{ $property->quantity }} owned · {{ $outCount }} borrowed ·
            {{ $property->availableQuantity() }} available
        </div>
    @endif
</div>

{{-- Borrowing history --}}
<div class="dpanel">
    <div class="dpanel-title mb-3">Borrowing History</div>

    @forelse($history as $units)
        @php $borrowing = $units->first()->borrowing; @endphp
        @continue(! $borrowing)

        <div class="timeline-item">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <a href="{{ route('borrowings.show', $borrowing) }}" class="req-no">{{ $borrowing->borrowNo() }}</a>
                    <div class="timeline-by">
                        {{ $borrowing->personnel->name ?? '—' }}
                        @if($borrowing->purpose) · {{ $borrowing->purpose }} @endif
                    </div>
                    <div class="timeline-by">
                        {{ $units->count() }} {{ Str::plural('unit', $units->count()) }}
                        @if($units->pluck('property_no')->filter()->count())
                            · {{ $units->pluck('property_no')->filter()->implode(', ') }}
                        @endif
                    </div>
                </div>
                <span class="req-badge {{ $borrowing->badgeClass() }}">{{ $borrowing->statusLabel() }}</span>
            </div>
        </div>
    @empty
        <div class="dash-empty">This item has never been borrowed.</div>
    @endforelse
</div>
@endsection
