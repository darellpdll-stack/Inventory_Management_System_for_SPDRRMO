@extends('layouts.app')
@section('title', $supplyRequest->requestNo())

@section('content')
@php
    $isAdmin = Auth::user()->role === 'admin';
    $canRelease = $isAdmin && $supplyRequest->status === 'for_release';
@endphp

<div class="page-head">
    <div>
        <div class="page-title">{{ $supplyRequest->requestNo() }}</div>
        <div class="page-sub">Supply request</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('requests.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>

        @if($isAdmin && $supplyRequest->status === 'pending')
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#declineModal">Decline</button>
            <form method="POST" action="{{ route('requests.approve', $supplyRequest) }}"
                  onsubmit="return confirm('Approve this request?');">
                @csrf
                <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Approve</button>
            </form>
        @endif

        @if($isAdmin && $supplyRequest->status === 'approved')
            <form method="POST" action="{{ route('requests.forRelease', $supplyRequest) }}">
                @csrf
                <button class="btn btn-primary"><i class="bi bi-box-seam"></i> Mark for Release</button>
            </form>
        @endif

        @if($canRelease)
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#releaseModal">
                <i class="bi bi-box-arrow-up"></i> Release Items
            </button>
        @endif
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="row g-3">
    {{-- Details --}}
    <div class="col-lg-8">
        <div class="dpanel mb-3" id="releaseForm">
            <div class="detail-grid mb-4">
                <div>
                    <div class="detail-label">Status</div>
                    <span class="req-badge req-{{ $supplyRequest->status }}">{{ $supplyRequest->statusLabel() }}</span>
                </div>
                <div>
                    <div class="detail-label">Date Requested</div>
                    <div class="detail-value">{{ ($supplyRequest->request_date ?? $supplyRequest->created_at)->format('M d, Y') }}</div>
                </div>
                <div>
                    <div class="detail-label">Requested by</div>
                    <div class="detail-value">{{ $supplyRequest->personnel->name ?? '—' }}</div>
                    <div class="cell-sub">{{ $supplyRequest->personnel->position ?? '' }}</div>
                </div>
                <div>
                    <div class="detail-label">Purpose</div>
                    <div class="detail-value">{{ $supplyRequest->purpose ?? '—' }}</div>
                </div>
            </div>

            <div class="detail-label mb-2">Items Requested</div>
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th class="text-center">Requested</th>
                            <th class="text-center">Released</th>
                            <th class="text-center">Remaining</th>
                            @if($canRelease)
                                <th class="text-center">In Stock</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($supplyRequest->items as $line)
                            @php $stock = $line->supplyItem->balance_per_card ?? 0; @endphp
                            <tr>
                                <td>{{ $line->supplyItem->description ?? '—' }}</td>
                                <td class="cell-sub">{{ $line->supplyItem->category->name ?? '—' }}</td>
                                <td class="text-center">{{ $line->quantity }} {{ $line->supplyItem->unit ?? '' }}</td>
                                <td class="text-center">{{ $line->released_quantity }}</td>
                                <td class="text-center {{ $line->remainingQuantity() > 0 ? 'fw-semibold' : 'text-muted' }}">
                                    {{ $line->remainingQuantity() }}
                                </td>
                                @if($canRelease)
                                    <td class="text-center {{ $stock < $line->remainingQuantity() ? 'text-danger fw-bold' : 'text-muted' }}">{{ $stock }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

                {{-- Release modal --}}
        @if($canRelease)
        <div class="modal fade" id="releaseModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <form method="POST" action="{{ route('requests.release', $supplyRequest) }}" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <div>
                            <h6 class="modal-title fw-bold">Release Items</h6>
                            <div class="cell-sub">{{ $supplyRequest->requestNo() }} · {{ $supplyRequest->personnel->name ?? '—' }}</div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <p class="cell-sub mb-3">Enter how much is being handed out now. Leave an item at 0 to release it later.</p>

                        <div class="row">
                            <div class="col-md-5 mb-3">
                                <label class="form-label">Date Released</label>
                                <input type="date" name="date_released" class="form-control"
                                    value="{{ old('date_released', now()->toDateString()) }}"
                                    max="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="col-md-7 mb-3">
                                <label class="form-label">Received by <span class="text-muted">(optional)</span></label>
                                <input type="text" name="received_by" class="form-control"
                                    value="{{ old('received_by', $supplyRequest->personnel->name ?? '') }}">
                            </div>
                        </div>

                        <div class="table-responsive mb-3">
                            <table class="table table-sm align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th class="text-center">In Stock</th>
                                        <th class="text-center">Remaining</th>
                                        <th style="width:130px;">Release now</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($supplyRequest->items as $i => $line)
                                        @php $stock = $line->supplyItem->balance_per_card ?? 0; @endphp
                                        <tr>
                                            <td>
                                                {{ $line->supplyItem->description ?? '—' }}
                                                <input type="hidden" name="lines[{{ $i }}][request_item_id]" value="{{ $line->id }}">
                                            </td>
                                            <td class="text-center {{ $stock < $line->remainingQuantity() ? 'text-danger fw-bold' : 'text-muted' }}">{{ $stock }}</td>
                                            <td class="text-center">{{ $line->remainingQuantity() }} {{ $line->supplyItem->unit ?? '' }}</td>
                                            <td>
                                                <input type="number" name="lines[{{ $i }}][quantity]"
                                                    class="form-control form-control-sm" inputmode="numeric"
                                                    min="0" max="{{ $line->remainingQuantity() }}"
                                                    value="{{ $line->remainingQuantity() }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div>
                            <label class="form-label">Remarks <span class="text-muted">(optional)</span></label>
                            <input type="text" name="remarks" class="form-control" value="{{ old('remarks') }}">
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary"><i class="bi bi-box-arrow-up"></i> Release Items</button>
                    </div>
                </form>
            </div>
        </div>
        @endif

               {{-- Release history --}}
        @if($supplyRequest->releases->count())
        <div class="dpanel">
            <div class="dpanel-title mb-3">Release History</div>
            @foreach($supplyRequest->releases->sortByDesc('date_released') as $release)
                <div class="timeline-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="timeline-when">{{ $release->date_released->format('M d, Y') }}</div>
                            <div class="timeline-by">
                                Received by {{ $release->received_by ?? '—' }} ·
                                released by {{ $release->releasedBy->name ?? '—' }}
                            </div>
                            @if($release->remarks)
                                <div class="timeline-by">{{ $release->remarks }}</div>
                            @endif
                        </div>
                        <a href="{{ route('withdrawals.receipt', $release) }}" target="_blank"
                           class="btn btn-sm btn-outline-primary" title="Print receipt">
                            <i class="bi bi-printer"></i>
                        </a>
                    </div>
                    <div class="mt-2 small">
                        @foreach($release->items as $ri)
                            <div class="text-muted">
                                {{ $ri->supplyItem->description ?? '—' }} — {{ $ri->quantity }} {{ $ri->supplyItem->unit ?? '' }}
                            </div>
                        @endforeach
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
                <div class="timeline-when">{{ ($supplyRequest->request_date ?? $supplyRequest->created_at)->format('M d, Y') }}</div>
                <div class="timeline-by">by {{ $supplyRequest->personnel->name ?? '—' }}</div>
            </div>

            <div class="timeline-item">
                <div class="detail-label">Encoded</div>
                <div class="timeline-when">{{ $supplyRequest->created_at->format('M d, Y g:i A') }}</div>
            </div>

            @if($supplyRequest->status === 'pending')
                <div class="timeline-item">
                    <div class="detail-label">Waiting for approval</div>
                    <div class="timeline-by">No action taken yet.</div>
                </div>
            @elseif($supplyRequest->status === 'declined')
                <div class="timeline-item">
                    <div class="detail-label">Declined</div>
                    <div class="timeline-when">{{ $supplyRequest->reviewed_at?->format('M d, Y g:i A') }}</div>
                    <div class="timeline-by">by {{ $supplyRequest->reviewedBy->name ?? '—' }}</div>
                    @if($supplyRequest->decline_reason)
                        <div class="timeline-by mt-1">Reason: {{ $supplyRequest->decline_reason }}</div>
                    @endif
                </div>
            @else
                <div class="timeline-item">
                    <div class="detail-label">Approved</div>
                    <div class="timeline-when">{{ $supplyRequest->reviewed_at?->format('M d, Y g:i A') }}</div>
                    <div class="timeline-by">by {{ $supplyRequest->reviewedBy->name ?? '—' }}</div>
                </div>

                @if($supplyRequest->status === 'approved')
                    <div class="timeline-item">
                        <div class="detail-label">Preparing items</div>
                        <div class="timeline-by">Mark for release once the items are ready.</div>
                    </div>
                @endif

                @foreach($supplyRequest->releases->sortBy('date_released') as $release)
                    <div class="timeline-item">
                        <div class="detail-label">Released</div>
                        <div class="timeline-when">{{ $release->date_released->format('M d, Y') }}</div>
                        <div class="timeline-by">{{ $release->items->sum('quantity') }} {{ Str::plural('item', $release->items->sum('quantity')) }} handed out</div>
                    </div>
                @endforeach

                @if($supplyRequest->status === 'for_release')
                    <div class="timeline-item">
                        <div class="detail-label">Awaiting release</div>
                        <div class="timeline-by">
                            {{ $supplyRequest->items->sum(fn ($l) => $l->remainingQuantity()) }} still to hand out.
                        </div>
                    </div>
                @elseif($supplyRequest->status === 'completed')
                    <div class="timeline-item">
                        <div class="detail-label">Completed</div>
                        <div class="timeline-by">All items handed out.</div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- Decline modal --}}
@if($isAdmin && $supplyRequest->status === 'pending')
<div class="modal fade" id="declineModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('requests.decline', $supplyRequest) }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Decline {{ $supplyRequest->requestNo() }}</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Reason <span class="text-muted">(optional)</span></label>
                <input type="text" name="decline_reason" class="form-control" placeholder="e.g. Item not available">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-danger">Decline Request</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection