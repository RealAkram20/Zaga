<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin — @yield('title', 'Dashboard') | Zaga Technologies</title>
    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/zz.png') }}">
    <style>
        .admin-wrapper { display: flex; min-height: 100vh; }
        .admin-sidebar {
            width: 240px; min-width: 240px; background: var(--dark-text, #1e293b);
            color: #fff; padding: 0; display: flex; flex-direction: column;
        }
        .admin-sidebar .brand {
            padding: 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .admin-sidebar .brand img { height: 40px; }
        .admin-sidebar .brand span {
            display: block; font-size: 11px; color: #94a3b8; margin-top: 4px;
        }
        .admin-nav { padding: 16px 0; flex: 1; }
        .admin-nav a {
            display: block; padding: 10px 24px; color: #cbd5e1; text-decoration: none;
            font-size: 14px; transition: all 0.2s;
        }
        .admin-nav a:hover, .admin-nav a.active {
            background: rgba(255,255,255,0.08); color: #fff; border-left: 3px solid #2563eb;
            padding-left: 21px;
        }
        .admin-nav .nav-section {
            padding: 16px 24px 6px; font-size: 11px; text-transform: uppercase;
            color: #64748b; letter-spacing: 0.05em;
        }
        .admin-main { flex: 1; display: flex; flex-direction: column; background: #f8fafc; }
        .admin-topbar {
            background: #fff; border-bottom: 1px solid #e2e8f0; padding: 12px 24px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .admin-topbar h1 { font-size: 18px; font-weight: 600; color: #1e293b; margin: 0; }
        .admin-topbar .topbar-right { display: flex; align-items: center; gap: 16px; }
        .admin-content { flex: 1; padding: 24px; }
        .admin-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;
            padding: 24px; margin-bottom: 24px;
        }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 8px;
            padding: 20px; text-align: center;
        }
        .stat-card .stat-value { font-size: 28px; font-weight: 700; color: #2563eb; }
        .stat-card .stat-label { font-size: 13px; color: #64748b; margin-top: 4px; }
        .admin-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .admin-table th {
            background: #f1f5f9; padding: 10px 12px; text-align: left;
            font-weight: 600; color: #374151; border-bottom: 1px solid #e2e8f0;
        }
        .admin-table td { padding: 10px 12px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
        .admin-table tr:hover td { background: #f8fafc; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: 12px; font-weight: 500; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef9c3; color: #854d0e; }
        .badge-danger  { background: #fee2e2; color: #991b1b; }
        .badge-info    { background: #dbeafe; color: #1e40af; }
        .badge-secondary { background: #f1f5f9; color: #475569; }
        .btn-sm { padding: 4px 10px; font-size: 13px; border-radius: 4px; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary-sm { background: #2563eb; color: #fff; }
        .btn-danger-sm  { background: #dc3545; color: #fff; }
        .btn-warning-sm { background: #ffc107; color: #1e293b; }
        .search-filter-bar { display: flex; gap: 12px; margin-bottom: 16px; flex-wrap: wrap; }
        .search-filter-bar input, .search-filter-bar select {
            padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 6px; font-size: 14px;
        }
        /* ── Mobile admin toggle ── */
        .admin-hamburger {
            display: none;
            align-items: center;
            justify-content: center;
            width: 40px; height: 40px;
            background: none;
            border: 1.5px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            flex-shrink: 0;
            padding: 0;
        }
        .admin-hamburger span {
            display: block; width: 20px; height: 2px;
            background: #1e293b; border-radius: 2px;
            position: relative;
            transition: background .2s;
        }
        .admin-hamburger span::before,
        .admin-hamburger span::after {
            content: ''; position: absolute; left: 0;
            width: 100%; height: 2px; background: #1e293b; border-radius: 2px;
            transition: transform .25s;
        }
        .admin-hamburger span::before { top: -6px; }
        .admin-hamburger span::after  { top: 6px; }
        .admin-hamburger.is-open span { background: transparent; }
        .admin-hamburger.is-open span::before { transform: translateY(6px) rotate(45deg); }
        .admin-hamburger.is-open span::after  { transform: translateY(-6px) rotate(-45deg); }

        .admin-sidebar-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(15,23,42,.45); z-index: 1050;
        }
        .admin-sidebar-overlay.is-open { display: block; }

        @media (max-width: 768px) {
            .admin-hamburger { display: flex; }
            .admin-sidebar {
                position: fixed; top: 0; left: 0; bottom: 0;
                z-index: 1060;
                transform: translateX(-100%);
                transition: transform .3s cubic-bezier(.4,0,.2,1);
                box-shadow: 4px 0 24px rgba(0,0,0,.12);
            }
            .admin-sidebar.is-open { transform: translateX(0); }
            .admin-topbar { padding: 12px 16px; }
            .admin-content { padding: 16px; }
        }
    </style>
</head>
<body>
<div class="admin-wrapper">

    <!-- Sidebar overlay (mobile) -->
    <div class="admin-sidebar-overlay" id="adminOverlay"></div>

    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="brand">
            <img src="{{ asset('images/logo.png') }}" alt="Zaga Technologies">
            <span>Admin Panel</span>
        </div>
        <nav class="admin-nav">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                📊 Dashboard
            </a>

            <div class="nav-section">Catalog</div>
            <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                📦 Products
            </a>

            <div class="nav-section">Customers</div>
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                👥 Customers
            </a>

            <div class="nav-section">Sales</div>
            <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                🛒 Orders
            </a>
            <a href="{{ route('admin.payments.index') }}" class="{{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                💳 Payments
            </a>
            <a href="{{ route('admin.reports') }}" class="{{ request()->routeIs('admin.reports') ? 'active' : '' }}">
                📈 Reports
            </a>

            <div class="nav-section">Site</div>
            <a href="{{ route('home') }}" target="_blank">🌐 View Site</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" style="all:unset;display:block;padding:10px 24px;color:#cbd5e1;font-size:14px;cursor:pointer;width:100%;box-sizing:border-box;">
                    🚪 Logout
                </button>
            </form>
        </nav>
    </aside>

    <!-- Main Content -->
    <div class="admin-main">
        <div class="admin-topbar">
            <button class="admin-hamburger" id="adminHamburger" aria-label="Open admin menu" aria-expanded="false">
                <span></span>
            </button>
            <h1>@yield('title', 'Dashboard')</h1>
            <div class="topbar-right">
                <span style="font-size:14px;color:#64748b;">Welcome, {{ auth()->user()->name }}</span>
            </div>
        </div>

        @if(session('success'))
            <div style="background:#dcfce7;color:#166534;padding:12px 24px;border-bottom:1px solid #bbf7d0;">
                ✓ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div style="background:#fee2e2;color:#991b1b;padding:12px 24px;border-bottom:1px solid #fecaca;">
                ✗ {{ session('error') }}
            </div>
        @endif

        <div class="admin-content">
            @yield('content')
        </div>
    </div>
</div>
@stack('scripts')
<script>
(function () {
    const btn     = document.getElementById('adminHamburger');
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('adminOverlay');
    if (!btn || !sidebar) return;
    function toggle(open) {
        const isOpen = typeof open === 'boolean' ? open : !sidebar.classList.contains('is-open');
        sidebar.classList.toggle('is-open', isOpen);
        overlay.classList.toggle('is-open', isOpen);
        btn.classList.toggle('is-open', isOpen);
        btn.setAttribute('aria-expanded', String(isOpen));
        document.body.style.overflow = isOpen ? 'hidden' : '';
    }
    btn.addEventListener('click', () => toggle());
    overlay.addEventListener('click', () => toggle(false));
    document.addEventListener('keydown', e => { if (e.key === 'Escape') toggle(false); });
})();
</script>
</body>
</html>
