@extends('layouts.app')
@section('title', 'New Borrowing')

@section('content')
<div class="page-head">
    <div>
        <div class="page-title">New Borrowing</div>
        <div class="page-sub">Reserve equipment for a date and purpose.</div>
    </div>
    <a href="{{ route('borrowings.index') }}" class="btn btn-light"><i class="bi bi-arrow-left"></i> Back</a>
</div>

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if($errors->any())
    <div class="alert alert-danger">
        <div class="fw-semibold mb-1">Please check the form:</div>
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('borrowings.store') }}">
    @csrf

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="dpanel">
                <div class="dpanel-title mb-3">Borrower</div>

                <div class="mb-3">
                    <label class="form-label">Personnel</label>
                    <select name="personnel_id" class="form-select" required>
                        <option value="">Select personnel…</option>
                        @foreach($personnel as $person)
                            <option value="{{ $person->id }}" {{ old('personnel_id') == $person->id ? 'selected' : '' }}>
                                {{ $person->name }}@if($person->position) — {{ $person->position }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Date needed</label>
                    <input type="date" name="date_needed" id="dateNeeded" class="form-control"
                           value="{{ old('date_needed', now()->toDateString()) }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Expected return <span class="cell-sub">(optional)</span></label>
                    <input type="date" name="date_due" id="dateDue" class="form-control"
                           value="{{ old('date_due') }}"
                           min="{{ old('date_needed', now()->toDateString()) }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Purpose</label>
                    <input type="text" name="purpose" class="form-control" value="{{ old('purpose') }}"
                           placeholder="e.g. Flood response operation, Barangay Bato">
                </div>

                <div class="mb-0">
                    <label class="form-label">Remarks <span class="cell-sub">(optional)</span></label>
                    <input type="text" name="remarks" class="form-control" value="{{ old('remarks') }}">
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="dpanel">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <div class="dpanel-title">Equipment</div>
                        <div class="cell-sub">Tick the exact units being handed over.</div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap justify-content-end">
                        <div class="search-box" style="max-width: 240px;">
                            <i class="bi bi-search"></i>
                            <input type="text" id="itemFilter" class="form-control" placeholder="Search equipment…">
                        </div>
                        <select id="categoryFilter" class="form-select" style="width:auto;">
                            <option value="">All Categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @forelse($items as $item)
                    @php
                        $available = $item->availableNumbers();
                        $hasNumbers = $item->property_no && count($available) && $available[0] !== '—';
                    @endphp
                    <div class="timeline-item borrow-item"
                         data-name="{{ Str::lower($item->description . ' ' . ($item->property_no ?? '') . ' ' . ($item->category->name ?? '')) }}"
                         data-category="{{ $item->category_id }}">
                        <div class="d-flex justify-content-between align-items-center gap-2">
                            <div>
                                <div class="cell-main">
                                    {{ $item->description }}
                                    <span class="req-badge req-approved ms-1 d-none" data-count-for="{{ $item->id }}"></span>
                                </div>
                                <div class="cell-sub">
                                    {{ $item->category->name ?? '—' }} ·
                                    {{ $item->availableQuantity() }} of {{ $item->quantity }} available
                                </div>
                            </div>

                            @if($hasNumbers)
                                <button type="button" class="btn btn-sm btn-outline-primary text-nowrap"
                                        data-bs-toggle="collapse" data-bs-target="#units{{ $item->id }}">
                                    Select units
                                </button>
                            @endif
                        </div>

                        @if($hasNumbers)
                            <div class="collapse" id="units{{ $item->id }}">
                                <div class="d-flex align-items-center gap-2 mt-3 flex-wrap">
                                    <label class="form-label mb-0 small">Pick the first</label>
                                    <input type="number" class="form-control form-control-sm quick-pick"
                                           style="width: 80px;" min="1" max="{{ count($available) }}"
                                           data-item="{{ $item->id }}" placeholder="e.g. 5">
                                    <button type="button" class="btn btn-sm btn-light quick-pick-go"
                                            data-item="{{ $item->id }}">Tick them</button>
                                    <button type="button" class="btn btn-sm btn-light quick-clear"
                                            data-item="{{ $item->id }}">Clear</button>
                                </div>

                                <div class="d-flex flex-wrap gap-2 mt-2 p-2 rounded"
                                     style="max-height: 190px; overflow-y: auto; border: 1px solid var(--line);">
                                    @foreach($available as $number)
                                        <div class="form-check form-check-inline me-0">
                                            <input class="form-check-input unit-box" type="checkbox"
                                                   name="picked[]" value="{{ $item->id }}|{{ $number }}"
                                                   data-item="{{ $item->id }}"
                                                   id="pick{{ $item->id }}_{{ $loop->index }}">
                                            <label class="form-check-label small" for="pick{{ $item->id }}_{{ $loop->index }}">
                                                {{ $number }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="d-flex align-items-center gap-2 mt-2">
                                <label class="form-label mb-0 small">Quantity to borrow</label>
                                <input type="number" name="counts[{{ $item->id }}]"
                                       class="form-control form-control-sm" style="width: 90px;"
                                       value="0" min="0" max="{{ $item->availableQuantity() }}">
                                <span class="cell-sub">{{ $item->unit }}</span>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="dash-empty">
                        No semi-expendable equipment is available to borrow right now.
                    </div>
                @endforelse

                @if($items->count())
                    <div id="noMatches" class="dash-empty d-none">No equipment matches that search.</div>
                @endif
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('borrowings.index') }}" class="btn btn-light">Cancel</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check2-circle"></i> Record Borrowing</button>
            </div>
        </div>
    </div>
</form>

<script>
(function () {
    // everything is delegated off the document, so it works no matter when
    // this script runs relative to the markup it controls
    function refreshCount(itemId) {
        var boxes = document.querySelectorAll('.unit-box[data-item="' + itemId + '"]');
        var picked = 0;
        boxes.forEach(function (box) { if (box.checked) picked++; });

        var badge = document.querySelector('[data-count-for="' + itemId + '"]');
        if (!badge) return;
        badge.textContent = picked + ' selected';
        badge.classList.toggle('d-none', picked === 0);
    }

    function applyFilter() {
        var search = document.getElementById('itemFilter');
        var category = document.getElementById('categoryFilter');
        var empty = document.getElementById('noMatches');

        var term = (search && search.value ? search.value : '').trim().toLowerCase();
        var cat = (category && category.value) ? category.value : '';
        var shown = 0;

        document.querySelectorAll('.borrow-item').forEach(function (row) {
            var matchesTerm = term === '' || (row.dataset.name || '').indexOf(term) !== -1;
            var matchesCat = cat === '' || row.dataset.category === cat;
            var visible = matchesTerm && matchesCat;

            row.classList.toggle('d-none', !visible);
            if (visible) shown++;
        });

        if (empty) empty.classList.toggle('d-none', shown > 0);
    }

    document.addEventListener('click', function (e) {
        var go = e.target.closest('.quick-pick-go');
        if (go) {
            var itemId = go.dataset.item;
            var field = document.querySelector('.quick-pick[data-item="' + itemId + '"]');
            var want = parseInt((field && field.value) ? field.value : '0', 10);
            if (!want || want < 1) return;

            document.querySelectorAll('.unit-box[data-item="' + itemId + '"]')
                .forEach(function (box, i) { box.checked = i < want; });
            refreshCount(itemId);
            return;
        }

        var clear = e.target.closest('.quick-clear');
        if (clear) {
            var clearId = clear.dataset.item;
            document.querySelectorAll('.unit-box[data-item="' + clearId + '"]')
                .forEach(function (box) { box.checked = false; });

            var clearField = document.querySelector('.quick-pick[data-item="' + clearId + '"]');
            if (clearField) clearField.value = '';
            refreshCount(clearId);
        }
    });

    document.addEventListener('change', function (e) {
        if (e.target.classList && e.target.classList.contains('unit-box')) {
            refreshCount(e.target.dataset.item);
        }
        if (e.target.id === 'categoryFilter') applyFilter();
    });

    document.addEventListener('input', function (e) {
        if (e.target.id === 'itemFilter') applyFilter();
    });

    // equipment cannot come back before it goes out, so the return date
    // follows whatever the date needed is set to
    document.addEventListener('change', function (e) {
        if (e.target.id !== 'dateNeeded') return;

        var due = document.getElementById('dateDue');
        if (!due) return;

        due.min = e.target.value;
        if (due.value && due.value < e.target.value) {
            due.value = e.target.value;
        }
    });
})();
</script>
@endsection
