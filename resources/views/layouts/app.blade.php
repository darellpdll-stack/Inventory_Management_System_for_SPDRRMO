<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Inventory Management System for SPDRRMO')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar {
            width: 240px;
            min-height: 100vh;
            background-color: #0d6efd;
            position: fixed;
            top: 0;
            left: 0;
            padding-top: 1rem;
        }
        .sidebar .brand {
            color: #fff;
            font-weight: bold;
            font-size: 1.1rem;
            padding: 0.5rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.2);
            display: block;
            text-decoration: none;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.85);
            padding: 0.65rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            border-left: 3px solid transparent;
        }
        .sidebar .nav-link:hover {
            background-color: rgba(255,255,255,0.12);
            color: #fff;
        }
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.18);
            color: #fff;
            border-left-color: #fff;
            font-weight: 600;
        }
        .content-wrap { margin-left: 240px; }
        .topbar {
            background-color: #fff;
            border-bottom: 1px solid #dee2e6;
            padding: 0.5rem 1.5rem;
        }
        .notif-item { transition: background-color 0.15s ease; border-radius: 6px; }
        .notif-item:hover { background-color: #f1f5f9; }
        @media (max-width: 768px) {
            .sidebar { position: static; width: 100%; min-height: auto; }
            .content-wrap { margin-left: 0; }
        }
    </style>
</head>
<body>
    @auth
    {{-- Sidebar --}}
    <div class="sidebar">
        <a href="{{ route('dashboard') }}" class="brand">SPDRRMO Inventory</a>
        <ul class="nav flex-column mt-2">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('supplies.index') }}" class="nav-link {{ request()->routeIs('supplies.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i> Supplies
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('deployments.index') }}" class="nav-link {{ request()->routeIs('deployments.*') ? 'active' : '' }}">
                    <i class="bi bi-truck"></i> Deployments
                </a>
            </li>
            @if(Auth::user()->role === 'admin')
            <li class="nav-item">
                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Manage Users
                </a>
            </li>
            @endif
        </ul>
    </div>

    {{-- Content area --}}
    <div class="content-wrap">
        {{-- Top bar --}}
        <div class="topbar d-flex justify-content-end align-items-center">
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
                                        <span class="fw-semibold">{{ $item->item_name }}</span>
                                        <span class="badge bg-danger">{{ $item->current_stock }} left</span>
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
                        <li><span class="dropdown-item-text text-muted small">All items sufficiently stocked ✅</span></li>
                    @endforelse

                    <li><hr class="dropdown-divider"></li>

                    {{-- Expiry section --}}
                    <li><h6 class="dropdown-header text-warning"><i class="bi bi-clock-history"></i> Expiring / Expired</h6></li>
                    @forelse($expiringItems as $item)
                        <li>
                            <div class="notif-item d-flex align-items-start px-2 py-2">
                                <a class="flex-grow-1 text-decoration-none text-dark" href="{{ route('supplies.edit', $item) }}">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-semibold">{{ $item->item_name }}</span>
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
                                <form action="{{ route('supplies.dismiss', $item) }}" method="POST" class="ms-2">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-link text-muted p-0" title="Dismiss">✕</button>
                                </form>
                            </div>
                        </li>
                    @empty
                        <li><span class="dropdown-item-text text-muted small">No items expiring soon 🎉</span></li>
                    @endforelse
                </ul>
            </div>

            <span class="text-dark me-3">{{ Auth::user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-sm btn-outline-secondary" type="submit">Logout</button>
            </form>
        </div>

        {{-- Main content --}}
        <main class="container-fluid py-4 px-4">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
    @endauth

    {{-- Guest pages (login) have no sidebar --}}
    @guest
    <main class="container py-4">
        @yield('content')
    </main>
    @endguest

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>