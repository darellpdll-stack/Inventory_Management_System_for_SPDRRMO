@extends('layouts.app')
@section('title', 'New Withdrawal')

@push('scripts')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <h4 class="fw-bold mb-3">Record a Withdrawal</h4>

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
                <form method="POST" action="{{ route('withdrawals.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Withdrawn By</label>
                            <input type="text" name="withdrawn_by" value="{{ old('withdrawn_by') }}"
                                   class="form-control" placeholder="e.g. Cristina Amor" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Date Withdrawn</label>
                            <input type="date" name="date_withdrawn" value="{{ old('date_withdrawn', date('Y-m-d')) }}"
                                   class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Date Returned <span class="text-muted">(optional)</span></label>
                            <input type="date" name="date_returned" value="{{ old('date_returned') }}" class="form-control">
                        </div>
                    </div>

                    <hr>
                    <label class="form-label fw-semibold">Items Withdrawn</label>

                    <div id="itemRows">
                        <div class="row g-2 mb-2 item-row">
                            <div class="col-md-8">
                                <select name="items[0][supply_item_id]" class="form-select item-select" required>
                                    <option value="">Select item</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->description }} ({{ $item->category->name }}) — {{ $item->balance_per_card }} {{ $item->unit }} available
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="items[0][quantity]" class="form-control" min="1" placeholder="Qty" required>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-danger w-100 remove-row">×</button>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="addRow" class="btn btn-outline-secondary btn-sm mb-3">+ Add another item</button>

                    <div class="mb-3">
                        <label class="form-label">Remark <span class="text-muted">(optional)</span></label>
                        <input type="text" name="remark" value="{{ old('remark') }}" class="form-control">
                    </div>

                    <button class="btn btn-primary">Record Withdrawal</button>
                    <a href="{{ route('withdrawals.index') }}" class="btn btn-light">Cancel</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let rowIndex = 1;
    const itemOptions = `@foreach($items as $item)<option value="{{ $item->id }}">{{ $item->description }} ({{ $item->category->name }}) — {{ $item->balance_per_card }} {{ $item->unit }} available</option>@endforeach`;

    // turn a select into a searchable Tom Select
    function makeSearchable(el) {
        new TomSelect(el, {
            placeholder: 'Search item...',
            searchField: 'text',
        });
    }

    // initialize the first row's dropdown
    document.querySelectorAll('.item-select').forEach(makeSearchable);

    document.getElementById('addRow').addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 item-row';
        row.innerHTML = `
            <div class="col-md-8">
                <select name="items[${rowIndex}][supply_item_id]" class="form-select item-select" required>
                    <option value="">Select item</option>${itemOptions}
                </select>
            </div>
            <div class="col-md-3">
                <input type="number" name="items[${rowIndex}][quantity]" class="form-control" min="1" placeholder="Qty" required>
            </div>
            <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger w-100 remove-row">×</button>
            </div>`;
        document.getElementById('itemRows').appendChild(row);
        // make the new row's dropdown searchable
        makeSearchable(row.querySelector('.item-select'));
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