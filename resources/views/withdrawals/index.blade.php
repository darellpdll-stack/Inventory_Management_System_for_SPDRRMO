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

<div class="mt-3">{{ $releases->links() }}</div>
@endsection