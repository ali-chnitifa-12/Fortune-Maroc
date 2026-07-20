@php
    $user = auth()->user();
    $role = $user?->roleValue() ?? 'operator';
    $currentLocale = session('locale', app()->getLocale());
@endphp

<aside class="app-sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">P</div>

        <div class="brand-text">
            <div class="brand-title">Production App</div>
            <div class="brand-subtitle">Industrial Production ERP</div>
        </div>
    </div>

    <div class="sidebar-menu">
        @if($user?->canViewDashboard())
            <div class="menu-section">
                <div class="menu-section-title">MAIN</div>

                <a href="{{ route('dashboard') }}"
                   class="menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <span class="menu-icon">D</span>
                    <span class="menu-text">Dashboard</span>
                </a>
            </div>
        @endif

        <div class="menu-section">
            <div class="menu-section-title">PRODUCTION</div>

            <a href="{{ route('production-entries.index') }}"
               class="menu-link {{ request()->routeIs('production-entries.*') ? 'active' : '' }}">
                <span class="menu-icon">P</span>
                <span class="menu-text">Production Entries</span>
            </a>

            <a href="{{ route('production-plans.index') }}"
               class="menu-link {{ request()->routeIs('production-plans.*') ? 'active' : '' }}">
                <span class="menu-icon">PL</span>
                <span class="menu-text">Production Planning</span>
            </a>

            @if($user?->canViewMachineStatus())
                <a href="{{ route('machine-status.index') }}"
                   class="menu-link {{ request()->routeIs('machine-status.*') ? 'active' : '' }}">
                    <span class="menu-icon">MS</span>
                    <span class="menu-text">Machine Status</span>
                </a>
            @endif

            @if($user?->canViewLineKpiBoard())
                <a href="{{ route('line-status.index') }}"
                   class="menu-link {{ request()->routeIs('line-status.*') ? 'active' : '' }}">
                    <span class="menu-icon">LK</span>
                    <span class="menu-text">Line KPI Board</span>
                </a>
            @endif
        </div>

        @if($user?->canViewMasterData())
            <div class="menu-section">
                <div class="menu-section-title">PLANT STRUCTURE</div>

                <a href="{{ route('zones.index') }}"
                   class="menu-link {{ request()->routeIs('zones.*') ? 'active' : '' }}">
                    <span class="menu-icon">Z</span>
                    <span class="menu-text">Zones</span>
                </a>

                <a href="{{ route('production-lines.index') }}"
                   class="menu-link {{ request()->routeIs('production-lines.*') ? 'active' : '' }}">
                    <span class="menu-icon">L</span>
                    <span class="menu-text">Production Lines</span>
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-section-title">MASTER DATA</div>

                <a href="{{ route('machines.index') }}"
                   class="menu-link {{ request()->routeIs('machines.*') ? 'active' : '' }}">
                    <span class="menu-icon">M</span>
                    <span class="menu-text">Machines</span>
                </a>

                <a href="{{ route('products.index') }}"
                   class="menu-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                    <span class="menu-icon">I</span>
                    <span class="menu-text">Products</span>
                </a>

                <a href="{{ route('shifts.index') }}"
                   class="menu-link {{ request()->routeIs('shifts.*') ? 'active' : '' }}">
                    <span class="menu-icon">S</span>
                    <span class="menu-text">Shifts</span>
                </a>

                <a href="{{ route('downtime-categories.index') }}"
                   class="menu-link {{ request()->routeIs('downtime-categories.*') ? 'active' : '' }}">
                    <span class="menu-icon">C</span>
                    <span class="menu-text">Downtime Categories</span>
                </a>

                <a href="{{ route('downtime-reasons.index') }}"
                   class="menu-link {{ request()->routeIs('downtime-reasons.*') ? 'active' : '' }}">
                    <span class="menu-icon">R</span>
                    <span class="menu-text">Downtime Reasons</span>
                </a>
            </div>

            <div class="menu-section">
                <div class="menu-section-title">INTEGRATION</div>

                <a href="{{ route('thingsboard-devices.index') }}"
                   class="menu-link {{ request()->routeIs('thingsboard-devices.*') ? 'active' : '' }}">
                    <span class="menu-icon">TB</span>
                    <span class="menu-text">TB Devices</span>
                </a>
            </div>
        @endif

        @if($user?->canManageUsers())
            <div class="menu-section">
                <div class="menu-section-title">ADMINISTRATION</div>

                <a href="{{ route('users-management.index') }}"
                   class="menu-link {{ request()->routeIs('users-management.*') ? 'active' : '' }}">
                    <span class="menu-icon">U</span>
                    <span class="menu-text">Users</span>
                </a>
            </div>
        @endif

        @if($user?->canViewAbsences())
            <div class="menu-section">
                <div class="menu-section-title">RESSOURCES HUMAINES</div>

                <a href="{{ route('absences.index') }}"
                   class="menu-link {{ request()->routeIs('absences.*') ? 'active' : '' }}">
                    <span class="menu-icon" style="background:#fef9c3;color:#ca8a04;">RH</span>
                    <span class="menu-text">Absences</span>
                </a>
            </div>
        @endif
    </div>

    <div class="sidebar-footer">
        <div class="footer-title">Production ERP</div>
        <div class="footer-subtitle">v1.0 — Manufacturing</div>
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
    .app-sidebar {
        width: 260px;
        min-width: 260px;
        height: 100vh;
        position: fixed;
        left: 0;
        top: 0;
        z-index: 40;
        background: #ffffff;
        border-right: 1px solid #e5e7eb;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .sidebar-brand {
        min-height: 88px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        border-bottom: 1px solid #e5e7eb;
        background: #ffffff;
    }

    .brand-icon {
        width: 46px;
        height: 46px;
        min-width: 46px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 14px;
        background: #2563eb;
        color: #ffffff;
        font-size: 18px;
        font-weight: 900;
    }

    .brand-title {
        font-size: 16px;
        line-height: 1.15;
        font-weight: 900;
        color: #0f172a;
        white-space: nowrap;
    }

    .brand-subtitle {
        margin-top: 4px;
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        white-space: nowrap;
    }

    .sidebar-menu {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 16px 14px 18px;
    }

    .menu-section {
        margin-bottom: 22px;
    }

    .menu-section-title {
        margin: 0 0 8px 8px;
        font-size: 11px;
        font-weight: 900;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.06em;
    }

    .menu-link {
        width: 100%;
        min-height: 42px;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 7px 10px;
        margin-bottom: 6px;
        border-radius: 12px;
        color: #334155;
        font-size: 13px;
        font-weight: 900;
        text-decoration: none;
        line-height: 1.2;
        white-space: nowrap;
        overflow: hidden;
    }

    .menu-link:hover {
        background: #f8fafc;
        color: #2563eb;
    }

    .menu-link.active {
        background: #eff6ff;
        color: #2563eb;
    }

    .menu-icon {
        width: 28px;
        height: 28px;
        min-width: 28px;
        max-width: 28px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 9px;
        background: #f1f5f9;
        color: #475569;
        font-size: 10px;
        font-weight: 900;
        line-height: 1;
        text-align: center;
    }

    .menu-link.active .menu-icon {
        background: #dbeafe;
        color: #2563eb;
    }

    .menu-text {
        display: block;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .sidebar-footer {
        min-height: 64px;
        padding: 12px 18px;
        border-top: 1px solid #e5e7eb;
        background: #ffffff;
    }

    .footer-title {
        font-size: 12px;
        font-weight: 900;
        color: #0f172a;
    }

    .footer-subtitle {
        margin-top: 4px;
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
    }

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

    @media (max-width: 1024px) {
        .app-sidebar {
            width: 230px;
            min-width: 230px;
        }

        .app-topbar {
            left: 230px;
        }
    }

    @media (max-width: 768px) {
        body {
            padding-top: 0;
        }

        .app-sidebar {
            position: relative;
            width: 100%;
            min-width: 100%;
            height: auto;
            border-right: 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .sidebar-brand {
            min-height: auto;
            padding: 12px 14px;
        }

        .sidebar-menu {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            overflow-y: hidden;
            padding: 10px 14px;
        }

        .menu-section {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 0;
        }

        .menu-section-title {
            display: none;
        }

        .menu-link {
            width: auto;
            min-width: max-content;
            margin-bottom: 0;
        }

        .sidebar-footer {
            display: none;
        }

        .app-topbar {
            position: relative;
            left: 0;
            right: 0;
            height: auto;
            min-height: 58px;
            padding: 10px 14px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .topbar-actions {
            width: 100%;
            justify-content: flex-start;
            flex-wrap: wrap;
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