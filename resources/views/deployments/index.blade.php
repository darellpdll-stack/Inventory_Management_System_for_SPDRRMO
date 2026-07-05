@extends('layouts.app')
@section('title', 'Deployments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Supply Deployments</h4>
    <a href="{{ route('deployments.create') }}" class="btn btn-primary">+ New Deployment</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Date</th>
                    <th>Destination</th>
                    <th>Purpose</th>
                    <th>Items</th>
                    <th>Released By</th>
                    <th>Status</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deployments as $d)
                <tr>
                    <td>{{ $d->deployed_at->format('M d, Y') }}</td>
                    <td>{{ $d->destination }}</td>
                    <td>{{ $d->purpose ?? '—' }}</td>
                    <td>{{ $d->items->count() }} item(s)</td>
                    <td>{{ $d->releasedBy->name ?? '—' }}</td>
                    <td><span class="badge bg-info text-dark">{{ ucfirst($d->status) }}</span></td>
                    <td class="text-end">
                        <a href="{{ route('deployments.show', $d) }}" class="btn btn-sm btn-outline-primary">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted py-3">No deployments recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">{{ $deployments->links() }}</div>
@endsection