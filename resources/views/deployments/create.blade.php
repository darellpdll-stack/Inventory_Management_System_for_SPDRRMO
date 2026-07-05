@extends('layouts.app')
@section('title', 'New Deployment')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <h4 class="fw-bold mb-3">Record a Deployment</h4>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('deployments.store') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Destination</label>
                            <input type="text" name="destination" value="{{ old('destination') }}"
                                   class="form-control" placeholder="e.g. Brgy. Rizal, Sorsogon" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Purpose / Event <span class="text-muted">(optional)</span></label>
                            <input type="text" name="purpose" value="{{ old('purpose') }}"
                                   class="form-control" placeholder="e.g. Typhoon response">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Authorized By</label>
                            <input type="text" name="authorized_by" value="{{ old('authorized_by') }}"
                                   class="form-control" placeholder="e.g. SPDRRMO Chief" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="deployed_at" value="{{ old('deployed_at', date('Y-m-d')) }}"
                                   class="form-control" required>
                        </div>
                    </div>

                    <hr>
                    <label class="form-label fw-semibold">Items to Deploy</label>

                    <div id="itemRows">
                        <div class="row g-2 mb-2 item-row">
                            <div class="col-md-7">
                                <select name="items[0][supply_item_id]" class="form-select" required>
                                    <option value="">Select item</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->item_name }} ({{ $item->category->name }}) — {{ $item->current_stock }} {{ $item->unit }} left
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="items[0][quantity]" class="form-control" min="1" placeholder="Qty" required>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger w-100 remove-row">Remove</button>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="addRow" class="btn btn-outline-secondary btn-sm mb-3">+ Add another item</button>

                    <div>
                        <button class="btn btn-primary">Record Deployment</button>
                        <a href="{{ route('deployments.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let rowIndex = 1;
    const itemOptions = `@foreach($items as $item)<option value="{{ $item->id }}">{{ $item->item_name }} ({{ $item->category->name }}) — {{ $item->current_stock }} {{ $item->unit }} left</option>@endforeach`;

    document.getElementById('addRow').addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 item-row';
        row.innerHTML = `
            <div class="col-md-7">
                <select name="items[${rowIndex}][supply_item_id]" class="form-select" required>
                    <option value="">Select item</option>${itemOptions}
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" name="items[${rowIndex}][quantity]" class="form-control" min="1" placeholder="Qty" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger w-100 remove-row">Remove</button>
            </div>`;
        document.getElementById('itemRows').appendChild(row);
        rowIndex++;
    });

    document.getElementById('itemRows').addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-row')) {
            const rows = document.querySelectorAll('.item-row');
            if (rows.length > 1) e.target.closest('.item-row').remove();
        }
    });
</script>
@endpush