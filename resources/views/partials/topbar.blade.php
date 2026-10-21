<div class="topbar">
    <div class="d-flex align-items-center gap-2" style="min-width: 0;">
        {{-- opens the sidebar drawer; only there when the sidebar is hidden --}}
        <button type="button" id="navToggle" class="btn btn-sm btn-light d-lg-none"
                aria-label="Open menu" aria-expanded="false">
            <i class="bi bi-list"></i>
        </button>
        <div style="min-width: 0;">
            <div class="eyebrow">SPDRRMO Sorsogon</div>
            <div class="page-title text-truncate">@yield('title', 'Dashboard')</div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 gap-lg-3">
        {{-- Light / dark toggle --}}
        <label class="theme-toggle" title="Toggle light and dark mode">
            <input type="checkbox" id="themeToggle">
            <span class="tt-track">
                <span class="tt-knob">
                    <i class="bi bi-sun-fill tt-sun"></i>
                    <i class="bi bi-moon-stars-fill tt-moon"></i>
                </span>
            </span>
        </label>

        {{-- Notification bell (low stock + expiry) --}}
        @php $totalAlerts = $expiringItems->count() + $lowStockItems->count(); @endphp
        <div class="dropdown">
            <button class="btn btn-sm btn-light position-relative" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell-fill"></i>
                @if($totalAlerts > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $totalAlerts }}
                    </span>
                @endif
            </button>
            {{-- 340px overflows a phone, so it gives way to the screen --}}
            <ul class="dropdown-menu dropdown-menu-end shadow"
                style="min-width: min(340px, calc(100vw - 1.8rem)); max-width: calc(100vw - 1.8rem); max-height: 440px; overflow-y: auto;">

                {{-- Low stock section --}}
                <li><h6 class="dropdown-header text-danger"><i class="bi bi-exclamation-triangle-fill"></i> Low Stock</h6></li>
                @forelse($lowStockItems as $item)
                    <li>
                        <div class="notif-item d-flex align-items-start px-2 py-2">
                            <a class="flex-grow-1 text-decoration-none text-body" href="{{ route('supplies.edit', $item) }}">
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
                            <a class="flex-grow-1 text-decoration-none text-body" href="{{ route('supplies.edit', $item) }}">
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

        {{-- the name is in the drawer too, so a narrow screen can do without it --}}
        <div class="d-none d-md-flex align-items-center gap-2">
            <span class="user-name">{{ Auth::user()->name }}</span>
            <span class="role-badge role-{{ Auth::user()->role }}">{{ ucfirst(Auth::user()->role) }}</span>
        </div>

        <form method="POST" action="{{ route('logout') }}" class="m-0">
            @csrf
            <button class="btn btn-sm btn-outline-secondary" type="submit" title="Log out">
                <i class="bi bi-box-arrow-right d-md-none"></i>
                <span class="d-none d-md-inline">Logout</span>
            </button>
        </form>
    </div>
</div>