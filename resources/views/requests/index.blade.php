@extends('layouts.app')
@section('title', 'Requests')

@section('content')
<div class="page-head">
    <div>
        <div class="page-title">Supply Requests</div>
        <div class="page-sub">Encode, review, and approve supply requests.</div>
    </div>
    <a href="{{ route('requests.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> New Request</a>
</div>

<div class="card shadow-sm">
    <form method="GET" class="list-toolbar">
        <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                   placeholder="Search request number or name…">
        </div>
        <select name="status" class="form-select" style="width:auto;" onchange="this.form.submit()">
            <option value="all"         {{ $status === 'all'         ? 'selected' : '' }}>All statuses</option>
            <option value="pending"     {{ $status === 'pending'     ? 'selected' : '' }}>Pending</option>
            <option value="approved"    {{ $status === 'approved'    ? 'selected' : '' }}>Approved</option>
            <option value="declined"    {{ $status === 'declined'    ? 'selected' : '' }}>Declined</option>
            <option value="for_release" {{ $status === 'for_release' ? 'selected' : '' }}>For Release</option>
            <option value="completed"   {{ $status === 'completed'   ? 'selected' : '' }}>Completed</option>
            
                        
        </select>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-nowrap">Request No.</th>
                    <th class="text-nowrap">Requested by</th>
                    <th>Items</th>
                    <th class="text-nowrap">Status</th>
                    <th class="text-nowrap">Date Requested</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($requests as $req)
                    @php $first = $req->items->first(); @endphp
                    <tr>
                        <td class="text-nowrap"><a href="{{ route('requests.show', $req) }}" class="req-no">{{ $req->requestNo() }}</a></td>
                        <td class="text-nowrap">{{ $req->personnel->name ?? '—' }}</td>
                        <td>
                            <div class="cell-main text-truncate" style="max-width: 320px;"
                                 title="{{ $first->supplyItem->description ?? '' }}">
                                {{ $first->supplyItem->description ?? '—' }}
                            </div>
                            <div class="cell-sub">
                                @if($req->items->count() > 1)
                                    +{{ $req->items->count() - 1 }} more {{ Str::plural('item', $req->items->count() - 1) }}
                                @else
                                    {{ $first->quantity ?? '' }} {{ $first->supplyItem->unit ?? '' }}
                                @endif
                            </div>
                        </td>
                        <td class="text-nowrap"><span class="req-badge req-{{ $req->status }}">{{ $req->statusLabel() }}</span></td>
                        <td class="text-nowrap">{{ ($req->request_date ?? $req->created_at)->format('M d, Y') }}</td>
                        <td class="text-end">
                            <a href="{{ route('requests.show', $req) }}" class="btn btn-sm btn-outline-primary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="dash-empty">No requests found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $requests->links() }}</div>
@endsection