@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Your financial position at a glance.')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 gap-3 flex-wrap">
    <div>
        <div class="text-secondary small">Welcome back</div>
        <div class="h5 fw-bold mb-0">{{ auth()->user()->name }}</div>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#globalQuickAdd">
        <i class="bi bi-plus-lg me-2"></i>Add transaction
    </button>
</div>

<!-- Key Stat Cards -->
<div class="row g-3 mb-4" id="dashboardStats">
    @foreach(['Net worth','Income this month','Expenses this month','Financial Health'] as $label)
        <div class="col-6 col-xl-3">
            <div class="surface-card stat-card placeholder-glow">
                <div class="placeholder col-3 rounded"></div>
                <div class="stat-label">{{ $label }}</div>
                <div class="stat-value placeholder col-8 rounded">&nbsp;</div>
            </div>
        </div>
    @endforeach
</div>

<!-- Accounts & Committees Grid -->
<div class="row g-3 mb-4">
    <div class="col-xl-8">
        <section class="surface-card h-100">
            <div class="card-header-clean">
                <h2>Accounts</h2>
                <a href="{{ route('accounts') }}" class="btn btn-sm btn-soft-primary">Manage</a>
            </div>
            <div class="card-body-clean">
                <div class="row g-3" id="accountsGrid">
                    <div class="col-12 text-secondary small">Loading accounts…</div>
                </div>
            </div>
        </section>
    </div>
    <div class="col-xl-4">
        <section class="surface-card h-100">
            <div class="card-header-clean">
                <h2>Loans</h2>
                <a href="{{ route('loans') }}" class="btn btn-sm btn-soft-primary">View</a>
            </div>
            <div class="card-body-clean" id="loanSummary">
                <div class="text-secondary small">Loading…</div>
            </div>
        </section>
    </div>
</div>

<!-- Committees Widget & Budget Status -->
<div class="row g-3 mb-4">
    <div class="col-xl-6">
        <section class="surface-card h-100">
            <div class="card-header-clean">
                <h2>Committees (Bisi / ROSCA)</h2>
                <a href="{{ route('committees') }}" class="btn btn-sm btn-soft-primary">Manage</a>
            </div>
            <div class="card-body-clean" id="committeeSummary">
                <div class="text-secondary small">Loading committees…</div>
            </div>
        </section>
    </div>
    <div class="col-xl-6">
        <section class="surface-card h-100">
            <div class="card-header-clean">
                <h2>Budget status</h2>
                <a href="{{ route('budgets') }}" class="btn btn-sm btn-soft-primary">Manage</a>
            </div>
            <div class="card-body-clean" id="budgetSummary">
                <div class="text-secondary small">Loading…</div>
            </div>
        </section>
    </div>
</div>

<!-- Recent Transactions -->
<div class="row g-3">
    <div class="col-12">
        <section class="surface-card">
            <div class="card-header-clean">
                <h2>Recent transactions</h2>
                <a href="{{ route('transactions') }}" class="btn btn-sm btn-soft-primary">All activity</a>
            </div>
            <div class="card-body-clean" id="recentTransactions">
                <div class="text-secondary small">Loading…</div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
(async () => {
    const M = window.MyLedger;
    try {
        const data = await M.request('/ajax/dashboard');

        const health = data.financial_health || { score: 0, emergency_months: 0 };
        const healthBadge = health.score >= 70 ? 'text-success' : health.score >= 40 ? 'text-warning' : 'text-danger';

        const stats = [
            ['bi-wallet2', 'Net worth', data.net_worth.net_worth, 'Assets minus liabilities'],
            ['bi-arrow-down-left', 'Income this month', data.month.income, `${data.month.savings_rate}% savings rate`],
            ['bi-arrow-up-right', 'Expenses this month', data.month.expense, `${M.date(data.month.from)} – ${M.date(data.month.to)}`],
            ['bi-shield-check', 'Financial Health', `${health.score}%`, `${health.emergency_months} mo. emergency fund`],
        ];

        document.querySelector('#dashboardStats').innerHTML = stats.map(([icon, label, value, meta]) => `
            <div class="col-6 col-xl-3">
                <div class="surface-card stat-card">
                    <div class="stat-icon"><i class="bi ${icon}"></i></div>
                    <div class="stat-label">${M.esc(label)}</div>
                    <div class="stat-value ${label === 'Financial Health' ? healthBadge : ''}">${typeof value === 'number' ? M.money(value) : M.esc(value)}</div>
                    <div class="stat-meta">${M.esc(meta)}</div>
                </div>
            </div>
        `).join('');

        // Accounts Grid
        document.querySelector('#accountsGrid').innerHTML = data.accounts.length ? data.accounts.map(a => `
            <div class="col-md-6 col-xxl-4">
                <div class="account-tile">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="account-icon"><i class="bi bi-${a.type === 'bank' ? 'bank' : a.type === 'savings' ? 'piggy-bank' : a.type === 'investment' ? 'graph-up-arrow' : 'wallet2'}"></i></div>
                        <span class="badge-soft">${M.esc(a.type)}</span>
                    </div>
                    <div class="account-balance">${M.money(a.balance, a.currency.sybmol)}</div>
                    <div class="fw-semibold small mt-1">${M.esc(a.name)}</div>
                    <div class="account-subtitle">${M.esc(a.institution || (a.last_four ? `•••• ${a.last_four}` : 'Personal account'))}</div>
                </div>
            </div>
        `).join('') : '<div class="empty-state py-4"><i class="bi bi-bank"></i>No accounts yet.</div>';

        // Loans Summary
        const loans = data.loans;
        document.querySelector('#loanSummary').innerHTML = `
            <div class="row g-3">
                <div class="col-6">
                    <div class="p-3 rounded-4 bg-body-tertiary">
                        <div class="small text-secondary">You will receive</div>
                        <div class="h5 mb-0 mt-2 text-success">${M.money(loans.given_total, loans.currency.sybmol)}</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="p-3 rounded-4 bg-body-tertiary">
                        <div class="small text-secondary">You have to pay</div>
                        <div class="h5 mb-0 mt-2 text-danger">${M.money(loans.taken_total, loans.currency.sybmol)}</div>
                    </div>
                </div>
            </div>
            <div class="small text-secondary mt-3">${loans.items.filter(x => x.status === 'active').length} active loan(s)</div>
        `;

        // Committees Summary Widget
        const comms = data.upcoming_committees || [];
        document.querySelector('#committeeSummary').innerHTML = comms.length ? comms.slice(0, 3).map(c => `
            <div class="p-3 rounded-3 border mb-2 bg-body-tertiary">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <h4 class="h6 fw-bold mb-0 text-truncate">${M.esc(c.name)}</h4>
                    <span class="badge bg-primary">${M.money(c.total_pool_amount , c.currency.sybmol)} Pot</span>
                </div>
                <div class="d-flex justify-content-between small text-secondary">
                    <span>Next Due: <strong>${c.next_due_date ? M.date(c.next_due_date) : 'N/A'}</strong></span>
                    <span>Winner: <strong>${M.esc(c.next_winner)}</strong></span>
                </div>
                <div class="progress mt-2" style="height:4px;">
                    <div class="progress-bar bg-success" style="width:${c.progress_percent}%;"></div>
                </div>
            </div>
        `).join('') : `
            <div class="empty-state py-3">
                <i class="bi bi-diagram-3"></i>
                <div class="small">No active committees.</div>
                <a href="{{ route('committees') }}" class="btn btn-sm btn-outline-primary mt-2">Create Committee</a>
            </div>
        `;

        // Recent Transactions
        const icon = t => { const type=M.typeValue(t.type); return type==='income'?'arrow-down-left':type==='expense'?'arrow-up-right':type.includes('loan')?'cash-stack':'arrow-left-right'; };
        const cls = t => M.typeValue(t.type)==='income' ? 'money-positive' : M.typeValue(t.type)==='expense' ? 'money-negative' : '';
        document.querySelector('#recentTransactions').innerHTML = data.recent_transactions.length ? data.recent_transactions.map(t => `
            <div class="transaction-row">
                <div class="transaction-icon"><i class="bi bi-${icon(t)}"></i></div>
                <div>
                    <div class="transaction-title">${M.esc(t.description || t.category?.name || M.typeValue(t.type).replaceAll('_',' '))}</div>
                    <div class="transaction-meta">${M.date(t.transaction_date)} · ${M.esc(t.source_account?.name || t.destination_account?.name || t.person?.name || '')}</div>
                </div>
                <div class="text-end ${cls(t)}">${M.money(t.amount, t.currency.sybmol)}</div>
            </div>
        `).join('') : '<div class="empty-state py-4"><i class="bi bi-receipt"></i>No transactions yet.</div>';

        // Budget Summary
        document.querySelector('#budgetSummary').innerHTML = data.budgets.length ? data.budgets.slice(0,5).map(b => `
            <div class="mb-3">
                <div class="d-flex justify-content-between small mb-1">
                    <span class="fw-semibold">${M.esc(b.category)}</span>
                    <span>${Math.round(b.percent)}%</span>
                </div>
                <div class="progress-thin">
                    <div class="progress-bar ${b.over_budget?'bg-danger':''}" style="width:${Math.min(100,b.percent)}%;height:100%"></div>
                </div>
                <div class="d-flex justify-content-between small text-secondary mt-1">
                    <span>${M.money(b.spent, b.currency)} spent</span>
                    <span>${M.money(b.amount, b.currency)} budget</span>
                </div>
            </div>
        `).join('') : '<div class="empty-state py-4"><i class="bi bi-bullseye"></i>No active budgets.</div>';

    } catch (e) {
        M.toast(e.message, 'danger');
    }
})();
</script>
@endpush
