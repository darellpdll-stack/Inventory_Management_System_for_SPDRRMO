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

                <form method="POST" action="{{ route('requests.store') }}" id="requestForm">
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
                        <div class="row g-2 mb-3 item-row">
                            <div class="col-12 col-sm-8 mb-1 mb-sm-0">
                                <input type="hidden" name="items[0][supply_item_id]" class="item-id">
                                <button type="button" class="btn btn-outline-secondary w-100 text-start pick-item text-truncate">
                                    Choose an item…
                                </button>
                            </div>
                            <div class="col-9 col-sm-3">
                                <input type="number" name="items[0][quantity]" inputmode="numeric"
                                       class="form-control" min="1" placeholder="Qty" required>
                            </div>
                            <div class="col-3 col-sm-1 px-sm-0">
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
        <div class="col-12 col-sm-8 mb-1 mb-sm-0">
            <input type="hidden" name="items[${i}][supply_item_id]" class="item-id">
            <button type="button" class="btn btn-outline-secondary w-100 text-start pick-item text-truncate">Choose an item…</button>
        </div>
        <div class="col-9 col-sm-3">
            <input type="number" name="items[${i}][quantity]" inputmode="numeric" class="form-control" min="1" placeholder="Qty" required>
        </div>
        <div class="col-3 col-sm-1 px-sm-0">
            <button type="button" class="btn btn-outline-danger w-100 remove-row">×</button>
        </div>`;

    document.getElementById('addRow').addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'row g-2 mb-3 item-row';
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
        picker.hide();
    });

    function applyFilter() {
        const term = search.value.toLowerCase();
        const cat = catFilter.value;
        let visible = 0;
        document.querySelectorAll('.picker-item').forEach(function (el) {
            const matchesText = el.dataset.label.toLowerCase().includes(term);
            const matchesCat = !cat || el.dataset.category === cat;
            const show = matchesText && matchesCat;
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