@extends('layouts.app')
@section('title', $borrowing->borrowNo())

@section('content')
@php
    $isAdmin = Auth::user()->role === 'admin';
    $groups = $borrowing->groupedUnits();
    $outstanding = $borrowing->units->whereNull('returned_at');
@endphp

<div class="page-head">
    <div>
        <div class="page-title">{{ $borrowing->borrowNo() }}</div>
        <div class="page-sub">Borrowed equipment</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('borrowings.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>

        @if(in_array($borrowing->status, ['approved', 'released', 'returned']))
            <a href="{{ route('borrowings.form', $borrowing) }}" target="_blank" class="btn btn-outline-primary">
                <i class="bi bi-printer"></i> Borrower's Form
            </a>
        @endif

        @if($isAdmin && $borrowing->status === 'pending')
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#declineModal">Decline</button>
            <form method="POST" action="{{ route('borrowings.approve', $borrowing) }}"
                  onsubmit="return confirm('Approve this borrowing?');">
                @csrf
                <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Approve</button>
            </form>
        @endif

        @if($isAdmin && $borrowing->status === 'approved')
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#releaseModal">
                <i class="bi bi-box-arrow-up"></i> Release Equipment
            </button>
        @endif

        @if($isAdmin && $borrowing->status === 'released')
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#returnModal">
                <i class="bi bi-arrow-return-left"></i> Record Return
            </button>
        @endif
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="dpanel mb-3">
            <div class="detail-grid mb-4">
                <div>
                    <div class="detail-label">Status</div>
                    <span class="req-badge {{ $borrowing->badgeClass() }}">{{ $borrowing->statusLabel() }}</span>
                </div>
                <div>
                    <div class="detail-label">Date Needed</div>
                    <div class="detail-value">{{ $borrowing->date_needed->format('M d, Y') }}</div>
                </div>
                <div>
                    <div class="detail-label">Borrower</div>
                    <div class="detail-value">{{ $borrowing->personnel->name ?? '—' }}</div>
                    <div class="cell-sub">{{ $borrowing->personnel->position ?? '' }}</div>
                </div>
                <div>
                    <div class="detail-label">Expected Return</div>
                    <div class="detail-value">{{ $borrowing->date_due?->format('M d, Y') ?? '—' }}</div>
                </div>
                <div>
                    <div class="detail-label">Date Borrowed</div>
                    <div class="detail-value">
                        {{ $borrowing->date_released?->format('M d, Y') ?? 'Not yet released' }}
                    </div>
                    @if($borrowing->releasedBy)
                        <div class="cell-sub">released by {{ $borrowing->releasedBy->name }}</div>
                    @endif
                </div>
                <div>
                    <div class="detail-label">Date Returned</div>
                    <div class="detail-value">
                        @if($borrowing->dateReturned())
                            {{ $borrowing->dateReturned()->format('M d, Y') }}
                        @elseif($borrowing->status === 'released')
                            Still out
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div>
                    <div class="detail-label">Purpose</div>
                    <div class="detail-value">{{ $borrowing->purpose ?? '—' }}</div>
                </div>
                <div>
                    <div class="detail-label">Remarks</div>
                    <div class="detail-value">{{ $borrowing->remarks ?? '—' }}</div>
                </div>
            </div>

            <div class="detail-label mb-2">Equipment</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Property No.</th>
                            <th class="text-center">Qty</th>
                            <th class="text-center">Returned</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($groups as $group)
                            <tr>
                                <td>{{ $group->item->description ?? '—' }}</td>
                                <td class="cell-sub">{{ $group->item->category->name ?? '—' }}</td>
                                <td class="cell-sub">
                                    {{ $group->numbers->count() ? $group->numbers->implode(', ') : '—' }}
                                </td>
                                <td class="text-center">{{ $group->quantity }} {{ $group->item->unit ?? '' }}</td>
                                <td class="text-center {{ $group->returned < $group->quantity ? 'fw-semibold' : 'text-muted' }}">
                                    {{ $group->returned }} / {{ $group->quantity }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if($borrowing->units->whereNotNull('returned_at')->count())
        <div class="dpanel">
            <div class="dpanel-title mb-3">Return History</div>
            @foreach($borrowing->units->whereNotNull('returned_at')->sortByDesc('returned_at') as $unit)
                <div class="timeline-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="cell-main">
                                {{ $unit->propertyItem->description ?? '—' }}
                                @if($unit->property_no)
                                    <span class="cell-sub">· {{ $unit->property_no }}</span>
                                @endif
                            </div>
                            <div class="timeline-by">
                                Returned {{ $unit->returned_at->format('M d, Y') }} ·
                                received by {{ $unit->receivedBy->name ?? '—' }}
                            </div>
                            @if($unit->remarks)
                                <div class="timeline-by">{{ $unit->remarks }}</div>
                            @endif
                        </div>
                        <span class="req-badge {{ $unit->condition === 'damaged' ? 'req-declined' : 'req-completed' }}">
                            {{ ucfirst($unit->condition ?? 'good') }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Timeline --}}
    <div class="col-lg-4">
        <div class="dpanel">
            <div class="dpanel-title mb-2">Timeline</div>

            <div class="timeline-item">
                <div class="detail-label">Requested</div>
                <div class="timeline-when">{{ $borrowing->created_at->format('M d, Y g:i A') }}</div>
                <div class="timeline-by">by {{ $borrowing->personnel->name ?? '—' }}</div>
            </div>

            @if($borrowing->status === 'pending')
                <div class="timeline-item">
                    <div class="detail-label">Waiting for approval</div>
                    <div class="timeline-by">The units are reserved in the meantime.</div>
                </div>
            @elseif($borrowing->status === 'declined')
                <div class="timeline-item">
                    <div class="detail-label">Declined</div>
                    <div class="timeline-when">{{ $borrowing->reviewed_at?->format('M d, Y g:i A') }}</div>
                    <div class="timeline-by">by {{ $borrowing->reviewedBy->name ?? '—' }}</div>
                    @if($borrowing->decline_reason)
                        <div class="timeline-by mt-1">Reason: {{ $borrowing->decline_reason }}</div>
                    @endif
                </div>
            @else
                <div class="timeline-item">
                    <div class="detail-label">Approved</div>
                    <div class="timeline-when">{{ $borrowing->reviewed_at?->format('M d, Y g:i A') }}</div>
                    <div class="timeline-by">by {{ $borrowing->reviewedBy->name ?? '—' }}</div>
                </div>

                @if($borrowing->status === 'approved')
                    <div class="timeline-item">
                        <div class="detail-label">Awaiting pickup</div>
                        <div class="timeline-by">Print the Borrower's Form before handing over.</div>
                    </div>
                @endif

                @if($borrowing->date_released)
                    <div class="timeline-item">
                        <div class="detail-label">Released</div>
                        <div class="timeline-when">{{ $borrowing->date_released->format('M d, Y') }}</div>
                        <div class="timeline-by">by {{ $borrowing->releasedBy->name ?? '—' }}</div>
                    </div>
                @endif

                @if($borrowing->status === 'released')
                    <div class="timeline-item">
                        <div class="detail-label">With the borrower</div>
                        <div class="timeline-by">
                            {{ $outstanding->count() }} {{ Str::plural('unit', $outstanding->count()) }} still to come back.
                        </div>
                    </div>
                @elseif($borrowing->status === 'returned')
                    <div class="timeline-item">
                        <div class="detail-label">Returned</div>
                        <div class="timeline-by">All equipment is back and accounted for.</div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- Decline modal --}}
@if($isAdmin && $borrowing->status === 'pending')
<div class="modal fade" id="declineModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('borrowings.decline', $borrowing) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Decline {{ $borrowing->borrowNo() }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Reason <span class="cell-sub">(optional)</span></label>
                <input type="text" name="decline_reason" class="form-control" maxlength="255">
                <div class="cell-sub mt-2">The reserved units become available again.</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger">Decline</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Release modal --}}
@if($isAdmin && $borrowing->status === 'approved')
<div class="modal fade" id="releaseModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('borrowings.release', $borrowing) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <div>
                    <h6 class="modal-title fw-bold">Release Equipment</h6>
                    <div class="cell-sub">{{ $borrowing->borrowNo() }} · {{ $borrowing->personnel->name ?? '—' }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Date released</label>
                <input type="date" name="date_released" class="form-control"
                       value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required>
                <div class="cell-sub mt-2">
                    Confirm only once the borrower has signed the form and taken the equipment.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Release</button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- Return modal --}}
@if($isAdmin && $borrowing->status === 'released')
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form method="POST" action="{{ route('borrowings.return', $borrowing) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <div>
                    <h6 class="modal-title fw-bold">Record Return</h6>
                    <div class="cell-sub">{{ $borrowing->borrowNo() }} · {{ $borrowing->personnel->name ?? '—' }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-5">
                        <label class="form-label">Date returned</label>
                        <input type="date" name="date_returned" class="form-control"
                               value="{{ now()->toDateString() }}"
                               min="{{ $borrowing->date_released?->toDateString() }}"
                               max="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Condition</label>
                        <select name="condition" class="form-select" required>
                            <option value="good">Good</option>
                            <option value="damaged">Damaged</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Remarks <span class="cell-sub">(optional)</span></label>
                        <input type="text" name="remarks" class="form-control">
                    </div>
                </div>

                <div class="detail-label mb-2">Which units came back?</div>
                <div class="d-flex flex-column gap-1">
                    @foreach($outstanding as $unit)
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="unit_ids[]"
                                   value="{{ $unit->id }}" id="unit{{ $unit->id }}" checked>
                            <label class="form-check-label" for="unit{{ $unit->id }}">
                                {{ $unit->propertyItem->description ?? '—' }}
                                @if($unit->property_no)
                                    <span class="cell-sub">· {{ $unit->property_no }}</span>
                                @endif
                            </label>
                        </div>
                    @endforeach
                </div>

                <div class="cell-sub mt-3">
                    Untick anything the borrower still has. The borrowing stays open until every unit is back.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Record Return</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection
