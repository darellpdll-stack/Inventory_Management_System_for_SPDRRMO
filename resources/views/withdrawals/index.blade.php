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

<form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
        <select name="category" class="form-select" onchange="this.form.submit()">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-5">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search person or item...">
    </div>
    <div class="col-md-3">
        <button class="btn btn-outline-secondary w-100">Search</button>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>No.</th>
                    <th class="text-nowrap">Request No.</th>
                    <th class="text-nowrap">Unit &amp; Quantity</th>
                    <th>Item Description</th>
                    <th class="text-nowrap">Withdrawn By</th>
                    <th class="text-nowrap">Date Withdrawn</th>
                    <th class="text-nowrap">Date Returned</th>
                    <th>Remark</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @php $rowNo = ($withdrawals->currentPage() - 1) * $withdrawals->perPage() + 1; $groupIndex = 0; @endphp
                @forelse($withdrawals as $w)
                    @php $groupIndex++; @endphp
                    @foreach($w->items as $i => $line)
                    <tr class="{{ $groupIndex % 2 === 0 ? 'group-tint' : '' }} {{ $i === 0 && $groupIndex > 1 ? 'group-start' : '' }}">
                        <td>{{ $rowNo++ }}</td>
                        <td class="text-nowrap">
                            @if($i === 0)
                                @if($w->supplyRequest)
                                    <a href="{{ route('requests.show', $w->supplyRequest) }}" class="req-no">{{ $w->supplyRequest->requestNo() }}</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            @else
                                <span class="text-muted">"</span>
                            @endif
                        </td>
                        <td class="text-nowrap">{{ $line->quantity }} {{ $line->supplyItem->unit ?? '' }}</td>
                        <td>{{ $line->supplyItem->description ?? '—' }}</td>
                        <td class="text-nowrap">
                            @if($i === 0) {{ $w->withdrawn_by }} @else <span class="text-muted">"</span> @endif
                        </td>
                        <td class="text-nowrap">
                            @if($i === 0) {{ $w->date_withdrawn->format('M d, Y') }} @else <span class="text-muted">"</span> @endif
                        </td>
                        <td class="text-nowrap">
                            @if($i === 0) {{ $w->date_returned ? $w->date_returned->format('M d, Y') : '—' }} @else <span class="text-muted">"</span> @endif
                        </td>
                        <td>
                            @if($i === 0) {{ $w->remark ?? '—' }} @else <span class="text-muted">"</span> @endif
                        </td>
                        <td class="text-end">
                            @if($i === 0)
                                <a href="{{ route('withdrawals.receipt', $w) }}" target="_blank"
                                   class="btn btn-sm btn-outline-primary" title="Print receipt">
                                    <i class="bi bi-printer"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-3">No withdrawals yet. Approve a request and it will appear here.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $withdrawals->links() }}</div>
@endsection