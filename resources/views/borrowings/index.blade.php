@extends('layouts.app')
@section('title', 'Borrowings')

@section('content')
<div class="page-head">
    <div>
        <div class="page-title">Borrowed Equipment</div>
        <div class="page-sub">Equipment reserved, lent out, and returned.</div>
    </div>
    <a href="{{ route('borrowings.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Borrowing</a>
</div>

<div class="card shadow-sm">
    <form method="GET" class="list-toolbar">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                   placeholder="Search borrowing number or name…">
        </div>
        <select name="status" class="form-select" style="width:auto;" onchange="this.form.submit()">
            <option value="all"      {{ $status === 'all'      ? 'selected' : '' }}>All statuses</option>
            <option value="pending"  {{ $status === 'pending'  ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="released" {{ $status === 'released' ? 'selected' : '' }}>Borrowed</option>
            <option value="returned" {{ $status === 'returned' ? 'selected' : '' }}>Returned</option>
            <option value="declined" {{ $status === 'declined' ? 'selected' : '' }}>Declined</option>
        </select>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-nowrap">Borrowing No.</th>
                    <th class="text-nowrap">Borrower</th>
                    <th>Equipment</th>
                    <th class="text-nowrap">Status</th>
                    <th class="text-nowrap">Date Needed</th>
                    <th class="text-nowrap">Date Borrowed</th>
                    <th class="text-nowrap">Outstanding</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($borrowings as $borrowing)
                    @php
                        $groups = $borrowing->groupedUnits();
                        $first = $groups->first();
                        $outstanding = $borrowing->units->whereNull('returned_at')->count();
                    @endphp
                    <tr>
                        <td class="text-nowrap">
                            <a href="{{ route('borrowings.show', $borrowing) }}" class="req-no">{{ $borrowing->borrowNo() }}</a>
                        </td>
                        <td class="text-nowrap">{{ $borrowing->personnel->name ?? '—' }}</td>
                        <td>
                            <div class="cell-main text-truncate" style="max-width: 300px;">
                                {{ $first->item->description ?? '—' }}
                            </div>
                            <div class="cell-sub">
                                @if($groups->count() > 1)
                                    +{{ $groups->count() - 1 }} more {{ Str::plural('item', $groups->count() - 1) }}
                                @else
                                    {{ $first->quantity ?? '' }} {{ $first->item->unit ?? '' }}
                                @endif
                            </div>
                        </td>
                        <td class="text-nowrap">
                            <span class="req-badge {{ $borrowing->badgeClass() }}">{{ $borrowing->statusLabel() }}</span>
                        </td>
                        <td class="text-nowrap">{{ $borrowing->date_needed->format('M d, Y') }}</td>
                        <td class="text-nowrap">
                            @if($borrowing->date_released)
                                {{ $borrowing->date_released->format('M d, Y') }}
                                @if($returnedOn = $borrowing->dateReturned())
                                    <div class="cell-sub">back {{ $returnedOn->format('M d, Y') }}</div>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-nowrap">
                            @if($borrowing->status === 'released')
                                <span class="fw-semibold">{{ $outstanding }}</span>
                                <span class="cell-sub">still out</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-end">
                            @if(Auth::user()->role === 'admin' && $borrowing->status === 'released')
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                        data-bs-toggle="modal" data-bs-target="#returnModal{{ $borrowing->id }}"
                                        title="Record return">
                                    <i class="bi bi-arrow-return-left"></i>
                                </button>
                            @endif
                            <a href="{{ route('borrowings.show', $borrowing) }}" class="btn btn-sm btn-outline-primary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="dash-empty">No borrowings found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@foreach($borrowings as $borrowing)
    @continue(Auth::user()->role !== 'admin' || $borrowing->status !== 'released')

    <div class="modal fade" id="returnModal{{ $borrowing->id }}" tabindex="-1">
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
                        @foreach($borrowing->units->whereNull('returned_at') as $unit)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="unit_ids[]"
                                       value="{{ $unit->id }}" id="rowUnit{{ $unit->id }}" checked>
                                <label class="form-check-label" for="rowUnit{{ $unit->id }}">
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
@endforeach

<div class="mt-3">{{ $borrowings->links() }}</div>
@endsection
