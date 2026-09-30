@extends('layouts.app')
@section('title', 'New Request')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold mb-0">New Request</h4>
        <div class="text-muted small">Encode a supply request from the printed request form.</div>
    </div>
    <a href="{{ route('requests.index') }}" class="btn btn-light">← Back</a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('requests.store') }}" id="requestForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label class="form-label">Date Requested</label>
                            <input type="date" name="request_date" class="form-control"
                                   value="{{ old('request_date', now()->toDateString()) }}"
                                   max="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-7 mb-3">
                            <label class="form-label">Requested by</label>
                            <select name="personnel_id" class="form-select" required>
                                <option value="">Select employee</option>
                                @foreach($personnel as $person)
                                    <option value="{{ $person->id }}" {{ old('personnel_id') == $person->id ? 'selected' : '' }}>
                                        {{ $person->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Purpose <span class="text-muted">(optional)</span></label>
                        <input type="text" name="purpose" value="{{ old('purpose') }}" class="form-control"
                               placeholder="e.g. Office use for monthly reports">
                    </div>

                    <label class="form-label">Items Requested</label>
                    <div id="itemRows">
                        <div class="row g-2 mb-2 item-row">
                            <div class="col-12 col-sm-7 mb-1 mb-sm-0">
                                <input type="hidden" name="items[0][supply_item_id]" class="item-id">
                                <button type="button" class="btn btn-outline-secondary w-100 text-start pick-item text-truncate">
                                    Choose an item…
                                </button>
                            </div>
                            <div class="col-9 col-sm-4">
                                <div class="input-group">
                                    <input type="number" name="items[0][quantity]" inputmode="numeric"
                                           class="form-control" min="1" placeholder="Qty" required>
                                    <span class="input-group-text unit-label">—</span>
                                </div>
                            </div>
                            <div class="col-3 col-sm-1 px-sm-0">
                                <button type="button" class="btn btn-outline-danger w-100 remove-row">×</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="addRow" class="btn btn-outline-secondary btn-sm mb-4">+ Add another item</button>

                    <div>
                        <button class="btn btn-primary">Save Request</button>
                        <a href="{{ route('requests.index') }}" class="btn btn-light">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Item picker --}}
<div class="modal fade" id="itemPicker" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold">Select an Item</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="pickerSearch" class="form-control mb-2" placeholder="Search item…">
                <select id="pickerCategory" class="form-select form-select-sm mb-3">
                    <option value="">All categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>

                <div class="picker-list" id="pickerList">
                    @foreach($items as $item)
                    <button type="button" class="picker-item"
                            data-id="{{ $item->id }}"
                            data-category="{{ $item->category_id }}"
                            data-unit="{{ $item->unit }}"
                            data-label="{{ $item->description }}">
                        <span>
                            <span class="pi-name">{{ $item->description }}</span>
                            <span class="pi-cat">{{ $item->category->name ?? '—' }}</span>
                        </span>
                        <span class="pi-stock">{{ $item->balance_per_card }} {{ $item->unit }}</span>
                    </button>
                    @endforeach
                </div>
                <div id="pickerEmpty" class="text-muted small text-center py-3 d-none">No matching items.</div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const picker = new bootstrap.Modal(document.getElementById('itemPicker'));
    const search = document.getElementById('pickerSearch');
    const catFilter = document.getElementById('pickerCategory');
    const emptyMsg = document.getElementById('pickerEmpty');
    let activeRow = null;
    let rowIndex = 1;

    const rowTemplate = (i) => `
        <div class="col-12 col-sm-7 mb-1 mb-sm-0">
            <input type="hidden" name="items[${i}][supply_item_id]" class="item-id">
            <button type="button" class="btn btn-outline-secondary w-100 text-start pick-item text-truncate">Choose an item…</button>
        </div>
        <div class="col-9 col-sm-4">
            <div class="input-group">
                <input type="number" name="items[${i}][quantity]" inputmode="numeric" class="form-control" min="1" placeholder="Qty" required>
                <span class="input-group-text unit-label">—</span>
            </div>
        </div>
        <div class="col-3 col-sm-1 px-sm-0">
            <button type="button" class="btn btn-outline-danger w-100 remove-row">×</button>
        </div>`;

    document.getElementById('addRow').addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'row g-2 mb-2 item-row';
        row.innerHTML = rowTemplate(rowIndex++);
        document.getElementById('itemRows').appendChild(row);
    });

    document.getElementById('itemRows').addEventListener('click', function (e) {
        if (e.target.classList.contains('pick-item')) {
            activeRow = e.target.closest('.item-row');
            search.value = '';
            catFilter.value = '';
            applyFilter();
            picker.show();
        }
        if (e.target.classList.contains('remove-row')) {
            const rows = document.querySelectorAll('.item-row');
            if (rows.length > 1) e.target.closest('.item-row').remove();
        }
    });

    document.getElementById('pickerList').addEventListener('click', function (e) {
        const btn = e.target.closest('.picker-item');
        if (!btn || !activeRow) return;
        activeRow.querySelector('.item-id').value = btn.dataset.id;
        activeRow.querySelector('.pick-item').textContent = btn.dataset.label;
        activeRow.querySelector('.unit-label').textContent = btn.dataset.unit || '—';
        picker.hide();
    });

    function applyFilter() {
        const term = search.value.toLowerCase();
        const cat = catFilter.value;
        let visible = 0;
        document.querySelectorAll('.picker-item').forEach(function (el) {
            const show = el.dataset.label.toLowerCase().includes(term) && (!cat || el.dataset.category === cat);
            el.classList.toggle('d-none', !show);
            if (show) visible++;
        });
        emptyMsg.classList.toggle('d-none', visible > 0);
    }

    search.addEventListener('input', applyFilter);
    catFilter.addEventListener('change', applyFilter);

    document.getElementById('requestForm').addEventListener('submit', function (e) {
        const missing = [...document.querySelectorAll('.item-id')].some(i => !i.value);
        if (missing) {
            e.preventDefault();
            alert('Please choose an item for each row.');
        }
    });
})();
</script>
@endpush