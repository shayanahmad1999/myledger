@extends('layouts.app')
@section('title', 'Settings')
@section('page-title', 'Settings')
@section('page-subtitle', 'Profile, preferences, security and data export.')
@section('content')
    <div class="row g-3">
        <div class="col-xl-7">
            <section class="surface-card mb-3">
                <div class="card-header-clean">
                    <h2>Profile</h2>
                </div>
                <div class="card-body-clean">
                    <form id="profileForm">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Name</label><input class="form-control"
                                    name="name" value="{{ auth()->user()->name }}" required></div>
                            <div class="col-md-6"><label class="form-label">Email</label><input class="form-control"
                                    name="email" type="email" value="{{ auth()->user()->email }}" required></div>
                        </div>
                        <div class="text-end mt-3"><button class="btn btn-primary">Save profile</button></div>
                    </form>
                </div>
            </section>
            <section class="surface-card mb-3">
                <div class="card-header-clean">
                    <h2>Preferences</h2>
                </div>
                <div class="card-body-clean">
                    <form id="settingsForm">
                        <div class="row g-3">
                            <div class="col-md-3"><label class="form-label">Base Currency</label><select class="form-select"
                                    name="base_currency_id" id="baseCurrencySelect" required></select></div>
                            <div class="col-md-3"><label class="form-label">Theme</label><select class="form-select"
                                    name="theme">
                                    <option value="system">System</option>
                                    <option value="light">Light</option>
                                    <option value="dark">Dark</option>
                                </select></div>
                            <div class="col-md-3"><label class="form-label">Locale</label><input class="form-control"
                                    name="locale" value="en" maxlength="10"></div>
                            <div class="col-md-3"><label class="form-label">Daily reminder</label><input
                                    class="form-control" name="daily_reminder_time" type="time"></div>
                        </div>
                        <div class="text-end mt-3"><button class="btn btn-primary">Save preferences</button></div>
                    </form>
                </div>
            </section>
            <section class="surface-card mb-3">
                <div class="card-header-clean">
                    <h2>Change password</h2>
                </div>
                <div class="card-body-clean">
                    <form id="passwordForm">
                        <div class="mb-3"><label class="form-label">Current password</label><input class="form-control"
                                type="password" name="current_password" required></div>
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">New password</label><input class="form-control"
                                    type="password" name="password" required></div>
                            <div class="col-md-6"><label class="form-label">Confirm new password</label><input
                                    class="form-control" type="password" name="password_confirmation" required></div>
                        </div>
                        <div class="text-end mt-3"><button class="btn btn-primary">Update password</button></div>
                    </form>
                </div>
            </section>
            <section class="surface-card">
                <div class="card-header-clean">
                    <h2>Recent accounting activity</h2>
                </div>
                <div class="card-body-clean" id="auditList">
                    <div class="small text-secondary">Loading audit history…</div>
                </div>
            </section>
        </div>
        <div class="col-xl-5">
            <section class="surface-card mb-3">
                <div class="card-header-clean d-flex justify-content-between align-items-center">
                    <h2>Currencies & Exchange Rates</h2><button class="btn btn-sm btn-primary" id="btnNewCurrency"><i
                            class="bi bi-plus-lg me-1"></i>Add Currency</button>
                </div>
                <div class="card-body-clean">
                    <p class="small text-secondary">View, add, and manage system currencies and exchange rates.</p>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Symbol</th>
                                    <th class="text-end">Rate</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody id="currencyList">
                                <tr>
                                    <td colspan="5" class="text-secondary small">Loading currencies...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
            <section class="surface-card mb-3">
                <div class="card-header-clean">
                    <h2>Data & backup</h2>
                </div>
                <div class="card-body-clean">
                    <p class="small text-secondary">Export a complete JSON backup or your transaction history as CSV.</p>
                    <div class="d-grid gap-2"><a class="btn btn-outline-secondary" href="{{ route('exports.backup') }}"><i
                                class="bi bi-download me-2"></i>Download full backup</a><a
                            class="btn btn-outline-secondary" href="{{ route('exports.transactions') }}"><i
                                class="bi bi-filetype-csv me-2"></i>Export transactions CSV</a></div>
                </div>
            </section>
            <section class="surface-card">
                <div class="card-header-clean">
                    <h2>Web security</h2>
                </div>
                <div class="card-body-clean">
                    <div class="d-flex gap-3">
                        <div class="account-icon"><i class="bi bi-shield-lock"></i></div>
                        <div>
                            <div class="fw-semibold">Session protected</div>
                            <p class="small text-secondary mb-0 mt-1">MyLedger uses Laravel session authentication, hashed
                                passwords and CSRF protection for browser actions. Always use HTTPS in production.</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="modal fade" id="currencyModal">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="currencyForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="currencyModalTitle">Edit Exchange Rate</h5><button class="btn-close"
                        type="button" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"><input type="hidden" name="id">
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Currency Code (3 letters)</label><input
                                class="form-control" name="code" placeholder="e.g. USD" maxlength="3" required>
                        </div>
                        <div class="col-md-6"><label class="form-label">Symbol</label><input class="form-control"
                                name="symbol" placeholder="e.g. $" required></div>
                    </div>
                    <div class="mt-3"><label class="form-label">Currency Name</label><input class="form-control"
                            name="name" placeholder="e.g. US Dollar" required></div>
                    <div class="mt-3"><label class="form-label">Exchange Rate (vs Base)</label><input
                            class="form-control" name="exchange_rate" type="number" min="0.000001" step="0.000001"
                            required></div>
                </div>
                <div class="modal-footer"><button class="btn btn-light" type="button"
                        data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Currency</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="currencyHistoryModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Exchange Rate History <span id="historyCurrencyCode"
                            class="fw-normal text-secondary"></span></h5><button class="btn-close" type="button"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th class="text-end">Rate (vs Base)</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody id="currencyHistoryBody">
                                <tr>
                                    <td colspan="3" class="text-center text-secondary py-3">Loading...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3"><label class="form-label">Add Historical Rate</label>
                        <div class="row g-2">
                            <div class="col-md-4"><input class="form-control" type="date" id="newHistoryDate"
                                    required></div>
                            <div class="col-md-4"><input class="form-control" type="number" step="0.000001"
                                    min="0.000001" id="newHistoryRate" placeholder="Rate" required></div>
                            <div class="col-md-4"><button class="btn btn-primary w-100" id="btnAddHistoryRate"><i
                                        class="bi bi-plus-lg me-1"></i>Add Rate</button></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endsection
    @push('scripts')
        <script>
            (async () => {
                const M = MyLedger,
                    profile = document.querySelector('#profileForm'),
                    settings = document.querySelector('#settingsForm'),
                    password = document.querySelector('#passwordForm'),
                    audit = document.querySelector('#auditList'),
                    currencies = document.querySelector('#currencyList'),
                    currForm = document.querySelector('#currencyForm'),
                    baseSelect = document.querySelector('#baseCurrencySelect');
                let cItems = [];
                async function loadCurrencies() {
                    try {
                        cItems = await M.request('/ajax/currencies');
                        M.fillSelect(baseSelect, cItems, {
                            label: c => `${c.code} - ${c.name} (${c.symbol})`
                        });
                        if (settings.dataset.baseId) baseSelect.value = settings.dataset.baseId;
                        currencies.innerHTML = cItems.length ? cItems.map(c =>
                                `<tr><td><span class="fw-bold">${M.esc(c.code)}</span></td><td>${M.esc(c.name)}</td><td>${M.esc(c.symbol)}</td><td class="text-end fw-semibold">${c.exchange_rate}</td><td class="text-end"><button class="btn btn-sm btn-outline-secondary edit-rate" data-id="${c.id}"><i class="bi bi-pencil"></i> Edit</button> <button class="btn btn-sm btn-outline-info view-history" data-id="${c.id}" data-code="${M.esc(c.code)}"><i class="bi bi-clock-history"></i></button></td></tr>`
                                ).join('') :
                            '<tr><td colspan="5" class="text-secondary small">No active currencies.</td></tr>'
                    } catch (e) {
                        M.toast(e.message, 'danger')
                    }
                }
                try {
                    const [d, a] = await Promise.all([M.request('/ajax/settings'), M.request(
                        '/ajax/audit-log?per_page=10')]);
                    settings.theme.value = d.theme || 'system';
                    settings.locale.value = d.locale || 'en';
                    settings.daily_reminder_time.value = d.daily_reminder_time ? String(d.daily_reminder_time).slice(0,
                        5) : '';
                    if (d.base_currency_id) {
                        settings.dataset.baseId = d.base_currency_id;
                        baseSelect.value = d.base_currency_id;
                    }
                    audit.innerHTML = a.data.length ? a.data.map(x =>
                        `<div class="d-flex justify-content-between gap-3 py-2 border-bottom"><div><div class="small fw-semibold text-capitalize">${M.esc(x.action)}</div><div class="small text-secondary">${M.esc(x.new_values?.reference_no||x.auditable_type.split('\\').pop())}</div></div><div class="small text-secondary text-nowrap">${M.dateTime(x.created_at)}</div></div>`
                        ).join('') : '<div class="small text-secondary">No accounting changes yet.</div>';
                    loadCurrencies();
                } catch (e) {
                    M.toast(e.message, 'danger')
                }
                document.querySelector('#btnNewCurrency').onclick = () => {
                    currForm.reset();
                    currForm.id.value = '';
                    currForm.code.readOnly = false;
                    currForm.name.readOnly = false;
                    currForm.symbol.readOnly = false;
                    document.querySelector('#currencyModalTitle').textContent = 'Add New Currency';
                    M.modal('currencyModal');
                };
                currencies.onclick = e => {
                    const b = e.target.closest('.edit-rate');
                    if (!b) return;
                    const c = cItems.find(x => String(x.id) === b.dataset.id);
                    currForm.reset();
                    currForm.id.value = c.id;
                    currForm.code.value = c.code;
                    currForm.name.value = c.name;
                    currForm.symbol.value = c.symbol;
                    currForm.exchange_rate.value = c.exchange_rate;
                    currForm.code.readOnly = true;
                    currForm.name.readOnly = true;
                    currForm.symbol.readOnly = true;
                    document.querySelector('#currencyModalTitle').textContent = 'Edit Exchange Rate';
                    M.modal('currencyModal')
                };
                currForm.onsubmit = async e => {
                    e.preventDefault();
                    const id = currForm.id.value,
                        d = M.payload(currForm);
                    delete d.id;
                    try {
                        if (id) {
                            await M.request(`/ajax/currencies/${id}`, {
                                method: 'PATCH',
                                body: JSON.stringify({
                                    exchange_rate: d.exchange_rate
                                })
                            });
                            M.toast('Exchange rate updated.');
                        } else {
                            await M.request('/ajax/currencies', {
                                method: 'POST',
                                body: JSON.stringify(d)
                            });
                            M.toast('New currency created.');
                        }
                        M.modal('currencyModal', 'hide');
                        loadCurrencies();
                    } catch (err) {
                        M.errors(currForm, err)
                    }
                };
                profile.onsubmit = async e => {
                    e.preventDefault();
                    try {
                        await M.request('/ajax/profile', {
                            method: 'PATCH',
                            body: JSON.stringify(M.payload(profile))
                        });
                        M.toast('Profile updated.')
                    } catch (err) {
                        M.errors(profile, err)
                    }
                };
                settings.onsubmit = async e => {
                    e.preventDefault();
                    try {
                        const d = await M.request('/ajax/settings', {
                            method: 'PATCH',
                            body: JSON.stringify(M.payload(settings))
                        });
                        M.toast('Preferences saved.');
                        document.documentElement.dataset.themePreference = d.theme || 'system';
                        const dark = d.theme === 'dark' || (d.theme === 'system' && matchMedia(
                            '(prefers-color-scheme: dark)').matches);
                        document.documentElement.setAttribute('data-bs-theme', dark ? 'dark' : 'light')
                    } catch (err) {
                        M.errors(settings, err)
                    }
                };
                password.onsubmit = async e => {
                    e.preventDefault();
                    try {
                        await M.request('/ajax/profile/password', {
                            method: 'PUT',
                            body: JSON.stringify(M.payload(password))
                        });
                        password.reset();
                        M.toast('Password updated.')
                    } catch (err) {
                        M.errors(password, err)
                    }
                };

                // Currency History Modal handlers
                const historyModal = document.querySelector('#currencyHistoryModal');
                const historyBody = document.querySelector('#currencyHistoryBody');
                const historyCurrencyCode = document.querySelector('#historyCurrencyCode');
                const newHistoryDate = document.querySelector('#newHistoryDate');
                const newHistoryRate = document.querySelector('#newHistoryRate');
                const btnAddHistoryRate = document.querySelector('#btnAddHistoryRate');
                let currentHistoryCurrencyId = null;

                async function loadCurrencyHistory(currencyId) {
                    try {
                        const history = await M.request(`/ajax/currencies/${currencyId}/history`);
                        historyBody.innerHTML = history.length ? history.map(h => `
                <tr>
                    <td>${M.date(h.rate_date)}</td>
                    <td class="text-end fw-semibold">${Number(h.rate).toLocaleString(undefined, {minimumFractionDigits: 4, maximumFractionDigits: 8})}</td>
                    <td class="text-end"><button class="btn btn-sm btn-outline-danger delete-history" data-id="${h.id}"><i class="bi bi-trash"></i></button></td>
                </tr>
            `).join('') : '<tr><td colspan="3" class="text-center text-secondary py-3">No historical rates recorded.</td></tr>';
                    } catch (e) {
                        historyBody.innerHTML =
                            `<tr><td colspan="3" class="text-center text-danger py-3">${M.esc(e.message)}</td></tr>`;
                    }
                }

                currencies.onclick = async e => {
                    const editBtn = e.target.closest('.edit-rate');
                    const historyBtn = e.target.closest('.view-history');
                    if (historyBtn) {
                        currentHistoryCurrencyId = historyBtn.dataset.id;
                        historyCurrencyCode.textContent = `(${historyBtn.dataset.code})`;
                        await loadCurrencyHistory(currentHistoryCurrencyId);
                        M.modal('currencyHistoryModal');
                        return;
                    }
                    if (!editBtn) return;
                    const c = cItems.find(x => String(x.id) === editBtn.dataset.id);
                    currForm.reset();
                    currForm.id.value = c.id;
                    currForm.code.value = c.code;
                    currForm.name.value = c.name;
                    currForm.symbol.value = c.symbol;
                    currForm.exchange_rate.value = c.exchange_rate;
                    currForm.code.readOnly = true;
                    currForm.name.readOnly = true;
                    currForm.symbol.readOnly = true;
                    document.querySelector('#currencyModalTitle').textContent = 'Edit Exchange Rate';
                    M.modal('currencyModal');
                };

                historyBody.onclick = async e => {
                    const delBtn = e.target.closest('.delete-history');
                    if (!delBtn) return;
                    if (!confirm('Delete this historical rate?')) return;
                    try {
                        await M.request(
                            `/ajax/currencies/${currentHistoryCurrencyId}/history/${delBtn.dataset.id}`, {
                                method: 'DELETE'
                            });
                        M.toast('Historical rate deleted.');
                        loadCurrencyHistory(currentHistoryCurrencyId);
                    } catch (err) {
                        M.toast(err.message, 'danger');
                    }
                };

                btnAddHistoryRate.onclick = async () => {
                    const date = newHistoryDate.value;
                    const rate = newHistoryRate.value;
                    if (!date || !rate) {
                        M.toast('Please fill in both date and rate.', 'warning');
                        return;
                    }
                    try {
                        await M.request(`/ajax/currencies/${currentHistoryCurrencyId}/history`, {
                            method: 'POST',
                            body: JSON.stringify({
                                rate_date: date,
                                rate: parseFloat(rate)
                            })
                        });
                        M.toast('Historical rate added.');
                        newHistoryDate.value = '';
                        newHistoryRate.value = '';
                        loadCurrencyHistory(currentHistoryCurrencyId);
                    } catch (err) {
                        M.errors(document.querySelector('#currencyHistoryModal form') || {}, err);
                    }
                };

                historyModal.addEventListener('hidden.bs.modal', () => {
                    currentHistoryCurrencyId = null;
                    historyCurrencyCode.textContent = '';
                    newHistoryDate.value = '';
                    newHistoryRate.value = '';
                });
            })();
        </script>
    @endpush
