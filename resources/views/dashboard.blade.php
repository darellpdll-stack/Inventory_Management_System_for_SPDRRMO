@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')

<div class="dash-header">
    <div>
        <div class="dash-greeting">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }}, {{ Auth::user()->name }}</div>
        <div class="dash-date">{{ now()->format('l, F j, Y') }}</div>
    </div>
    <div class="dash-office">
        <div class="o-name">SPDRRMO Inventory</div>
        <div class="o-sub">Sorsogon Province</div>
    </div>
</div>

{{-- Summary --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="stat-card">
            <div class="stat-label">Items tracked</div>
            <div class="stat-value">{{ $totalItems }}</div>
            <div class="stat-sub">across {{ $categoryLabels->count() }} categories</div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('supplies.index') }}" class="stat-card feature">
            <div class="stat-label">Needs restocking</div>
            <div class="stat-value">{{ $lowStockCount }}</div>
            <div class="stat-sub">{{ $lowStockCount === 0 ? 'All items above minimum' : 'Tap to review low items' }}</div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('supplies.index') }}" class="stat-card">
            <div class="stat-label">Expiring soon</div>
            <div class="stat-value warn">{{ $expiringCount }}</div>
            <div class="stat-sub">{{ $expiringCount === 0 ? 'Clear for 3 months' : 'Within 3 months' }}</div>
        </a>
    </div>
    <div class="col-md-3 col-sm-6">
        <a href="{{ route('withdrawals.index') }}" class="stat-card">
            <div class="stat-label">Withdrawals</div>
            <div class="stat-value">{{ $totalWithdrawals }}</div>
            <div class="stat-sub">recorded to date</div>
        </a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-7">
        <div class="panel">
            <div class="panel-title">Inventory by category</div>
            <canvas id="categoryChart" height="150"></canvas>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel">
            <div class="panel-title">Stock status</div>
            <canvas id="statusChart" height="190"></canvas>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="panel">
            <div class="panel-title">Withdrawal activity <span class="hint">last 6 months</span></div>
            <canvas id="trendChart" height="150"></canvas>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="panel">
            <div class="panel-title">Most withdrawn</div>
            @forelse($topItems as $index => $t)
                <div class="rank-row">
                    <span class="rank-num">{{ $index + 1 }}</span>
                    <span class="rank-name">{{ $t->supplyItem->description ?? '—' }}</span>
                    <span class="rank-qty">{{ $t->total }} {{ $t->supplyItem->unit ?? '' }}</span>
                </div>
            @empty
                <div class="empty-state">
                    <i class="bi bi-inbox es-icon"></i>
                    <div class="es-text">No withdrawals yet — record one to see what gets used most.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<div id="chartData"
     data-categories='@json($categoryLabels)'
     data-category-counts='@json($categoryCounts)'
     data-status='@json([$ok, $low, $outOfStock])'
     data-trend-labels='@json($trend->pluck("label"))'
     data-trend-counts='@json($trend->pluck("count"))'
     hidden></div>
@endsection

@push('scripts')
<script>
(function () {
    const d = document.getElementById('chartData');
    const teal = '#0f2b31', amber = '#e8930c', red = '#c62b38', slate = '#7d9aa1';

    Chart.defaults.font.family = 'Archivo, system-ui, sans-serif';
    Chart.defaults.color = '#64777c';

    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: { labels: JSON.parse(d.dataset.categories),
            datasets: [{ data: JSON.parse(d.dataset.categoryCounts), backgroundColor: teal, borderRadius: 4, barThickness: 28 }] },
        options: { plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef1ee' }, border: { display: false } },
                      x: { grid: { display: false } } } }
    });

    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: { labels: ['Sufficient', 'Low', 'Out of stock'],
            datasets: [{ data: JSON.parse(d.dataset.status), backgroundColor: [slate, amber, red], borderWidth: 0 }] },
        options: { cutout: '64%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, padding: 14 } } } }
    });

    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: { labels: JSON.parse(d.dataset.trendLabels),
            datasets: [{ data: JSON.parse(d.dataset.trendCounts), borderColor: amber,
                backgroundColor: 'rgba(232,147,12,0.10)', fill: true, tension: 0.35,
                pointBackgroundColor: amber, pointRadius: 3 }] },
        options: { plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#eef1ee' }, border: { display: false } },
                      x: { grid: { display: false } } } }
    });
})();
</script>
@endpush