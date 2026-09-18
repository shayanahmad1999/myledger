@extends('layouts.app')
@section('title', 'Reports')
@section('page-title', 'Reports & insights')
@section('page-subtitle', 'Daily, weekly, monthly, yearly and custom financial analysis.')

@section('content')
    <!-- Smart Insights Container -->
    <div class="row g-3 mb-4" id="smartInsightsContainer"></div>

    <div class="surface-card p-3 mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Period</label>
                <select class="form-select" id="periodPreset">
                    <option value="today">Today</option>
                    <option value="week">This week</option>
                    <option value="month" selected>This month</option>
                    <option value="year">This year</option>
                    <option value="custom">Custom</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">From</label>
                <input class="form-control" id="reportFrom" type="date">
            </div>
            <div class="col-md-3">
                <label class="form-label">To</label>
                <input class="form-control" id="reportTo" type="date">
            </div>
            <div class="col-md-2">
                <label class="form-label">Currency</label>
                <select class="form-select" id="reportCurrency"></select>
            </div>
            <div class="col-md-1">
                <button class="btn btn-primary w-100" id="runReport">
                    <i class="bi bi-arrow-clockwise me-2"></i>Update
                </button>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="surface-card stat-card">
                <div class="stat-label">Income</div>
                <div class="stat-value text-success" id="reportIncome">{{ auth()->user()->settings->currency->symbol }} 0
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="surface-card stat-card">
                <div class="stat-label">Expenses</div>
                <div class="stat-value text-danger" id="reportExpense">{{ auth()->user()->settings->currency->symbol }} 0
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="surface-card stat-card">
                <div class="stat-label">Surplus</div>
                <div class="stat-value" id="reportNet">{{ auth()->user()->settings->currency->symbol }} 0</div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="surface-card stat-card">
                <div class="stat-label">Net worth</div>
                <div class="stat-value" id="reportNetWorth">{{ auth()->user()->settings->currency->symbol }} 0</div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-7">
            <section class="surface-card">
                <div class="card-header-clean">
                    <h2>Income vs expense trend</h2>
                </div>
                <div class="card-body-clean report-chart">
                    <canvas id="trendChart"></canvas>
                </div>
            </section>
        </div>
        <div class="col-xl-5">
            <section class="surface-card">
                <div class="card-header-clean">
                    <h2>Expense by category</h2>
                </div>
                <div class="card-body-clean report-chart">
                    <canvas id="categoryChart"></canvas>
                </div>
            </section>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12">
            <section class="surface-card">
                <div class="card-header-clean">
                    <h2>Category Budget vs Actual Spending</h2>
                </div>
                <div class="card-body-clean report-chart" style="height: 280px;">
                    <canvas id="budgetChart"></canvas>
                </div>
            </section>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-xl-6">
            <section class="surface-card">
                <div class="card-header-clean">
                    <h2>Cash flow</h2>
                </div>
                <div class="card-body-clean" id="cashFlowReport"></div>
            </section>
        </div>
        <div class="col-xl-6">
            <section class="surface-card">
                <div class="card-header-clean">
                    <h2>Net worth breakdown</h2>
                </div>
                <div class="card-body-clean" id="netWorthBreakdown"></div>
            </section>
        </div>
    </div>

    <!-- Advanced Accounting & Financial Reports Tabs -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <section class="surface-card">
                <div class="card-header-clean p-3 border-bottom">
                    <ul class="nav nav-pills flex-wrap gap-1" id="accountingReportTabs">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabTrialBalance"
                                type="button">
                                <i class="bi bi-scale me-1"></i>Trial Balance
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabBalanceSheet" type="button">
                                <i class="bi bi-file-earmark-spreadsheet me-1"></i>Balance Sheet
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabProfitLoss"
                                type="button">
                                <i class="bi bi-graph-up-arrow me-1"></i>Profit & Loss (P&L)
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabGeneralLedger"
                                type="button">
                                <i class="bi bi-journal-bookmark me-1"></i>General Ledger
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabPartyLedger"
                                type="button">
                                <i class="bi bi-person-lines-fill me-1"></i>Party Statement
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabComparison"
                                type="button">
                                <i class="bi bi-bar-chart-steps me-1"></i>MoM & YoY Growth
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabBurnRate" type="button">
                                <i class="bi bi-lightning-charge me-1"></i>Daily Burn Rate
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabCommitteesReport"
                                type="button">
                                <i class="bi bi-diagram-3 me-1"></i>Committees
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body-clean">
                    <div class="tab-content">
                        <!-- Tab 1: Trial Balance -->
                        <div class="tab-pane fade show active" id="tabTrialBalance">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-secondary small">Verifies that total debits equal total credits across
                                    all ledger accounts.</span>
                                <span class="badge bg-success" id="tbStatusBadge">Balanced</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Ledger Account</th>
                                            <th>Kind / Type</th>
                                            <th class="text-end">Total Debit</th>
                                            <th class="text-end">Total Credit</th>
                                            <th class="text-end">Net Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody id="trialBalanceTableBody">
                                        <tr>
                                            <td colspan="5" class="text-center text-secondary py-3">Loading Trial
                                                Balance...</td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-light fw-bold">
                                        <tr>
                                            <td colspan="2">Total Ledger Balance</td>
                                            <td class="text-end" id="tbTotalDebit">
                                                {{ auth()->user()->settings->currency->symbol }} 0</td>
                                            <td class="text-end" id="tbTotalCredit">
                                                {{ auth()->user()->settings->currency->symbol }} 0</td>
                                            <td class="text-end" id="tbNetEquilibrium">—</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Tab 2: Balance Sheet -->
                        <div class="tab-pane fade" id="tabBalanceSheet">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-secondary small">Comprehensive financial position verifying
                                    <strong>Assets = Liabilities + Equity</strong> with prior period comparison.</span>
                                <span class="badge bg-primary" id="bsStatusBadge">Equilibrium Verified</span>
                            </div>
                            <div class="row g-4" id="balanceSheetContent">
                                <div class="text-center py-4 text-secondary">Loading Balance Sheet...</div>
                            </div>
                        </div>

                        <!-- Tab 3: Profit & Loss -->
                        <div class="tab-pane fade" id="tabProfitLoss">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="text-secondary small">Statement of financial performance comparing current
                                    period against prior period of equal length.</span>
                            </div>
                            <div id="pnlContent">
                                <div class="text-center py-4 text-secondary">Loading P&L Statement...</div>
                            </div>
                        </div>

                        <!-- Tab 4: General Ledger Statement -->
                        <div class="tab-pane fade" id="tabGeneralLedger">
                            <div class="row g-3 mb-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label">Select Ledger Account</label>
                                    <select class="form-select" id="glAccountSelect"></select>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-primary" id="btnLoadGL"><i class="bi bi-search me-1"></i>View
                                        Statement</button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Date</th>
                                            <th>Reference</th>
                                            <th>Description</th>
                                            <th class="text-end">Debit</th>
                                            <th class="text-end">Credit</th>
                                            <th class="text-end">Running Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody id="generalLedgerTableBody">
                                        <tr>
                                            <td colspan="6" class="text-center text-secondary py-3">Select an account
                                                above to view statement.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab 5: Party Account Ledger -->
                        <div class="tab-pane fade" id="tabPartyLedger">
                            <div class="row g-3 mb-3 align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label">Select Person / Party</label>
                                    <select class="form-select" id="partySelect"></select>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-primary" id="btnLoadPartyLedger"><i
                                            class="bi bi-journal-text me-1"></i>Load Party Statement</button>
                                </div>
                            </div>
                            <div id="partyLedgerContainer">
                                <div class="text-center py-4 text-secondary">Select a person above to view their dedicated
                                    account statement.</div>
                            </div>
                        </div>

                        <!-- Tab 6: MoM & YoY Growth Comparison -->
                        <div class="tab-pane fade" id="tabComparison">
                            <div class="row g-3 mb-3 align-items-center">
                                <div class="col-md-3">
                                    <label class="form-label">Select Year</label>
                                    <select class="form-select" id="compYearSelect">
                                        <option value="2026" selected>2026</option>
                                        <option value="2025">2025</option>
                                        <option value="2024">2024</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button class="btn btn-primary mt-4" id="btnLoadComparison"><i
                                            class="bi bi-arrow-repeat me-1"></i>Calculate Growth</button>
                                </div>
                            </div>
                            <div id="comparisonContainer">
                                <div class="text-center py-4 text-secondary">Loading MoM & YoY Growth comparison...</div>
                            </div>
                        </div>

                        <!-- Tab 7: Daily Burn Rate -->
                        <div class="tab-pane fade" id="tabBurnRate">
                            <div id="burnRateContainer">
                                <div class="text-center py-4 text-secondary">Loading cashflow burn rate analysis...</div>
                            </div>
                        </div>

                        <!-- Tab 8: Committees Overview Section -->
                        <div class="tab-pane fade" id="tabCommitteesReport">
                            <div class="row g-3 mb-3">
                                <div class="col-sm-6 col-md-3">
                                    <div class="bg-light p-3 rounded">
                                        <div class="small text-secondary">Active Committees</div>
                                        <div class="h5 mb-0 fw-bold" id="commReportActive">0</div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="bg-light p-3 rounded">
                                        <div class="small text-secondary">Active Monthly Contribution</div>
                                        <div class="h5 mb-0 fw-bold text-primary" id="commReportMonthly">
                                            {{ auth()->user()->settings->currency->symbol }} 0</div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="bg-light p-3 rounded">
                                        <div class="small text-secondary">Total Collected</div>
                                        <div class="h5 mb-0 fw-bold text-success" id="commReportCollected">
                                            {{ auth()->user()->settings->currency->symbol }} 0</div>
                                    </div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div class="bg-light p-3 rounded">
                                        <div class="small text-secondary">Total Disbursed</div>
                                        <div class="h5 mb-0 fw-bold text-info" id="commReportDisbursed">
                                            {{ auth()->user()->settings->currency->symbol }} 0</div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Committee</th>
                                            <th>Contribution / Slot</th>
                                            <th>Pot / Round</th>
                                            <th>Rounds Progress</th>
                                            <th>Next Due Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="commReportTableBody">
                                        <tr>
                                            <td colspan="6" class="text-center text-secondary py-3">Loading committees
                                                report...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="page-actions mt-4">
        <button class="btn btn-outline-secondary" type="button" onclick="window.print()">
            <i class="bi bi-printer me-2"></i>Print / Save PDF
        </button>
        <a class="btn btn-outline-secondary" id="csvExport" href="{{ route('exports.transactions') }}">
            <i class="bi bi-filetype-csv me-2"></i>Export transactions CSV
        </a>
        <a class="btn btn-outline-secondary" href="{{ route('exports.backup') }}">
            <i class="bi bi-download me-2"></i>Download JSON backup
        </a>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.0/dist/chart.umd.min.js"></script>
    <script>
        (() => {
            const M = window.MyLedger;
            let trendChart, categoryChart, budgetChart;
            const from = document.querySelector('#reportFrom');
            const to = document.querySelector('#reportTo');
            const preset = document.querySelector('#periodPreset');

            function iso(d) {
                return new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
            }

            function applyPreset() {
                const now = new Date(),
                    a = new Date(now),
                    b = new Date(now);
                if (preset.value === 'today') {} else if (preset.value === 'week') {
                    const day = (now.getDay() + 6) % 7;
                    a.setDate(now.getDate() - day);
                    b.setDate(a.getDate() + 6);
                } else if (preset.value === 'month') {
                    a.setDate(1);
                    b.setMonth(now.getMonth() + 1, 0);
                } else if (preset.value === 'year') {
                    a.setMonth(0, 1);
                    b.setMonth(11, 31);
                } else return;

                from.value = iso(a);
                to.value = iso(b);
            }

            async function loadAccountsSelect() {
                try {
                    const accounts = await M.request('/ajax/accounts');
                    M.fillSelect(document.querySelector('#glAccountSelect'), accounts, {
                        placeholder: 'Select account for GL statement...'
                    });
                } catch (e) {}
            }

            async function loadPeopleSelect() {
                try {
                    const people = await M.request('/ajax/people');
                    M.fillSelect(document.querySelector('#partySelect'), people, {
                        placeholder: 'Select person / party...'
                    });
                } catch (e) {}
            }

            async function loadBalanceSheet() {
                try {
                    const bs = await M.request('/ajax/reports/balance-sheet?as_of=' + to.value);
                    const badge = document.querySelector('#bsStatusBadge');
                    badge.className = bs.totals.is_balanced ? 'badge bg-success' : 'badge bg-danger';
                    badge.textContent = bs.totals.is_balanced ?
                        'Equilibrium Verified (Assets = Liabilities + Equity)' : 'Unbalanced';

                    const container = document.querySelector('#balanceSheetContent');
                    container.innerHTML = `
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header bg-success text-white fw-bold d-flex justify-content-between">
                            <span>ASSETS</span>
                            <span>${formatMoney(bs.totals.assets)}</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                    <tr class="table-light">
                                        <th>Asset Account</th>
                                        <th class="text-end">Current Balance</th>
                                        <th class="text-end">Prior (${M.date(bs.prior_as_of)})</th>
                                        <th class="text-end">Change</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${bs.assets.map(a => `
                                            <tr>
                                                <td class="fw-semibold">${M.esc(a.name)}</td>
                                                <td class="text-end fw-bold">${formatMoney(a.current_balance)}</td>
                                                <td class="text-end text-secondary">${formatMoney(a.prior_balance)}</td>
                                                <td class="text-end ${a.change >= 0 ? 'text-success' : 'text-danger'}">${a.change >= 0 ? '+' : ''}${formatMoney(a.change)}</td>
                                            </tr>
                                        `).join('') || '<tr><td colspan="4" class="text-center text-secondary py-2">No asset accounts found</td></tr>'}
                                </tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td>Total Assets</td>
                                        <td class="text-end text-success">${formatMoney(bs.totals.assets)}</td>
                                        <td class="text-end text-secondary">${formatMoney(bs.totals.prior_assets)}</td>
                                        <td class="text-end">${formatMoney(bs.totals.assets - bs.totals.prior_assets)}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header bg-danger text-white fw-bold d-flex justify-content-between">
                            <span>LIABILITIES & EQUITY</span>
                            <span>${formatMoney(bs.totals.liabilities_plus_equity)}</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                    <tr class="table-light">
                                        <th>Liability / Equity Account</th>
                                        <th class="text-end">Current Balance</th>
                                        <th class="text-end">Prior</th>
                                        <th class="text-end">Change</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="table-secondary fw-bold"><td colspan="4">Liabilities</td></tr>
                                    ${bs.liabilities.map(l => `
                                            <tr>
                                                <td class="fw-semibold ps-3">${M.esc(l.name)}</td>
                                                <td class="text-end fw-bold text-danger">${formatMoney(l.current_balance)}</td>
                                                <td class="text-end text-secondary">${formatMoney(l.prior_balance)}</td>
                                                <td class="text-end">${l.change >= 0 ? '+' : ''}${formatMoney(l.change)}</td>
                                            </tr>
                                        `).join('')}
                                    <tr class="fw-bold border-bottom">
                                        <td class="ps-3">Subtotal Liabilities</td>
                                        <td class="text-end text-danger">${formatMoney(bs.totals.liabilities)}</td>
                                        <td class="text-end">${formatMoney(bs.totals.prior_liabilities)}</td>
                                        <td class="text-end">${formatMoney(bs.totals.liabilities - bs.totals.prior_liabilities)}</td>
                                    </tr>

                                    <tr class="table-secondary fw-bold"><td colspan="4">Equity</td></tr>
                                    ${bs.equity_accounts.map(e => `
                                            <tr>
                                                <td class="fw-semibold ps-3">${M.esc(e.name)}</td>
                                                <td class="text-end fw-bold">${formatMoney(e.current_balance)}</td>
                                                <td class="text-end text-secondary">${formatMoney(e.prior_balance)}</td>
                                                <td class="text-end">${e.change >= 0 ? '+' : ''}${formatMoney(e.change)}</td>
                                            </tr>
                                        `).join('')}
                                    <tr>
                                        <td class="fw-semibold ps-3">Retained Earnings (Accumulated Surplus)</td>
                                        <td class="text-end fw-bold text-primary">${formatMoney(bs.retained_earnings.current)}</td>
                                        <td class="text-end text-secondary">${formatMoney(bs.retained_earnings.prior)}</td>
                                        <td class="text-end ${bs.retained_earnings.change >= 0 ? 'text-success' : 'text-danger'}">${bs.retained_earnings.change >= 0 ? '+' : ''}${formatMoney(bs.retained_earnings.change)}</td>
                                    </tr>
                                    <tr class="fw-bold border-bottom">
                                        <td class="ps-3">Subtotal Equity</td>
                                        <td class="text-end text-primary">${formatMoney(bs.totals.equity)}</td>
                                        <td class="text-end">${formatMoney(bs.totals.prior_equity)}</td>
                                        <td class="text-end">${formatMoney(bs.totals.equity - bs.totals.prior_equity)}</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light fw-bold">
                                    <tr>
                                        <td>Total Liabilities & Equity</td>
                                        <td class="text-end text-dark">${formatMoney(bs.totals.liabilities_plus_equity)}</td>
                                        <td class="text-end text-secondary">${formatMoney(bs.totals.prior_liabilities + bs.totals.prior_equity)}</td>
                                        <td class="text-end">${formatMoney(bs.totals.liabilities_plus_equity - (bs.totals.prior_liabilities + bs.totals.prior_equity))}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            `;
                } catch (e) {}
            }

            async function loadProfitLoss() {
                try {
                    const pnl = await M.request(`/ajax/reports/profit-and-loss?from=${from.value}&to=${to.value}`);
                    const container = document.querySelector('#pnlContent');
                    container.innerHTML = `
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="surface-card p-3 text-center border-start border-4 border-success">
                            <div class="small text-secondary">Total Operating Income</div>
                            <div class="h4 mb-0 text-success fw-bold mt-1">${formatMoney(pnl.totals.current_income)}</div>
                            <div class="small text-secondary mt-1">Prior period: ${formatMoney(pnl.totals.prior_income)}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="surface-card p-3 text-center border-start border-4 border-danger">
                            <div class="small text-secondary">Total Operating Expenses</div>
                            <div class="h4 mb-0 text-danger fw-bold mt-1">${formatMoney(pnl.totals.current_expense)}</div>
                            <div class="small text-secondary mt-1">Prior period: ${formatMoney(pnl.totals.prior_expense)}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="surface-card p-3 text-center border-start border-4 border-primary">
                            <div class="small text-secondary">Net Operating Profit & Margin</div>
                            <div class="h4 mb-0 text-primary fw-bold mt-1">${formatMoney(pnl.totals.current_net)}</div>
                            <div class="small fw-semibold mt-1 ${pnl.totals.net_margin_percent >= 0 ? 'text-success' : 'text-danger'}">
                                Net Margin: ${pnl.totals.net_margin_percent}%
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Category / Account</th>
                                <th class="text-end">Current Period (${M.date(from.value)} to ${M.date(to.value)})</th>
                                <th class="text-end">Prior Period (${M.date(pnl.prior_from)} to ${M.date(pnl.prior_to)})</th>
                                <th class="text-end">Variance Amount</th>
                                <th class="text-end">Variance %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-success fw-bold"><td colspan="5">REVENUE & INCOME</td></tr>
                            ${pnl.income.map(i => `
                                    <tr>
                                        <td class="fw-semibold ps-3">${M.esc(i.name)}</td>
                                        <td class="text-end fw-bold text-success">${formatMoney(i.current)}</td>
                                        <td class="text-end text-secondary">${formatMoney(i.prior)}</td>
                                        <td class="text-end ${i.change >= 0 ? 'text-success' : 'text-danger'}">${i.change >= 0 ? '+' : ''}${formatMoney(i.change)}</td>
                                        <td class="text-end"><span class="badge ${i.change_percent >= 0 ? 'bg-success' : 'bg-danger'}">${i.change_percent >= 0 ? '+' : ''}${i.change_percent}%</span></td>
                                    </tr>
                                `).join('') || '<tr><td colspan="5" class="text-center text-secondary py-2 ps-3">No income records</td></tr>'}

                            <tr class="table-danger fw-bold"><td colspan="5">OPERATING EXPENSES</td></tr>
                            ${pnl.expense.map(e => `
                                    <tr>
                                        <td class="fw-semibold ps-3">${M.esc(e.name)}</td>
                                        <td class="text-end fw-bold text-danger">${formatMoney(e.current)}</td>
                                        <td class="text-end text-secondary">${formatMoney(e.prior)}</td>
                                        <td class="text-end ${e.change <= 0 ? 'text-success' : 'text-danger'}">${e.change >= 0 ? '+' : ''}${formatMoney(e.change)}</td>
                                        <td class="text-end"><span class="badge ${e.change_percent <= 0 ? 'bg-success' : 'bg-danger'}">${e.change_percent >= 0 ? '+' : ''}${e.change_percent}%</span></td>
                                    </tr>
                                `).join('') || '<tr><td colspan="5" class="text-center text-secondary py-2 ps-3">No expense records</td></tr>'}
                        </tbody>
                        <tfoot class="table-light fw-bold fs-6">
                            <tr>
                                <td>NET PROFIT / SURPLUS</td>
                                <td class="text-end text-primary">${formatMoney(pnl.totals.current_net)}</td>
                                <td class="text-end text-secondary">${formatMoney(pnl.totals.prior_net)}</td>
                                <td class="text-end ${pnl.totals.net_change >= 0 ? 'text-success' : 'text-danger'}">${pnl.totals.net_change >= 0 ? '+' : ''}${formatMoney(pnl.totals.net_change)}</td>
                                <td class="text-end"><span class="badge bg-primary">Margin ${pnl.totals.net_margin_percent}%</span></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;
                } catch (e) {}
            }

            async function loadPartyLedger() {
                const personId = document.querySelector('#partySelect').value;
                if (!personId) return;

                try {
                    const pl = await M.request(
                        `/ajax/reports/party-ledger?person_id=${personId}&from=${from.value}&to=${to.value}`);
                    const container = document.querySelector('#partyLedgerContainer');
                    container.innerHTML = `
                <div class="surface-card p-3 mb-3 d-flex justify-content-between align-items-center bg-light">
                    <div>
                        <div class="h5 mb-1 fw-bold">${M.esc(pl.person.name)}</div>
                        <div class="small text-secondary">
                            ${pl.person.phone ? `<i class="bi bi-telephone me-1"></i>${M.esc(pl.person.phone)} ` : ''}
                            ${pl.person.email ? `<i class="bi bi-envelope me-1 ms-2"></i>${M.esc(pl.person.email)}` : ''}
                        </div>
                    </div>
                    <div class="text-end">
                        <div class="small text-secondary">Net Closing Balance</div>
                        <div class="h4 mb-0 fw-bold ${pl.closing_balance >= 0 ? 'text-success' : 'text-danger'}">${formatMoney(pl.closing_balance)}</div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Reference</th>
                                <th>Type</th>
                                <th>Description</th>
                                <th class="text-end">Debit (+)</th>
                                <th class="text-end">Credit (-)</th>
                                <th class="text-end">Running Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="table-light fw-bold">
                                <td colspan="6">Opening Balance (as of ${M.date(from.value)})</td>
                                <td class="text-end">${formatMoney(pl.opening_balance)}</td>
                            </tr>
                            ${pl.entries.map(e => `
                                    <tr>
                                        <td>${M.date(e.date)}</td>
                                        <td class="font-monospace small">${M.esc(e.reference_no)}</td>
                                        <td><span class="badge bg-secondary-subtle text-dark text-capitalize">${e.type.replace('_',' ')}</span></td>
                                        <td>${M.esc(e.description)}</td>
                                        <td class="text-end text-danger fw-semibold">${e.debit > 0 ? formatMoneyWithOriginal(e.debit, e) : '—'}</td>
                                        <td class="text-end text-success fw-semibold">${e.credit > 0 ? formatMoneyWithOriginal(e.credit, e) : '—'}</td>
                                        <td class="text-end fw-bold">${formatMoney(e.running_balance)}</td>
                                    </tr>
                                `).join('') || '<tr><td colspan="7" class="text-center text-secondary py-3">No transaction activity during this period.</td></tr>'}
                        </tbody>
                        <tfoot class="table-light fw-bold">
                            <tr>
                                <td colspan="4">Total Period Activity</td>
                                <td class="text-end text-danger">${formatMoney(pl.total_debit)}</td>
                                <td class="text-end text-success">${formatMoney(pl.total_credit)}</td>
                                <td class="text-end text-primary">${formatMoney(pl.closing_balance)}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            `;
                } catch (e) {
                    M.toast(e.message, 'danger');
                }
            }

            document.querySelector('#btnLoadPartyLedger').onclick = loadPartyLedger;

            async function loadComparison() {
                const year = document.querySelector('#compYearSelect').value;
                try {
                    const comp = await M.request(`/ajax/reports/comparison?year=${year}`);
                    const container = document.querySelector('#comparisonContainer');

                    container.innerHTML = `
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="surface-card p-3 border-start border-4 border-success">
                            <div class="small text-secondary">${comp.year} Total Revenue</div>
                            <div class="h4 mb-1 text-success fw-bold">${formatMoney(comp.totals.year_income)}</div>
                            <div class="small">Growth vs ${comp.prior_year}: <span class="badge ${comp.totals.income_growth_percent >= 0 ? 'bg-success' : 'bg-danger'}">${comp.totals.income_growth_percent >= 0 ? '+' : ''}${comp.totals.income_growth_percent}%</span></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="surface-card p-3 border-start border-4 border-danger">
                            <div class="small text-secondary">${comp.year} Total Expenditure</div>
                            <div class="h4 mb-1 text-danger fw-bold">${formatMoney(comp.totals.year_expense)}</div>
                            <div class="small">Growth vs ${comp.prior_year}: <span class="badge ${comp.totals.expense_growth_percent <= 0 ? 'bg-success' : 'bg-danger'}">${comp.totals.expense_growth_percent >= 0 ? '+' : ''}${comp.totals.expense_growth_percent}%</span></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="surface-card p-3 border-start border-4 border-primary">
                            <div class="small text-secondary">${comp.year} Net Savings</div>
                            <div class="h4 mb-1 text-primary fw-bold">${formatMoney(comp.totals.year_net)}</div>
                            <div class="small">Growth vs ${comp.prior_year}: <span class="badge ${comp.totals.net_growth_percent >= 0 ? 'bg-success' : 'bg-danger'}">${comp.totals.net_growth_percent >= 0 ? '+' : ''}${comp.totals.net_growth_percent}%</span></div>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Month</th>
                                <th class="text-end">Income (${comp.year})</th>
                                <th class="text-end">Income (${comp.prior_year})</th>
                                <th class="text-end">Income Growth %</th>
                                <th class="text-end">Expense (${comp.year})</th>
                                <th class="text-end">Expense (${comp.prior_year})</th>
                                <th class="text-end">Expense Growth %</th>
                                <th class="text-end">Net Savings</th>
                                <th class="text-end">Savings Rate %</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${comp.months.map(m => `
                                    <tr>
                                        <td class="fw-bold">${m.month_name} ${comp.year}</td>
                                        <td class="text-end text-success fw-semibold">${formatMoney(m.income)}</td>
                                        <td class="text-end text-secondary">${formatMoney(m.prior_income)}</td>
                                        <td class="text-end"><span class="badge ${m.income_growth >= 0 ? 'bg-success' : 'bg-secondary'}">${m.income_growth >= 0 ? '+' : ''}${m.income_growth}%</span></td>
                                        
                                        <td class="text-end text-danger fw-semibold">${formatMoney(m.expense)}</td>
                                        <td class="text-end text-secondary">${formatMoney(m.prior_expense)}</td>
                                        <td class="text-end"><span class="badge ${m.expense_growth <= 0 ? 'bg-success' : 'bg-danger'}">${m.expense_growth >= 0 ? '+' : ''}${m.expense_growth}%</span></td>
                                        
                                        <td class="text-end fw-bold">${formatMoney(m.net)}</td>
                                        <td class="text-end"><span class="badge bg-info text-dark">${m.savings_rate}%</span></td>
                                    </tr>
                                `).join('')}
                        </tbody>
                    </table>
                </div>
            `;
                } catch (e) {}
            }

            document.querySelector('#btnLoadComparison').onclick = loadComparison;

            async function loadBurnRate() {
                const qs = M.query({
                    from: from.value,
                    to: to.value
                });
                try {
                    const br = await M.request(`/ajax/reports/burn-rate?${qs}`);
                    const container = document.querySelector('#burnRateContainer');

                    container.innerHTML = `
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="surface-card p-3 border-start border-4 border-warning">
                            <div class="small text-secondary">Daily Average Burn Rate</div>
                            <div class="h3 mb-0 text-warning fw-bold mt-1">${formatMoney(br.daily_average_burn)} / day</div>
                            <div class="small text-secondary mt-1">Based on ${br.days_count} days in period</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="surface-card p-3 border-start border-4 border-info">
                            <div class="small text-secondary">Liquid Assets Available</div>
                            <div class="h3 mb-0 text-info fw-bold mt-1">${formatMoney(br.liquid_assets)}</div>
                            <div class="small text-secondary mt-1">Cash, Bank & Wallet funds</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="surface-card p-3 border-start border-4 border-success">
                            <div class="small text-secondary">Estimated Cash Runway</div>
                            <div class="h3 mb-0 text-success fw-bold mt-1">${br.runway_days} Days</div>
                            <div class="small text-secondary mt-1">Months equivalent: ~${round(br.runway_days / 30, 1)} months</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="surface-card p-3 border-start border-4 border-danger">
                            <div class="small text-secondary">Total Burned in Period</div>
                            <div class="h3 mb-0 text-danger fw-bold mt-1">${formatMoney(br.total_expense)}</div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light fw-bold"><i class="bi bi-graph-up me-2 text-danger"></i>Peak Spending Days (Top 5)</div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr class="table-light">
                                            <th>Date</th>
                                            <th class="text-end">Total Spent</th>
                                            <th class="text-end">Multiple of Daily Avg</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${br.peak_spending_days.map(pd => `
                                                <tr>
                                                    <td class="fw-semibold">${M.date(pd.date)}</td>
                                                    <td class="text-end text-danger fw-bold">${formatMoney(pd.total)}</td>
                                                    <td class="text-end"><span class="badge bg-danger">${br.daily_average_burn > 0 ? round(pd.total / br.daily_average_burn, 1) : 1}x</span></td>
                                                </tr>
                                            `).join('') || '<tr><td colspan="3" class="text-center text-secondary py-3">No spending days recorded</td></tr>'}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light fw-bold"><i class="bi bi-pie-chart me-2 text-primary"></i>Category Burn Velocity Breakdown</div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr class="table-light">
                                            <th>Category</th>
                                            <th class="text-end">Total Spent</th>
                                            <th class="text-end">Share of Burn %</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${br.category_breakdown.map(cat => `
                                                <tr>
                                                    <td class="fw-semibold">${M.esc(cat.name)}</td>
                                                    <td class="text-end fw-bold">${formatMoney(cat.total)}</td>
                                                    <td class="text-end">
                                                        <div class="d-flex align-items-center justify-content-end gap-2">
                                                            <div class="progress" style="width: 60px; height: 6px;">
                                                                <div class="progress-bar bg-primary" style="width: ${cat.percent_of_burn}%;"></div>
                                                            </div>
                                                            <span class="small fw-bold">${cat.percent_of_burn}%</span>
                                                        </div>
                                                    </td>
                                                </tr>
                                            `).join('') || '<tr><td colspan="3" class="text-center text-secondary py-3">No category data</td></tr>'}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            `;
                } catch (e) {}
            }

            function round(val, decimals) {
                return Number(Math.round(val + 'e' + decimals) + 'e-' + decimals);
            }

            async function loadSmartInsights() {
                try {
                    const insights = await M.request('/ajax/reports/smart-insights');
                    const container = document.querySelector('#smartInsightsContainer');
                    if (!insights.length) {
                        container.innerHTML = '';
                        return;
                    }
                    container.innerHTML = insights.map(item => `
                <div class="col-md-4">
                    <div class="surface-card p-3 d-flex align-items-center gap-3 border-start border-4 border-${item.type}">
                        <div class="fs-3 text-${item.type}"><i class="bi ${item.icon}"></i></div>
                        <div>
                            <div class="fw-bold">${M.esc(item.title)}</div>
                            <div class="small text-secondary">${M.esc(item.message)}</div>
                        </div>
                    </div>
                </div>
            `).join('');
                } catch (e) {}
            }

            async function loadTrialBalance() {
                try {
                    const tb = await M.request('/ajax/reports/trial-balance?as_of=' + to.value);
                    document.querySelector('#tbStatusBadge').className = tb.is_balanced ? 'badge bg-success' :
                        'badge bg-danger';
                    document.querySelector('#tbStatusBadge').textContent = tb.is_balanced ?
                        'Balanced (Equilibrium Verified)' : 'Unbalanced';

                    document.querySelector('#tbTotalDebit').textContent = formatMoney(tb.total_debit);
                    document.querySelector('#tbTotalCredit').textContent = formatMoney(tb.total_credit);

                    const tbody = document.querySelector('#trialBalanceTableBody');
                    tbody.innerHTML = tb.accounts.map(acc => `
                <tr>
                    <td class="fw-bold">${M.esc(acc.name)}</td>
                    <td><span class="badge bg-secondary-subtle text-secondary text-capitalize">${acc.kind} / ${acc.type}</span></td>
                    <td class="text-end fw-semibold text-danger">${acc.debit > 0 ? formatMoney(acc.debit) : '—'}</td>
                    <td class="text-end fw-semibold text-success">${acc.credit > 0 ? formatMoney(acc.credit) : '—'}</td>
                    <td class="text-end fw-bold">${formatMoney(acc.net_balance)}</td>
                </tr>
            `).join('') ||
                    '<tr><td colspan="5" class="text-center text-secondary py-3">No account entries found.</td></tr>';
                } catch (e) {}
            }

            async function loadGeneralLedger() {
                const accountId = document.querySelector('#glAccountSelect').value;
                if (!accountId) return;

                try {
                    const gl = await M.request(
                        `/ajax/reports/general-ledger?account_id=${accountId}&from=${from.value}&to=${to.value}`
                        );
                    const tbody = document.querySelector('#generalLedgerTableBody');

                    let html = `
                <tr class="table-light fw-bold">
                    <td colspan="5">Opening Balance (as of ${M.date(from.value)})</td>
                    <td class="text-end">${formatMoney(gl.opening_balance)}</td>
                </tr>
            `;

                    html += gl.entries.map(e => `
                <tr>
                    <td>${M.date(e.date)}</td>
                    <td class="font-monospace small">${M.esc(e.reference_no)}</td>
                    <td>${M.esc(e.description)}</td>
                    <td class="text-end text-danger">${e.debit > 0 ? formatMoneyWithOriginal(e.debit, e) : '—'}</td>
                    <td class="text-end text-success">${e.credit > 0 ? formatMoneyWithOriginal(e.credit, e) : '—'}</td>
                    <td class="text-end fw-bold">${formatMoney(e.running_balance)}</td>
                </tr>
            `).join('');

                    html += `
                <tr class="table-light fw-bold">
                    <td colspan="5">Closing Balance (as of ${M.date(to.value)})</td>
                    <td class="text-end text-primary">${formatMoney(gl.closing_balance)}</td>
                </tr>
            `;

                    tbody.innerHTML = html;
                } catch (e) {
                    M.toast(e.message, 'danger');
                }
            }

            document.querySelector('#btnLoadGL').onclick = loadGeneralLedger;

            // --- Currency handling ---
            let selectedCurrency = null;
            let cachedCurrencies = [];

            async function loadCurrencies() {
                try {
                    // Fetch user settings to get base currency
                    let userBaseCurrencyId = null;
                    try {
                        const settings = await M.request('/ajax/settings');
                        userBaseCurrencyId = settings?.base_currency_id;
                    } catch (e) {}

                    cachedCurrencies = await M.request('/ajax/currencies');
                    const currencySelect = document.querySelector('#reportCurrency');
                    M.fillSelect(currencySelect, cachedCurrencies, {
                        value: 'id',
                        label: c => `${c.code} (${c.symbol})`,
                        placeholder: 'Select currency...'
                    });

                    // Load saved currency from localStorage
                    const savedCurrencyId = localStorage.getItem('reportCurrencyId');
                    if (savedCurrencyId && cachedCurrencies.find(c => String(c.id) === String(savedCurrencyId))) {
                        currencySelect.value = savedCurrencyId;
                        selectedCurrency = cachedCurrencies.find(c => String(c.id) === String(savedCurrencyId));
                    } else if (userBaseCurrencyId && cachedCurrencies.find(c => String(c.id) === String(
                            userBaseCurrencyId))) {
                        // Default to user's base currency setting
                        currencySelect.value = userBaseCurrencyId;
                        selectedCurrency = cachedCurrencies.find(c => String(c.id) === String(userBaseCurrencyId));
                    } else if (cachedCurrencies.length > 0) {
                        // Fallback: base currency (exchange_rate = 1) or first currency
                        const baseCurrency = cachedCurrencies.find(c => Number(c.exchange_rate) === 1) ||
                            cachedCurrencies[0];
                        currencySelect.value = baseCurrency.id;
                        selectedCurrency = baseCurrency;
                    }

                    currencySelect.onchange = () => {
                        selectedCurrency = cachedCurrencies.find(c => String(c.id) === String(currencySelect
                            .value));
                        if (selectedCurrency) {
                            localStorage.setItem('reportCurrencyId', selectedCurrency.id);
                            load(); // Refresh all reports with new currency
                        }
                    };
                } catch (e) {}
            }

            function formatMoney(value) {
                const number = Number(value || 0);
                if (!selectedCurrency)
                return `{{ auth()->user()->settings->currency->symbol }} ${number.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 })}`;
                const rate = Number(selectedCurrency.exchange_rate || 1);
                const converted = number * rate;
                return `${selectedCurrency.symbol} ${converted.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 })}`;
            }

            // Format money with support for original foreign currency amounts
            function formatMoneyWithOriginal(baseAmount, entry) {
                // If entry has original currency info and it matches selected display currency, show original
                if (entry?.original_amount !== null && entry?.original_currency_id && entry?.exchange_rate) {
                    const origCurrency = cachedCurrencies.find(c => String(c.id) === String(entry
                    .original_currency_id));
                    if (origCurrency && selectedCurrency && String(origCurrency.id) === String(selectedCurrency.id)) {
                        const origAmt = Number(entry.original_amount || 0);
                        return `${origCurrency.symbol} ${origAmt.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 })}`;
                    }
                }
                // Otherwise convert from base currency to display currency
                return formatMoney(baseAmount);
            }

            async function load() {
                const qs = M.query({
                    from: from.value,
                    to: to.value
                });
                try {
                    const [summary, cats, trend, nw, cf, comm, budgets] = await Promise.all([
                        M.request('/ajax/reports/summary?' + qs),
                        M.request('/ajax/reports/expense-by-category?' + qs),
                        M.request('/ajax/reports/monthly-trend?months=12'),
                        M.request('/ajax/reports/net-worth?as_of=' + to.value),
                        M.request('/ajax/reports/cash-flow?' + qs),
                        M.request('/ajax/reports/committees'),
                        M.request('/ajax/budgets')
                    ]);

                    document.querySelector('#reportIncome').textContent = formatMoney(summary.income);
                    document.querySelector('#reportExpense').textContent = formatMoney(summary.expense);
                    document.querySelector('#reportNet').textContent = formatMoney(summary.net_cash_surplus);
                    document.querySelector('#reportNetWorth').textContent = formatMoney(nw.net_worth);

                    // Trend chart
                    trendChart?.destroy();
                    trendChart = new Chart(document.querySelector('#trendChart'), {
                        type: 'line',
                        data: {
                            labels: trend.map(x => x.month),
                            datasets: [{
                                    label: 'Income',
                                    data: trend.map(x => x.income),
                                    tension: .35
                                },
                                {
                                    label: 'Expense',
                                    data: trend.map(x => x.expense),
                                    tension: .35
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    // Category chart
                    categoryChart?.destroy();
                    categoryChart = new Chart(document.querySelector('#categoryChart'), {
                        type: 'doughnut',
                        data: {
                            labels: cats.map(x => x.name),
                            datasets: [{
                                data: cats.map(x => x.total)
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });

                    // Budget vs Actual chart
                    budgetChart?.destroy();
                    budgetChart = new Chart(document.querySelector('#budgetChart'), {
                        type: 'bar',
                        data: {
                            labels: (budgets || []).map(b => b.category?.name || 'Category'),
                            datasets: [{
                                    label: 'Budget Target',
                                    data: (budgets || []).map(b => b.amount),
                                    backgroundColor: 'rgba(40, 95, 115, 0.55)'
                                },
                                {
                                    label: 'Spent Amount',
                                    data: (budgets || []).map(b => b.spent || 0),
                                    backgroundColor: 'rgba(220, 53, 69, 0.75)'
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true
                                }
                            }
                        }
                    });

                    // Cash flow
                    document.querySelector('#cashFlowReport').innerHTML = `
                <div class="row g-3">
                    <div class="col-4"><div class="small text-secondary">External inflow</div><div class="h5 text-success mt-1">${formatMoney(cf.inflow)}</div></div>
                    <div class="col-4"><div class="small text-secondary">External outflow</div><div class="h5 text-danger mt-1">${formatMoney(cf.outflow)}</div></div>
                    <div class="col-4"><div class="small text-secondary">Net cash flow</div><div class="h5 mt-1">${formatMoney(cf.net)}</div></div>
                </div>
                <hr>
                <div class="small text-secondary">Internal transfers excluded from inflow/outflow: ${formatMoney(cf.internal_transfers)}</div>
            `;

                    // Net worth breakdown
                    document.querySelector('#netWorthBreakdown').innerHTML = `
                <div class="d-flex justify-content-between mb-2"><span>Assets</span><strong>${formatMoney(nw.asset_total)}</strong></div>
                ${nw.assets.slice(0, 5).map(x => `<div class="d-flex justify-content-between small text-secondary py-1"><span>${M.esc(x.name)}</span><span>${formatMoney(x.balance)}</span></div>`).join('')}
                <hr>
                <div class="d-flex justify-content-between mb-2"><span>Liabilities</span><strong>${formatMoney(nw.liability_total)}</strong></div>
                ${nw.liabilities.slice(0, 5).map(x => `<div class="d-flex justify-content-between small text-secondary py-1"><span>${M.esc(x.name)}</span><span>${formatMoney(x.balance)}</span></div>`).join('')}
            `;

                    // Committees Report
                    document.querySelector('#commReportActive').textContent = comm.active_count || 0;
                    document.querySelector('#commReportMonthly').textContent = formatMoney(comm
                        .monthly_contribution_total || 0);
                    document.querySelector('#commReportCollected').textContent = formatMoney(comm.total_collected ||
                        0);
                    document.querySelector('#commReportDisbursed').textContent = formatMoney(comm.total_disbursed ||
                        0);

                    const commList = comm.committees || [];
                    const commTbody = document.querySelector('#commReportTableBody');
                    if (!commList.length) {
                        commTbody.innerHTML =
                            `<tr><td colspan="6" class="text-center text-secondary py-3">No committees found.</td></tr>`;
                    } else {
                        commTbody.innerHTML = commList.map(c => `
                    <tr>
                        <td class="fw-bold">${M.esc(c.name)}</td>
                        <td>${formatMoney(c.contribution_amount)}</td>
                        <td class="fw-bold text-success">${formatMoney(c.total_pool_amount)}</td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress flex-fill" style="height: 6px;">
                                    <div class="progress-bar bg-success" style="width: ${c.progress_percent}%;"></div>
                                </div>
                                <span class="small text-secondary">${c.completed_rounds}/${c.total_members}</span>
                            </div>
                        </td>
                        <td>${c.next_due_date ? M.date(c.next_due_date) : '<span class="text-secondary">—</span>'}</td>
                        <td>
                            ${c.status === 'completed' ? '<span class="badge bg-success">Completed</span>' : '<span class="badge bg-primary">Active</span>'}
                        </td>
                    </tr>
                `).join('');
                    }

                    loadTrialBalance();
                    loadSmartInsights();

                    // Auto-refresh whichever active report tab is visible
                    const activeTab = document.querySelector('#accountingReportTabs .nav-link.active');
                    if (activeTab) {
                        triggerTabLoad(activeTab.getAttribute('data-bs-target'));
                    }

                    document.querySelector('#csvExport').href = '{{ route('exports.transactions') }}?' + qs;
                } catch (e) {
                    M.toast(e.message, 'danger');
                }
            }

            function triggerTabLoad(target) {
                if (target === '#tabBalanceSheet') loadBalanceSheet();
                else if (target === '#tabProfitLoss') loadProfitLoss();
                else if (target === '#tabComparison') loadComparison();
                else if (target === '#tabBurnRate') loadBurnRate();
                else if (target === '#tabGeneralLedger') loadGeneralLedger();
                else if (target === '#tabPartyLedger') loadPartyLedger();
                else if (target === '#tabTrialBalance') loadTrialBalance();
            }

            // Attach listener to load reports on tab switch
            document.querySelectorAll('#accountingReportTabs button[data-bs-toggle="tab"]').forEach(tabBtn => {
                tabBtn.addEventListener('shown.bs.tab', (e) => {
                    const target = e.target.getAttribute('data-bs-target');
                    triggerTabLoad(target);
                });
            });

            preset.onchange = () => {
                applyPreset();
                if (preset.value !== 'custom') load();
            };

            document.querySelector('#runReport').onclick = load;

            applyPreset();
            loadAccountsSelect();
            loadPeopleSelect();
            loadCurrencies();
            load();
        })();
    </script>
@endpush
