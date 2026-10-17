@extends('layouts.app')
@section('title', 'Physical Count')

@section('content')
<div class="page-head">
    <div>
        <div class="page-title">Physical Count</div>
        <div class="page-sub">Walk the storeroom, scan what you find, and see what the records expect.</div>
    </div>
    @if($openCount)
        <a href="{{ route('counts.show', $openCount) }}" class="btn btn-primary">
            <i class="bi bi-upc-scan"></i> Continue {{ $openCount->countNo() }}
        </a>
    @else
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#startModal">
            <i class="bi bi-plus-lg"></i> Start a Count
        </button>
    @endif
</div>

@if($openCount)
    <div class="alert alert-info">
        <strong>{{ $openCount->countNo() }}</strong> is in progress, started
        {{ $openCount->created_at->format('M d, Y g:i A') }}
        by {{ $openCount->startedBy->name ?? '—' }}.
        Only one count can run at a time.
    </div>
@endif

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-nowrap">Count No.</th>
                    <th class="text-nowrap">Date</th>
                    <th class="text-nowrap">Status</th>
                    <th class="text-center">Tags Scanned</th>
                    <th class="text-nowrap">Started by</th>
                    <th class="text-nowrap">Ended</th>
                    <th>Remarks</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($counts as $count)
                    <tr>
                        <td class="text-nowrap">
                            <a href="{{ route('counts.show', $count) }}" class="req-no">{{ $count->countNo() }}</a>
                        </td>
                        <td class="text-nowrap">{{ $count->count_date->format('M d, Y') }}</td>
                        <td class="text-nowrap">
                            <span class="req-badge {{ $count->badgeClass() }}">{{ $count->statusLabel() }}</span>
                        </td>
                        <td class="text-center">{{ $count->scans_count }}</td>
                        <td class="text-nowrap">{{ $count->startedBy->name ?? '—' }}</td>
                        <td class="text-nowrap cell-sub">
                            @if($count->closed_at)
                                {{ $count->closed_at->format('M d, Y') }}
                                <div>by {{ $count->closedBy->name ?? '—' }}</div>
                            @else
                                —
                            @endif
                        </td>
                        <td class="cell-sub">{{ $count->remarks ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('counts.show', $count) }}" class="btn btn-sm btn-outline-primary" title="View">
                                <i class="bi bi-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="dash-empty">No counts yet. Start one to begin.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $counts->links() }}</div>

{{-- Start a count --}}
<div class="modal fade" id="startModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('counts.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Start a Physical Count</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Date of count</label>
                    <input type="date" name="count_date" class="form-control"
                           value="{{ now()->toDateString() }}" max="{{ now()->toDateString() }}" required>
                </div>
                <div class="mb-0">
                    <label class="form-label">Remarks <span class="cell-sub">(optional)</span></label>
                    <input type="text" name="remarks" class="form-control"
                           placeholder="e.g. Year-end count of rescue equipment">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary">Start</button>
            </div>
        </form>
    </div>
</div>
@endsection
