@extends('layouts.app')
@section('title', 'Withdrawal Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Withdrawal Record</h4>
            <a href="{{ route('withdrawals.index') }}" class="btn btn-light">← Back</a>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <div class="row mb-2">
                    <div class="col-md-6"><strong>Withdrawn By:</strong> {{ $withdrawal->withdrawn_by }}</div>
                    <div class="col-md-6"><strong>Date Withdrawn:</strong> {{ $withdrawal->date_withdrawn->format('M d, Y') }}</div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-6"><strong>Date Returned:</strong> {{ $withdrawal->date_returned ? $withdrawal->date_returned->format('M d, Y') : '—' }}</div>
                    <div class="col-md-6"><strong>Recorded By:</strong> {{ $withdrawal->recordedBy->name ?? '—' }}</div>
                </div>
                <div class="row">
                    <div class="col-12"><strong>Remark:</strong> {{ $withdrawal->remark ?? '—' }}</div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header fw-semibold">Items Withdrawn</div>
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
                        @foreach($withdrawal->items as $line)
                        <tr>
                            <td>{{ $line->supplyItem->description ?? '—' }}</td>
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