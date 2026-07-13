<div class="sidebar">
    <a href="{{ route('dashboard') }}" class="brand-block">
        <div class="brand-mark">S</div>
        <div class="brand-text">
            <div class="name">SPDRRMO</div>
            <div class="sub">Supply Inventory</div>
        </div>
    </a>

    <ul class="nav flex-column">
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
        <a href="{{ route('personnel.index') }}" class="nav-link {{ request()->routeIs('personnel.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Personnel
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

    <div class="sidebar-foot">
        <div class="small">Sorsogon Province · DRRM</div>
    </div>
</div>