@extends('layouts.app')
@section('title', 'Supply Requests')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Supply Requests</h4>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('requests.qr') }}" class="btn btn-sm btn-outline-primary" target="_blank">QR Code</a>
        <form method="GET">
            <select name="status" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                <option value="pending"  {{ $status === 'pending'  ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="declined" {{ $status === 'declined' ? 'selected' : '' }}>Declined</option>
                <option value="all"      {{ $status === 'all'      ? 'selected' : '' }}>All</option>
            </select>
        </form>
    </div>
</div>

@forelse($requests as $req)
<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start mb-2">
            <div>
                <span class="fw-bold">{{ $req->personnel->name ?? '—' }}</span>
                <span class="text-muted small ms-2">{{ $req->created_at->format('M d, Y g:i A') }}</span>
            </div>
            <div>
                @if($req->status === 'pending')
                    <span class="badge bg-warning text-dark">Pending</span>
                @elseif($req->status === 'approved')
                    <span class="badge bg-success">Approved</span>
                @else
                    <span class="badge" style="background: var(--danger);">Declined</span>
                @endif
            </div>
        </div>

        @if($req->purpose)
            <div class="small text-muted mb-2">Purpose: {{ $req->purpose }}</div>
        @endif

        <table class="table table-sm align-middle mb-2">
            <thead class="table-light">
                <tr>
                    <th>Item</th>
                    <th class="text-center">Requested</th>
                    <th class="text-center">In Stock</th>
                </tr>
            </thead>
            <tbody>
                @foreach($req->items as $line)
                <tr>
                    <td>{{ $line->supplyItem->description ?? '—' }}</td>
                    <td class="text-center">{{ $line->quantity }} {{ $line->supplyItem->unit ?? '' }}</td>
                    <td class="text-center">
                        @php $stock = $line->supplyItem->balance_per_card ?? 0; @endphp
                        <span class="{{ $stock < $line->quantity ? 'text-danger fw-bold' : 'text-muted' }}">{{ $stock }}</span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if($req->status === 'pending')
            <div class="d-flex gap-2">
                <form method="POST" action="{{ route('requests.approve', $req) }}"
                      onsubmit="return confirm('Approve this request? Stock will be deducted.');">
                    @csrf
                    <button class="btn btn-sm btn-primary">Approve</button>
                </form>
                <form method="POST" action="{{ route('requests.decline', $req) }}" class="d-flex gap-2">
                    @csrf
                    <input type="text" name="decline_reason" class="form-control form-control-sm"
                           placeholder="Reason (optional)" style="width:220px;">
                    <button class="btn btn-sm btn-outline-danger">Decline</button>
                </form>
            </div>
        @else
            <div class="small text-muted">
                {{ ucfirst($req->status) }} by {{ $req->reviewedBy->name ?? '—' }}
                on {{ $req->reviewed_at?->format('M d, Y g:i A') }}
                @if($req->decline_reason) — {{ $req->decline_reason }} @endif
            </div>
        @endif
    </div>
</div>
@empty
<div class="card shadow-sm">
    <div class="card-body text-center text-muted py-4">No {{ $status === 'all' ? '' : $status }} requests.</div>
</div>
@endforelse

<div class="mt-3">{{ $requests->links() }}</div>
@endsection