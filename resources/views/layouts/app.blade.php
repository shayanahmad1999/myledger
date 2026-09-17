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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css" id="flatpickrDarkTheme" disabled>
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
            <a class="nav-item {{ $route === 'committees' ? 'active' : '' }}" href="{{ route('committees') }}"><i class="bi bi-diagram-3-fill"></i><span>Committees</span></a>
            <a class="nav-item {{ $route === 'budgets' ? 'active' : '' }}" href="{{ route('budgets') }}"><i class="bi bi-bullseye"></i><span>Budgets</span></a>
            <a class="nav-item {{ $route === 'recurring' ? 'active' : '' }}" href="{{ route('recurring') }}"><i class="bi bi-arrow-repeat"></i><span>Recurring</span></a>
            <div class="nav-label">Insights</div>
            <a class="nav-item {{ $route === 'reports' ? 'active' : '' }}" href="{{ route('reports') }}"><i class="bi bi-bar-chart-line"></i><span>Reports</span></a>
            <a class="nav-item {{ $route === 'calendar' ? 'active' : '' }}" href="{{ route('calendar') }}"><i class="bi bi-calendar3"></i><span>Calendar</span></a>
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
                <button class="btn btn-outline-secondary d-none d-sm-inline-flex align-items-center gap-2" type="button" id="btnGlobalSearch" style="border-radius:12px; height:42px;">
                    <i class="bi bi-search"></i><span>Search...</span><kbd class="bg-body-tertiary text-secondary border px-1 rounded small">Ctrl K</kbd>
                </button>
                <button class="btn btn-icon d-sm-none" type="button" onclick="document.querySelector('#btnGlobalSearch').click()"><i class="bi bi-search"></i></button>
                <button class="btn btn-icon" id="btnThemeToggle" type="button" aria-label="Toggle Theme" title="Toggle Light/Dark Mode">
                    <i class="bi bi-moon-stars" id="themeIcon"></i>
                </button>
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
            <a class="nav-item" href="{{ route('committees') }}"><i class="bi bi-diagram-3-fill"></i><span>Committees</span></a>
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

<!-- Modal: Global Search -->
<div class="modal fade" id="globalSearchModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-top modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom p-3">
                <div class="input-group input-group-lg border-0">
                    <span class="input-group-text bg-transparent border-0"><i class="bi bi-search fs-5 text-secondary"></i></span>
                    <input class="form-control border-0 shadow-none ps-0" id="globalSearchInput" placeholder="Search transactions, accounts, committees, contacts, loans... (Esc to close)" autocomplete="off">
                </div>
                <button type="button" class="btn-close me-1" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" style="max-height: 420px; overflow-y: auto;">
                <div class="list-group list-group-flush" id="globalSearchResults">
                    <div class="p-4 text-center text-secondary small">Start typing to search across your ledger...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="{{ asset('assets/js/myledger.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.MyLedger && window.MyLedger.initDatePickers) {
        window.MyLedger.initDatePickers();
    }

    // Theme Toggle
    const btnTheme = document.querySelector('#btnThemeToggle');
    if (btnTheme) {
        const icon = document.querySelector('#themeIcon');
        const updateIcon = (isDark) => {
            if (icon) icon.className = isDark ? 'bi bi-sun-fill text-warning' : 'bi bi-moon-stars';
            const darkThemeLink = document.querySelector('#flatpickrDarkTheme');
            if (darkThemeLink) darkThemeLink.disabled = !isDark;
        };

        let isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        updateIcon(isDark);

        btnTheme.onclick = async () => {
            isDark = !isDark;
            const newTheme = isDark ? 'dark' : 'light';
            document.documentElement.setAttribute('data-bs-theme', newTheme);
            updateIcon(isDark);
            try {
                await window.MyLedger.request('/ajax/settings', {
                    method: 'PATCH',
                    body: JSON.stringify({ theme: newTheme })
                });
            } catch(e) {}
        };
    }

    // Global Search
    const searchBtn = document.querySelector('#btnGlobalSearch');
    const searchInput = document.querySelector('#globalSearchInput');
    const searchResults = document.querySelector('#globalSearchResults');

    if (searchBtn && searchInput) {
        searchBtn.onclick = () => {
            window.MyLedger.modal('globalSearchModal');
            setTimeout(() => searchInput.focus(), 150);
        };

        document.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                searchBtn.click();
            }
        });

        let searchDebounce;
        searchInput.oninput = () => {
            clearTimeout(searchDebounce);
            const q = searchInput.value.trim();
            if (q.length < 2) {
                searchResults.innerHTML = '<div class="p-4 text-center text-secondary small">Start typing to search across your ledger...</div>';
                return;
            }

            searchResults.innerHTML = '<div class="p-4 text-center text-secondary small"><span class="spinner-border spinner-border-sm me-2"></span>Searching...</div>';

            searchDebounce = setTimeout(async () => {
                try {
                    const res = await window.MyLedger.request('/ajax/search?q=' + encodeURIComponent(q));
                    const items = res.results || [];
                    if (!items.length) {
                        searchResults.innerHTML = `<div class="p-4 text-center text-secondary small">No results found for "${window.MyLedger.esc(q)}".</div>`;
                        return;
                    }
                    searchResults.innerHTML = items.map(item => `
                        <a href="${item.url}" class="list-group-item list-group-item-action d-flex align-items-center gap-3 p-3 border-0 border-bottom">
                            <div class="rounded-circle p-2 bg-body-tertiary text-primary d-flex align-items-center justify-content-center" style="width:38px;height:38px;">
                                <i class="bi ${item.icon} fs-6"></i>
                            </div>
                            <div class="flex-fill min-w-0">
                                <div class="fw-bold text-truncate">${window.MyLedger.esc(item.title)}</div>
                                <div class="small text-secondary text-truncate">${window.MyLedger.esc(item.subtitle)}</div>
                            </div>
                            <span class="badge bg-secondary-subtle text-secondary small">${window.MyLedger.esc(item.type)}</span>
                        </a>
                    `).join('');
                } catch (e) {
                    searchResults.innerHTML = '<div class="p-4 text-center text-danger small">Search failed. Please try again.</div>';
                }
            }, 250);
        };
    }
});
</script>
@stack('scripts')
</body>
</html>
