@extends('layouts.app')
@section('title', 'Request Supplies')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm mt-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-1">Request Supplies</h5>
                <p class="text-muted small mb-4">Your request will be reviewed by the administrator before release.</p>

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('requests.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Your Name</label>
                        <select name="personnel_id" class="form-select" required>
                            <option value="">Select your name</option>
                            @foreach($personnel as $person)
                                <option value="{{ $person->id }}" {{ old('personnel_id') == $person->id ? 'selected' : '' }}>
                                    {{ $person->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <label class="form-label">Items Requested</label>
                    <div id="itemRows">
                        <div class="row g-2 mb-2 item-row">
                            <div class="col-8">
                                <select name="items[0][supply_item_id]" class="form-select" required>
                                    <option value="">Select item</option>
                                    @foreach($items as $item)
                                        <option value="{{ $item->id }}">
                                            {{ $item->description }} — {{ $item->balance_per_card }} {{ $item->unit }} available
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <input type="number" name="items[0][quantity]" class="form-control" min="1" placeholder="Qty" required>
                            </div>
                            <div class="col-1 px-0">
                                <button type="button" class="btn btn-outline-danger w-100 remove-row">×</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="addRow" class="btn btn-outline-secondary btn-sm mb-3">+ Add another item</button>

                    <div class="mb-4">
                        <label class="form-label">Purpose <span class="text-muted">(optional)</span></label>
                        <input type="text" name="purpose" value="{{ old('purpose') }}" class="form-control">
                    </div>

                    <button class="btn btn-primary w-100">Submit Request</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let rowIndex = 1;
    const itemOptions = `@foreach($items as $item)<option value="{{ $item->id }}">{{ $item->description }} — {{ $item->balance_per_card }} {{ $item->unit }} available</option>@endforeach`;

    document.getElementById('addRow').addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 item-row';
        row.innerHTML = `
            <div class="col-8">
                <select name="items[${rowIndex}][supply_item_id]" class="form-select" required>
                    <option value="">Select item</option>${itemOptions}
                </select>
            </div>
            <div class="col-3">
                <input type="number" name="items[${rowIndex}][quantity]" class="form-control" min="1" placeholder="Qty" required>
            </div>
            <div class="col-1 px-0">
                <button type="button" class="btn btn-outline-danger w-100 remove-row">×</button>
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