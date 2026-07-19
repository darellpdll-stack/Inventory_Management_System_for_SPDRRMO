<div class="topbar">
    <div>
        <div class="eyebrow">SPDRRMO Sorsogon</div>
        <div class="page-title">@yield('title', 'Dashboard')</div>
    </div>

    <div class="d-flex align-items-center">
        {{-- Notification bell (low stock + expiry) --}}
        @php $totalAlerts = $expiringItems->count() + $lowStockItems->count(); @endphp
        <div class="dropdown me-3">
            <button class="btn btn-sm btn-light position-relative" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell-fill"></i>
                @if($totalAlerts > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $totalAlerts }}
                    </span>
                @endif
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow" style="min-width: 340px; max-height: 440px; overflow-y: auto;">

                {{-- Low stock section --}}
                <li><h6 class="dropdown-header text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Low Stock</h6></li>
                @forelse($lowStockItems as $item)
                    <li>
                        <div class="notif-item d-flex align-items-start px-2 py-2">
                            <a class="flex-grow-1 text-decoration-none text-dark" href="{{ route('supplies.edit', $item) }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">{{ $item->description }}</span>
                                    <span class="badge bg-danger">{{ $item->balance_per_card }} left</span>
                                </div>
                                <div class="small text-muted">
                                    {{ $item->category->name }} · Min: {{ $item->minimum_stock }} {{ $item->unit }}
                                </div>
                                <div class="small text-primary mt-1">
                                    <i class="bi bi-box-arrow-in-right"></i> Click to view
                                </div>
                            </a>
                        </div>
                    </li>
                @empty
                    <li><span class="dropdown-item-text text-muted small">All items sufficiently stocked</span></li>
                @endforelse

                <li><hr class="dropdown-divider"></li>

                {{-- Expiry section --}}
                <li><h6 class="dropdown-header" style="color: var(--accent);"><i class="bi bi-clock-history"></i> Expiring / Expired</h6></li>
                @forelse($expiringItems as $item)
                    <li>
                        <div class="notif-item d-flex align-items-start px-2 py-2">
                            <a class="flex-grow-1 text-decoration-none text-dark" href="{{ route('supplies.edit', $item) }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">{{ $item->description }}</span>
                                    @if($item->expiration_date->isPast())
                                        <span class="badge bg-dark">Expired</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Soon</span>
                                    @endif
                                </div>
                                <div class="small text-muted">
                                    {{ $item->category->name }} · Exp: {{ $item->expiration_date->format('M d, Y') }}
                                </div>
                                <div class="small text-primary mt-1">
                                    <i class="bi bi-box-arrow-in-right"></i> Click to view
                                </div>
                            </a>
                        </div>
                    </li>
                @empty
                    <li><span class="dropdown-item-text text-muted small">No items expiring soon</span></li>
                @endforelse
            </ul>
        </div>

        <span class="user-name me-2">{{ Auth::user()->name }}</span>
        <span class="role-badge role-{{ Auth::user()->role }} me-3">{{ ucfirst(Auth::user()->role) }}</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-sm btn-outline-secondary" type="submit">Logout</button>
        </form>
    </div>
</div>