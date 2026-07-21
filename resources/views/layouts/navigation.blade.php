@php
    $user = auth()->user();
    $role = $user?->roleValue() ?? 'operator';
    $currentLocale = session('locale', app()->getLocale());
@endphp

<aside id="erp-sidebar" class="erp-sidebar">
    <div class="erp-sidebar-header">
        <div class="erp-logo-box">P</div>

        <div>
            <div class="erp-brand-title">Production App</div>
            <div class="erp-brand-subtitle">Industrial Production ERP</div>
        </div>
        <button type="button" class="erp-sidebar-close" onclick="closeMobileSidebar()">&times;</button>
    </div>

    <div class="erp-menu">
        @if($user?->canViewDashboard())
            <div class="erp-menu-section">
                <div class="erp-menu-section-title">MAIN</div>

                <a href="{{ route('dashboard') }}"
                   class="erp-menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="erp-menu-icon">D</span>
                    <span class="erp-menu-text">Dashboard</span>
                </a>
            </div>
        @endif

        <div class="erp-menu-section">
            <div class="erp-menu-section-title">PRODUCTION</div>

            <a href="{{ route('production-entries.index') }}"
               class="erp-menu-link {{ request()->routeIs('production-entries.*') ? 'active' : '' }}">
                <span class="erp-menu-icon">P</span>
                <span class="erp-menu-text">Production Entries</span>
            </a>

            <a href="{{ route('production-plans.index') }}"
               class="erp-menu-link {{ request()->routeIs('production-plans.*') ? 'active' : '' }}">
                <span class="erp-menu-icon">PL</span>
                <span class="erp-menu-text">Production Planning</span>
            </a>

            @if($user?->canViewMachineStatus())
                <a href="{{ route('machine-status.index') }}"
                   class="erp-menu-link {{ request()->routeIs('machine-status.*') ? 'active' : '' }}">
                    <span class="erp-menu-icon">MS</span>
                    <span class="erp-menu-text">Machine Status</span>
                </a>
            @endif

            @if($user?->canViewLineKpiBoard())
                <a href="{{ route('line-status.index') }}"
                   class="erp-menu-link {{ request()->routeIs('line-status.*') ? 'active' : '' }}">
                    <span class="erp-menu-icon">LK</span>
                    <span class="erp-menu-text">Line KPI Board</span>
                </a>
            @endif
        </div>

        @if($user?->canViewMasterData())
            <div class="erp-menu-section">
                <div class="erp-menu-section-title">PLANT STRUCTURE</div>

                <a href="{{ route('zones.index') }}"
                   class="erp-menu-link {{ request()->routeIs('zones.*') ? 'active' : '' }}">
                    <span class="erp-menu-icon">Z</span>
                    <span class="erp-menu-text">Zones</span>
                </a>

                <a href="{{ route('production-lines.index') }}"
                   class="erp-menu-link {{ request()->routeIs('production-lines.*') ? 'active' : '' }}">
                    <span class="erp-menu-icon">L</span>
                    <span class="erp-menu-text">Production Lines</span>
                </a>
            </div>

            <div class="erp-menu-section">
                <div class="erp-menu-section-title">MASTER DATA</div>

                <a href="{{ route('machines.index') }}"
                   class="erp-menu-link {{ request()->routeIs('machines.*') ? 'active' : '' }}">
                    <span class="erp-menu-icon">M</span>
                    <span class="erp-menu-text">Machines</span>
                </a>

                <a href="{{ route('products.index') }}"
                   class="erp-menu-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <span class="erp-menu-icon">I</span>
                    <span class="erp-menu-text">Products</span>
                </a>

                <a href="{{ route('shifts.index') }}"
                   class="erp-menu-link {{ request()->routeIs('shifts.*') ? 'active' : '' }}">
                    <span class="erp-menu-icon">S</span>
                    <span class="erp-menu-text">Shifts</span>
                </a>

                <a href="{{ route('downtime-categories.index') }}"
                   class="erp-menu-link {{ request()->routeIs('downtime-categories.*') ? 'active' : '' }}">
                    <span class="erp-menu-icon">C</span>
                    <span class="erp-menu-text">Downtime Categories</span>
                </a>

                <a href="{{ route('downtime-reasons.index') }}"
                   class="erp-menu-link {{ request()->routeIs('downtime-reasons.*') ? 'active' : '' }}">
                    <span class="erp-menu-icon">R</span>
                    <span class="erp-menu-text">Downtime Reasons</span>
                </a>
            </div>

            <div class="erp-menu-section">
                <div class="erp-menu-section-title">INTEGRATION</div>

                <a href="{{ route('thingsboard-devices.index') }}"
                   class="erp-menu-link {{ request()->routeIs('thingsboard-devices.*') ? 'active' : '' }}">
                    <span class="erp-menu-icon">TB</span>
                    <span class="erp-menu-text">TB Devices</span>
                </a>
            </div>
        @endif

        @if($user?->canManageUsers())
            <div class="erp-menu-section">
                <div class="erp-menu-section-title">ADMINISTRATION</div>

                <a href="{{ route('users-management.index') }}"
                   class="erp-menu-link {{ request()->routeIs('users-management.*') ? 'active' : '' }}">
                    <span class="erp-menu-icon">U</span>
                    <span class="erp-menu-text">Users</span>
                </a>
            </div>
        @endif

        @if($user?->canViewAbsences())
            <div class="erp-menu-section">
                <div class="erp-menu-section-title">RESSOURCES HUMAINES</div>

                <a href="{{ route('absences.index') }}"
                   class="erp-menu-link {{ request()->routeIs('absences.*') ? 'active' : '' }}">
                    <span class="erp-menu-icon" style="background:#fef9c3;color:#ca8a04;">RH</span>
                    <span class="erp-menu-text">Absences</span>
                </a>
            </div>
        @endif

        <!-- Mobile User Settings (visible on <= 1024px) -->
        <div class="mobile-only-user-section">
            <div class="erp-menu-section-title">User Account</div>
            
            <div class="mobile-user-info">
                <div class="mobile-user-name">{{ $user?->name ?? 'User' }}</div>
                <div class="mobile-role-badge">Role: {{ str_replace('_', ' ', $role) }}</div>
            </div>

            <div class="mobile-lang-switch">
                <a href="{{ route('language.switch', 'en') }}" class="lang-btn {{ $currentLocale === 'en' ? 'active' : '' }}">EN</a>
                <a href="{{ route('language.switch', 'fr') }}" class="lang-btn {{ $currentLocale === 'fr' ? 'active' : '' }}">FR</a>
            </div>

            <a href="{{ route('profile.edit') }}" class="erp-menu-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <span class="erp-menu-icon">👤</span>
                <span class="erp-menu-text">Profile</span>
            </a>

            <form method="POST" action="{{ route('logout') }}" id="mobile-logout-form">
                @csrf
                <a href="#" onclick="event.preventDefault(); document.getElementById('mobile-logout-form').submit();" class="erp-menu-link erp-delete-link-menu">
                    <span class="erp-menu-icon" style="background:#fee2e2;color:#dc2626;">🚪</span>
                    <span class="erp-menu-text" style="color:#dc2626;">Logout</span>
                </a>
            </form>
        </div>
    </div>

    <div class="erp-sidebar-footer">
        <div class="erp-footer-title">Production ERP</div>
        <div class="erp-footer-subtitle">v1.0 — Manufacturing</div>
    </div>
</aside>

<header class="app-topbar">
    <div class="topbar-title">Production Management</div>

    <div class="topbar-actions">
        <div class="language-switch">
            <a href="{{ route('language.switch', 'en') }}"
               class="language-link {{ $currentLocale === 'en' ? 'active' : '' }}">
                EN
            </a>

            <a href="{{ route('language.switch', 'fr') }}"
               class="language-link {{ $currentLocale === 'fr' ? 'active' : '' }}">
                FR
            </a>
        </div>

        <div class="role-badge">
            Role: {{ str_replace('_', ' ', $role) }}
        </div>

        <div class="user-menu">
            <button type="button" class="user-menu-button" onclick="toggleUserMenu()">
                <span>{{ $user?->name ?? 'User' }}</span>
                <span class="user-menu-arrow">⌄</span>
            </button>

            <div id="userDropdown" class="user-dropdown">
                <a href="{{ route('profile.edit') }}" class="user-dropdown-link">
                    Profile
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit" class="user-dropdown-button">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>

<style>
    .app-topbar {
        height: 64px;
        position: fixed;
        top: 0;
        left: 260px;
        right: 0;
        z-index: 35;
        background: #ffffff;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 24px;
    }

    .topbar-title {
        font-size: 18px;
        font-weight: 900;
        color: #0f172a;
        white-space: nowrap;
    }

    .topbar-actions {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .language-switch {
        height: 36px;
        display: inline-flex;
        align-items: center;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        background: #ffffff;
    }

    .language-link {
        height: 36px;
        min-width: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 12px;
        font-size: 12px;
        font-weight: 900;
        color: #475569;
        text-decoration: none;
    }

    .language-link.active {
        background: #2563eb;
        color: #ffffff;
    }

    .role-badge {
        height: 36px;
        display: inline-flex;
        align-items: center;
        padding: 0 14px;
        border-radius: 999px;
        background: #eff6ff;
        color: #2563eb;
        font-size: 12px;
        font-weight: 900;
        white-space: nowrap;
        text-transform: capitalize;
    }

    .user-menu {
        position: relative;
    }

    .user-menu-button {
        height: 38px;
        min-width: 110px;
        display: inline-flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        padding: 0 14px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
        color: #0f172a;
        font-size: 13px;
        font-weight: 900;
        cursor: pointer;
    }

    .user-menu-arrow {
        font-size: 14px;
        color: #64748b;
    }

    .user-dropdown {
        display: none;
        position: absolute;
        top: 44px;
        right: 0;
        width: 170px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.15);
        padding: 8px;
        z-index: 60;
    }

    .user-dropdown.active {
        display: block;
    }

    .user-dropdown-link,
    .user-dropdown-button {
        width: 100%;
        display: block;
        padding: 9px 10px;
        border: 0;
        border-radius: 9px;
        background: transparent;
        color: #334155;
        font-size: 13px;
        font-weight: 800;
        text-align: left;
        text-decoration: none;
        cursor: pointer;
    }

    .user-dropdown-link:hover,
    .user-dropdown-button:hover {
        background: #f8fafc;
        color: #2563eb;
    }

    body {
        padding-top: 64px;
    }

    @media (max-width: 1280px) {
        .app-topbar {
            left: 230px;
        }
    }

    @media (max-width: 1024px) {
        body {
            padding-top: 0;
        }

        .app-topbar {
            display: none !important;
        }
    }
</style>

<script>
    function toggleUserMenu() {
        const dropdown = document.getElementById('userDropdown');

        if (dropdown) {
            dropdown.classList.toggle('active');
        }
    }

    document.addEventListener('click', function (event) {
        const menu = document.querySelector('.user-menu');
        const dropdown = document.getElementById('userDropdown');

        if (!menu || !dropdown) {
            return;
        }

        if (!menu.contains(event.target)) {
            dropdown.classList.remove('active');
        }
    });
</script>