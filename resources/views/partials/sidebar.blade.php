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
        <li class="nav-item">
        <a href="{{ route('withdrawals.index') }}" class="nav-link {{ request()->routeIs('withdrawals.*') ? 'active' : '' }}">
        <i class="bi bi-box-arrow-up"></i> Withdrawals
        </a>
        </li>
        
        </li>
            <a href="{{ route('personnel.index') }}" class="nav-link {{ request()->routeIs('personnel.*') ? 'active' : '' }}">
    <i class="bi bi-people"></i> Personnel
</a>
        @if(Auth::user()->role === 'admin')
        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
    <i class="bi bi-person-lock"></i> Manage Users
        </a>
        @endif
    </ul>

    <div class="sidebar-foot">
        <div class="small">Sorsogon Province · DRRM</div>
    </div>
</div>