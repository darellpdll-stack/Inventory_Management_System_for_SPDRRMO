@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
@php
    $hour = now()->hour;
    $greeting = $hour < 12 ? 'morning' : ($hour < 18 ? 'afternoon' : 'evening');
    $isAdmin = Auth::user()->role === 'admin';
    $dotColors = ['#eea316', '#85b7eb', '#ffffff', '#f09595'];
    $statusTotal = max($ok + $low + $outOfStock, 1);
@endphp

{{-- Page header --}}
<div class="page-head">
    <div>
        <div class="page-title">{{ $isAdmin ? 'Admin dashboard' : 'Dashboard' }}</div>
        <div class="page-sub">Good {{ $greeting }}, {{ Auth::user()->name }}. Here's your supply and property overview.</div>
    </div>
    <div class="page-meta">
        <span class="role-pill">{{ $isAdmin ? 'Administrator' : 'Staff' }}</span>
        <span>{{ now()->format('l, M j, Y') }}</span>
    </div>
</div>

{{-- Featured card + stat cards --}}
<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="hero-card">
            <div class="hero-top">
                <div>
                    <div class="hero-label">Total supplies</div>
                    <div class="hero-value">{{ $totalItems }}</div>
                    <div class="hero-sub">across {{ $byCategory->count() }} categories</div>
                </div>
                <div class="hero-chip"><i class="bi bi-box-seam"></i></div>
            </div>

            <div class="hero-divider"></div>
            <div class="hero-label mb-2">By category</div>
            <div class="hero-cats">
                @foreach($byCategory as $i => $cat)
                    <a href="{{ route('supplies.index', ['category' => $cat->id]) }}" class="hero-cat">
                        <span><span class="hero-dot" @style(['background: ' . $dotColors[$i % count($dotColors)]])></span>{{ $cat->name }}</span>
                        <span class="fw-bold">{{ $cat->items_count }} <i class="bi bi-arrow-right-short"></i></span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="d-grid gap-3 h-100">
            <a href="{{ route('property.index') }}" class="stat-mini">
                <div>
                    <div class="sm-label">Total properties</div>
                    <div class="sm-value">{{ $totalProperties }}</div>
                    <div class="sm-sub">{{ $propertyRecords }} {{ Str::plural('record', $propertyRecords) }}</div>
                </div>
                <div class="icon-chip chip-blue"><i class="bi bi-hdd-stack"></i></div>
            </a>
            <a href="{{ route('supplies.index') }}" class="stat-mini">
                <div>
                    <div class="sm-label">Low supplies</div>
                    <div class="sm-value" style="color: var(--danger);">{{ $lowStockCount }}</div>
                    <div class="sm-sub">{{ $lowStockCount === 0 ? 'All above minimum' : 'At or below minimum' }}</div>
                </div>
                <div class="icon-chip chip-red"><i class="bi bi-exclamation-triangle"></i></div>
            </a>
            <a href="{{ route('requests.index') }}" class="stat-mini">
                <div>
                    <div class="sm-label">Total requests</div>
                    <div class="sm-value">{{ $totalRequests }}</div>
                    <div class="sm-sub">{{ $pendingRequests }} pending</div>
                </div>
                <div class="icon-chip chip-yellow"><i class="bi bi-clipboard-check"></i></div>
            </a>
        </div>
    </div>
</div>

{{-- Stock status --}}
<div class="dpanel mb-3" style="height:auto;">
    <div class="dpanel-head">
        <div class="dpanel-title">Stock status</div>
        <div class="activity-date">{{ $totalItems }} items</div>
    </div>
    <div class="status-bar">
        <div @style(['width: ' . ($ok / $statusTotal * 100) . '%', 'background: var(--sidebar)'])></div>
        <div @style(['width: ' . ($low / $statusTotal * 100) . '%', 'background: var(--accent)'])></div>
        <div @style(['width: ' . ($outOfStock / $statusTotal * 100) . '%', 'background: var(--danger)'])></div>
    </div>
    <div class="status-legend">
        <span><span class="legend-swatch" style="background: var(--sidebar);"></span>Available {{ $ok }}</span>
        <span><span class="legend-swatch" style="background: var(--accent);"></span>Low {{ $low }}</span>
        <span><span class="legend-swatch" style="background: var(--danger);"></span>Out of stock {{ $outOfStock }}</span>
    </div>
</div>

{{-- Activity panels --}}
{{-- Recent withdrawals --}}
<div class="dpanel mb-3" style="height:auto;">
    <div class="dpanel-head">
        <div class="dpanel-title">Recent withdrawals</div>
        <a href="{{ route('withdrawals.index') }}" class="dpanel-link">View all</a>
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Item</th>
                    <th>Category</th>
                    <th class="text-center">Quantity</th>
                    <th>Withdrawn by</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentWithdrawals as $line)
                <tr>
                    <td>{{ $line->supplyItem->description ?? '—' }}</td>
                    <td class="text-muted small">{{ $line->supplyItem->category->name ?? '—' }}</td>
                    <td class="text-center">{{ $line->quantity }} {{ $line->supplyItem->unit ?? '' }}</td>
                    <td>{{ $line->withdrawal->withdrawn_by ?? '—' }}</td>
                    <td class="text-muted small">{{ optional($line->withdrawal)->date_withdrawn?->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="dash-empty">No withdrawals yet. They'll show here once requests are approved.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Recent requests --}}
<div class="dpanel" style="height:auto;">
    <div class="dpanel-head">
        <div class="dpanel-title">Recent requests</div>
        <a href="{{ route('requests.index') }}" class="dpanel-link">View all</a>
    </div>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Personnel</th>
                    <th>Items</th>
                    <th>Purpose</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentRequests as $req)
                <tr>
                    <td>{{ $req->personnel->name ?? '—' }}</td>
                    <td class="small">
                        {{ $req->items->map(fn ($i) => ($i->supplyItem->description ?? '—') . ' (' . $i->quantity . ')')->take(2)->join(', ') }}
                        @if($req->items->count() > 2)
                            <span class="text-muted">+{{ $req->items->count() - 2 }} more</span>
                        @endif
                    </td>
                    <td class="text-muted small">{{ $req->purpose ?? '—' }}</td>
                    <td><span class="req-badge req-{{ $req->status }}">{{ ucfirst($req->status) }}</span></td>
                    <td class="text-muted small">{{ $req->created_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="dash-empty">No requests yet. Add one from the Requests page.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection