@extends('layouts.app')
@section('title', $supplyRequest->requestNo())

@section('content')
@php $isAdmin = Auth::user()->role === 'admin'; @endphp

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
                  onsubmit="return confirm('Approve this request? Stock will be deducted.');">
                @csrf
                <button class="btn btn-primary"><i class="bi bi-check2-circle"></i> Approve</button>
            </form>
        @endif
        @if($supplyRequest->withdrawal_id)
            <a href="{{ route('withdrawals.receipt', $supplyRequest->withdrawal_id) }}" target="_blank" class="btn btn-outline-primary">
                <i class="bi bi-printer"></i> Print Receipt
            </a>
        @endif
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row g-3">
    {{-- Details --}}
    <div class="col-lg-8">
        <div class="dpanel">
            <div class="detail-grid mb-4">
                <div>
                    <div class="detail-label">Status</div>
                    <span class="req-badge req-{{ $supplyRequest->status }}">{{ ucfirst($supplyRequest->status) }}</span>
                </div>
                <div>
                    <div class="detail-label">Date Requested</div>
                    <div class="detail-value">{{ ($supplyRequest->request_date ?? $supplyRequest->created_at)->format('M d, Y') }}</div>
                </div>
                <div>
                    <div class="detail-label">Requester</div>
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
                            @if($supplyRequest->status === 'pending')
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
                                @if($supplyRequest->status === 'pending')
                                    <td class="text-center {{ $stock < $line->quantity ? 'text-danger fw-bold' : 'text-muted' }}">{{ $stock }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Timeline --}}
    <div class="col-lg-4">
        <div class="dpanel">
            <div class="dpanel-title mb-2">Timeline</div>

            <div class="timeline-item">
                <div class="detail-label">Submitted</div>
                <div class="timeline-when">{{ $supplyRequest->created_at->format('M d, Y g:i A') }}</div>
            </div>

            @if($supplyRequest->status !== 'pending')
                <div class="timeline-item">
                    <div class="detail-label">{{ ucfirst($supplyRequest->status) }}</div>
                    <div class="timeline-when">{{ $supplyRequest->reviewed_at?->format('M d, Y g:i A') }}</div>
                    <div class="timeline-by">by {{ $supplyRequest->reviewedBy->name ?? '—' }}</div>
                    @if($supplyRequest->decline_reason)
                        <div class="timeline-by mt-1">Reason: {{ $supplyRequest->decline_reason }}</div>
                    @endif
                </div>
            @else
                <div class="timeline-item">
                    <div class="detail-label">Waiting for approval</div>
                    <div class="timeline-by">No action taken yet.</div>
                </div>
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