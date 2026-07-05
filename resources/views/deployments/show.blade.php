@extends('layouts.app')
@section('title', 'Deployment Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Deployment Record</h4>
            <a href="{{ route('deployments.index') }}" class="btn btn-light">← Back</a>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-6"><strong>Destination:</strong> {{ $deployment->destination }}</div>
                    <div class="col-md-6"><strong>Date:</strong> {{ $deployment->deployed_at->format('M d, Y') }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6"><strong>Purpose:</strong> {{ $deployment->purpose ?? '—' }}</div>
                    <div class="col-md-6"><strong>Status:</strong>
                        <span class="badge bg-info text-dark">{{ ucfirst($deployment->status) }}</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6"><strong>Authorized By:</strong> {{ $deployment->authorized_by }}</div>
                    <div class="col-md-6"><strong>Released By:</strong> {{ $deployment->releasedBy->name ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Items Deployed</div>
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Item</th>
                            <th>Category</th>
                            <th class="text-end">Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deployment->items as $line)
                        <tr>
                            <td>{{ $line->supplyItem->item_name ?? '—' }}</td>
                            <td>{{ $line->supplyItem->category->name ?? '—' }}</td>
                            <td class="text-end">{{ $line->quantity }} {{ $line->supplyItem->unit ?? '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection