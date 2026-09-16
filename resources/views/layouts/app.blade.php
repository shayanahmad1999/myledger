@php
    $route = request()->route()?->getName();
    $settings = auth()->user()->settings;
@endphp
<!doctype html>
<html lang="en" data-theme-preference="{{ $settings?->theme ?? 'system' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MyLedger') · MyLedger</title>
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/myledger.css') }}" rel="stylesheet">
    @stack('styles')
    <script>
        (() => {
            const pref = document.documentElement.dataset.themePreference || 'system';
            const dark = pref === 'dark' || (pref === 'system' && matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light');
        })();
    </script>
</head>
<body class="app-body">
<div class="app-shell">
    <aside class="sidebar d-none d-lg-flex">
        <a href="{{ route('dashboard') }}" class="brand text-decoration-none">
            <span class="brand-mark"><i class="bi bi-wallet2"></i></span>
            <span><strong>MyLedger</strong><small>Personal Finance</small></span>
        </a>

        <nav class="sidebar-nav">
            <a class="nav-item {{ $route === 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a>
            <a class="nav-item {{ $route === 'transactions' ? 'active' : '' }}" href="{{ route('transactions') }}"><i class="bi bi-arrow-left-right"></i><span>Transactions</span></a>
            <a class="nav-item {{ $route === 'accounts' ? 'active' : '' }}" href="{{ route('accounts') }}"><i class="bi bi-bank"></i><span>Accounts</span></a>
            <div class="nav-label">Planning</div>
            <a class="nav-item {{ $route === 'loans' ? 'active' : '' }}" href="{{ route('loans') }}"><i class="bi bi-cash-stack"></i><span>Loans</span></a>
            <a class="nav-item {{ $route === 'savings' ? 'active' : '' }}" href="{{ route('savings') }}"><i class="bi bi-piggy-bank"></i><span>Savings</span></a>
            <a class="nav-item {{ $route === 'budgets' ? 'active' : '' }}" href="{{ route('budgets') }}"><i class="bi bi-bullseye"></i><span>Budgets</span></a>
            <a class="nav-item {{ $route === 'recurring' ? 'active' : '' }}" href="{{ route('recurring') }}"><i class="bi bi-arrow-repeat"></i><span>Recurring</span></a>
            <div class="nav-label">Insights</div>
            <a class="nav-item {{ $route === 'reports' ? 'active' : '' }}" href="{{ route('reports') }}"><i class="bi bi-bar-chart-line"></i><span>Reports</span></a>
            <a class="nav-item {{ $route === 'people' ? 'active' : '' }}" href="{{ route('people') }}"><i class="bi bi-people"></i><span>People</span></a>
            <a class="nav-item {{ $route === 'categories' ? 'active' : '' }}" href="{{ route('categories') }}"><i class="bi bi-tags"></i><span>Categories</span></a>
        </nav>

        <div class="sidebar-footer">
            <a class="nav-item {{ $route === 'notifications' ? 'active' : '' }}" href="{{ route('notifications') }}"><i class="bi bi-bell"></i><span>Notifications</span></a>
            <a class="nav-item {{ $route === 'settings' ? 'active' : '' }}" href="{{ route('settings') }}"><i class="bi bi-gear"></i><span>Settings</span></a>
        </div>
    </aside>

    <div class="app-main">
        <header class="topbar">
            <button class="btn btn-icon d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-label="Menu"><i class="bi bi-list"></i></button>
            <div class="topbar-copy">
                <h1>@yield('page-title', 'MyLedger')</h1>
                <p>@yield('page-subtitle', 'Manage your money with clarity.')</p>
            </div>
            <div class="ms-auto d-flex align-items-center gap-2">
                <a class="btn btn-icon position-relative" href="{{ route('notifications') }}" aria-label="Notifications"><i class="bi bi-bell"></i></a>
                <div class="dropdown">
                    <button class="btn profile-button dropdown-toggle" data-bs-toggle="dropdown">
                        <span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                        <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        <li><a class="dropdown-item" href="{{ route('settings') }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">@csrf<button class="dropdown-item text-danger" type="submit"><i class="bi bi-box-arrow-right me-2"></i>Sign out</button></form>
                        </li>
                    </ul>
                </div>
            </div>
        </header>

        <main class="content-area">
            @yield('content')
        </main>
    </div>
</div>

<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu">
    <div class="offcanvas-header border-bottom">
        <a href="{{ route('dashboard') }}" class="brand text-decoration-none"><span class="brand-mark"><i class="bi bi-wallet2"></i></span><span><strong>MyLedger</strong><small>Personal Finance</small></span></a>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-3">
        <nav class="sidebar-nav mobile-nav">
            <a class="nav-item" href="{{ route('dashboard') }}"><i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span></a>
            <a class="nav-item" href="{{ route('transactions') }}"><i class="bi bi-arrow-left-right"></i><span>Transactions</span></a>
            <a class="nav-item" href="{{ route('accounts') }}"><i class="bi bi-bank"></i><span>Accounts</span></a>
            <a class="nav-item" href="{{ route('loans') }}"><i class="bi bi-cash-stack"></i><span>Loans</span></a>
            <a class="nav-item" href="{{ route('savings') }}"><i class="bi bi-piggy-bank"></i><span>Savings</span></a>
            <a class="nav-item" href="{{ route('budgets') }}"><i class="bi bi-bullseye"></i><span>Budgets</span></a>
            <a class="nav-item" href="{{ route('recurring') }}"><i class="bi bi-arrow-repeat"></i><span>Recurring</span></a>
            <a class="nav-item" href="{{ route('reports') }}"><i class="bi bi-bar-chart-line"></i><span>Reports</span></a>
            <a class="nav-item" href="{{ route('people') }}"><i class="bi bi-people"></i><span>People</span></a>
            <a class="nav-item" href="{{ route('categories') }}"><i class="bi bi-tags"></i><span>Categories</span></a>
            <a class="nav-item" href="{{ route('settings') }}"><i class="bi bi-gear"></i><span>Settings</span></a>
        </nav>
    </div>
</div>

<nav class="mobile-bottom-nav d-lg-none">
    <a class="{{ $route === 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard') }}"><i class="bi bi-house"></i><span>Home</span></a>
    <a class="{{ $route === 'transactions' ? 'active' : '' }}" href="{{ route('transactions') }}"><i class="bi bi-arrow-left-right"></i><span>Activity</span></a>
    <button type="button" class="quick-add" data-bs-toggle="modal" data-bs-target="#globalQuickAdd"><i class="bi bi-plus-lg"></i></button>
    <a class="{{ $route === 'reports' ? 'active' : '' }}" href="{{ route('reports') }}"><i class="bi bi-graph-up"></i><span>Reports</span></a>
    <a class="{{ in_array($route, ['settings','accounts','loans','savings']) ? 'active' : '' }}" href="{{ route('settings') }}"><i class="bi bi-grid"></i><span>More</span></a>
</nav>

@include('partials.quick-add')
<div class="toast-container position-fixed top-0 end-0 p-3" id="toastContainer"></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/js/myledger.js') }}"></script>
@stack('scripts')
</body>
</html>
