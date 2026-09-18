@extends('layouts.app')
@section('title', 'Loans')
@section('page-title', 'Loans')
@section('page-subtitle', 'Track money you lend and money you borrow.')
@section('content')
    <div class="row g-3 mb-4" data-base-currency-id="{{ auth()->user()->settings?->base_currency_id }}">
        <div class="col-md-6">
            <div class="surface-card stat-card">
                <div class="stat-icon"><i class="bi bi-arrow-down-left"></i></div>
                <div class="stat-label">You will receive</div>
                <div class="stat-value text-success" id="loanGivenTotal">{{ auth()->user()->settings->currency->symbol }} 0
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="surface-card stat-card">
                <div class="stat-icon"><i class="bi bi-arrow-up-right"></i></div>
                <div class="stat-label">You have to pay</div>
                <div class="stat-value text-danger" id="loanTakenTotal">{{ auth()->user()->settings->currency->symbol }} 0
                </div>
            </div>
        </div>
    </div>

    <div class="page-actions justify-content-between">
        <select class="form-select w-auto" id="loanDirectionFilter">
            <option value="">All loans</option>
            <option value="given">Loans given</option>
            <option value="taken">Loans taken</option>
        </select>
        <button class="btn btn-primary" id="newLoan">
            <i class="bi bi-plus-lg me-2"></i>New loan
        </button>
    </div>

    <div class="surface-card table-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Person</th>
                        <th>Direction</th>
                        <th>Principal</th>
                        <th>Outstanding</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="loanRows"></tbody>
            </table>
        </div>
    </div>

    <!-- Modal: Loan Form -->
    <div class="modal fade" id="loanModal">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="loanForm">
                <div class="modal-header">
                    <h5 class="modal-title">New loan</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Direction</label>
                            <select class="form-select" name="direction">
                                <option value="given">I am giving money</option>
                                <option value="taken">I am taking money</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Person</label>
                            <select class="form-select" name="person_id" required></select>
                        </div>
                    </div>
                    <div class="mt-3 create-only">
                        <label class="form-label">Money account</label>
                        <select class="form-select" name="account_id"></select>
                    </div>
                    <div class="mt-3 create-only">
                        <label class="form-label">Currency</label>
                        <select class="form-select" name="currency_id" id="loanCurrencySelect"></select>
                    </div>
                    <div class="row g-3 mt-0">
                        <div class="col-md-6">
                            <label class="form-label">Principal</label>
                            <input class="form-control" name="principal" id="loanPrincipalInput" type="number"
                                min="0.01" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Start date</label>
                            <input class="form-control" name="start_date" type="date" value="{{ now()->toDateString() }}"
                                required>
                        </div>
                    </div>
                    <div id="loanRateNotice"></div>
                    <div class="row g-3 mt-0">
                        <div class="col-md-6">
                            <label class="form-label">Due date</label>
                            <input class="form-control" name="due_date" type="date">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Interest rate %</label>
                            <input class="form-control" name="interest_rate" type="number" min="0" step="0.01"
                                value="0">
                        </div>
                    </div>
                    <div class="row g-3 mt-0">
                        <div class="col-md-6">
                            <label class="form-label">Interest type</label>
                            <select class="form-select" name="interest_type">
                                <option value="none">None</option>
                                <option value="simple">Simple</option>
                                <option value="fixed">Fixed</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Title</label>
                            <input class="form-control" name="title">
                        </div>
                    </div>
                    <div class="mt-3 edit-only d-none">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status">
                            <option value="active">Active</option>
                            <option value="paid">Paid</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" id="saveLoanBtn">Save loan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Record Repayment -->
    <div class="modal fade" id="repayModal">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="repayForm">
                <div class="modal-header">
                    <h5 class="modal-title">Record repayment</h5>
                    <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="loan_id">
                    <div class="mb-3">
                        <label class="form-label">Account</label>
                        <select class="form-select" name="account_id" required></select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Principal</label>
                            <input class="form-control" name="principal_amount" type="number" min="0.01"
                                step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Interest</label>
                            <input class="form-control" name="interest_amount" type="number" min="0"
                                step="0.01" value="0">
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Paid on</label>
                        <input class="form-control" name="paid_at" type="date" value="{{ now()->toDateString() }}"
                            required>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Description</label>
                        <input class="form-control" name="description">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary">Save repayment</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Printable / Shareable Loan Receipt -->
    <div class="modal fade" id="loanReceiptModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-receipt me-2 text-primary"></i>Loan Receipt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="border rounded-4 p-4 bg-light text-center" id="loanReceiptCardContent">
                        <div class="mb-2 text-primary fs-3"><i class="bi bi-file-earmark-text-fill"></i></div>
                        <h4 class="h5 fw-bold mb-1" id="receiptLoanTitle">Loan Receipt</h4>
                        <div class="text-secondary small mb-3" id="receiptLoanPerson">Person Name</div>
                        <div class="h3 fw-bold text-success mb-3" id="receiptLoanAmount">
                            {{ auth()->user()->settings->currency->symbol }} 0</div>
                        <div class="border-top border-bottom py-3 text-start small">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-secondary">Direction:</span>
                                <strong id="receiptLoanDirection">Given</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-secondary">Outstanding Principal:</span>
                                <strong id="receiptLoanOutstanding">{{ auth()->user()->settings->currency->symbol }}
                                    0</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-secondary">Start Date:</span>
                                <span id="receiptLoanStartDate">Sep 17, 2026</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-secondary">Due Date:</span>
                                <span id="receiptLoanDueDate">Oct 17, 2026</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-secondary">Status:</span>
                                <span class="badge bg-success" id="receiptLoanStatus">ACTIVE</span>
                            </div>
                        </div>
                        <div class="small text-secondary mt-3">Verified by MyLedger Personal Finance</div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-success" id="btnCopyLoanWhatsApp">
                        <i class="bi bi-whatsapp me-1"></i>Copy Text
                    </button>
                    <div>
                        <button type="button" class="btn btn-light me-1" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="bi bi-printer me-1"></i>Print / PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const M = MyLedger,
                body = document.querySelector('#loanRows'),
                form = document.querySelector('#loanForm'),
                repay = document.querySelector('#repayForm');
            let loans = [],
                accounts = [],
                people = [];

            function updateLoanRateNotice() {
                const amt = form.principal.value;
                const currId = form.currency_id.value;
                M.renderCurrencyRateNotice(document.querySelector('#loanRateNotice'), amt, currId);
            }

            async function refs() {
                [accounts, people] = await Promise.all([
                    M.request('/ajax/accounts'),
                    M.request('/ajax/people')
                ]);
                currencies = await M.getCurrencies();
                M.fillSelect(form.account_id, accounts);
                M.fillSelect(repay.account_id, accounts);
                M.fillSelect(form.person_id, people, {
                    placeholder: 'Select person'
                });
                M.fillSelect(form.currency_id, currencies, {
                    label: c => `${c.code} (${c.symbol})`,
                    placeholder: 'Select currency...',
                    selected: getBaseCurrencyId()
                });
                form.account_id.onchange = () => {
                    const acc = accounts.find(a => String(a.id) === String(form.account_id.value));
                    if (acc && !form.currency_id.value) {
                        form.currency_id.value = acc.currency_id;
                    }
                };
                updateLoanRateNotice();
            }

            function getBaseCurrencyId() {
                return document.querySelector('[data-base-currency-id]')?.dataset?.baseCurrencyId || null;
            }

            form.principal.oninput = updateLoanRateNotice;
            form.currency_id.onchange = updateLoanRateNotice;

            async function load() {
                const d = document.querySelector('#loanDirectionFilter').value;
                try {
                    loans = await M.request('/ajax/loans' + (d ? '?direction=' + d : ''));
                    document.querySelector('#loanGivenTotal').textContent = M.money(loans.filter(x => M.typeValue(x
                        .direction) === 'given' && x.status === 'active').reduce((s, x) => s + Number(x
                        .outstanding_principal), 0));
                    document.querySelector('#loanTakenTotal').textContent = M.money(loans.filter(x => M.typeValue(x
                        .direction) === 'taken' && x.status === 'active').reduce((s, x) => s + Number(x
                        .outstanding_principal), 0));

                    body.innerHTML = loans.length ? loans.map(l => `
                <tr>
                    <td>
                        <div class="fw-semibold">${M.esc(l.person?.name || '')}</div>
                        <div class="small text-secondary">${M.esc(l.title || '')}</div>
                    </td>
                    <td>
                        <span class="badge ${M.typeValue(l.direction) === 'given' ? 'text-bg-success' : 'text-bg-warning'}">
                            ${M.typeValue(l.direction) === 'given' ? 'Given' : 'Taken'}
                        </span>
                    </td>
                    <td>${M.money(l.principal, l.currency.symbol)}</td>
                    <td class="fw-semibold">${M.money(l.outstanding_principal, l.currency.symbol)}</td>
                    <td>${M.date(l.due_date)}</td>
                    <td>
                        <span class="badge ${l.status === 'active' ? 'text-bg-primary' : l.status === 'paid' ? 'text-bg-success' : 'text-bg-secondary'}">
                            ${M.esc(l.status)}
                        </span>
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-success share-loan" data-id="${l.id}" title="Copy Text / Share WhatsApp">
                                <i class="bi bi-whatsapp"></i>
                            </button>
                            <button class="btn btn-outline-secondary edit-loan" data-id="${l.id}" title="Edit Loan">
                                <i class="bi bi-pencil"></i>
                            </button>
                            ${l.status === 'active' ? `<button class="btn btn-outline-primary repay" data-id="${l.id}">Repay</button>` : ''}
                        </div>
                    </td>
                </tr>
            `).join('') :
                        '<tr><td colspan="7"><div class="empty-state"><i class="bi bi-cash-stack"></i>No loans found.</div></td></tr>';
                } catch (e) {
                    M.toast(e.message, 'danger');
                }
            }

            function showLoanReceiptModal(l, repayment = null) {
                const personName = l.person?.name || 'Person';
                const isGiven = M.typeValue(l.direction) === 'given';
                const dirText = isGiven ? 'Loan Given' : 'Loan Taken';

                if (repayment) {
                    document.querySelector('#receiptLoanTitle').textContent = 'Loan Repayment Receipt';
                    document.querySelector('#receiptLoanPerson').textContent = personName + (l.title ? ` (${l.title})` :
                        '');
                    document.querySelector('#receiptLoanAmount').textContent = M.money(repayment.amount, l.currency
                        .symbol);
                    document.querySelector('#receiptLoanDirection').textContent = dirText;
                    document.querySelector('#receiptLoanOutstanding').textContent = M.money(l.outstanding_principal, l
                        .currency.symbol);
                    document.querySelector('#receiptLoanStartDate').textContent = M.date(repayment.date);
                    document.querySelector('#receiptLoanDueDate').textContent = l.due_date ? M.date(l.due_date) : 'N/A';
                    document.querySelector('#receiptLoanStatus').textContent = 'REPAID';

                    const rawText =
                        `🧾 *Loan Repayment Receipt*\n📌 *Person:* ${personName}\n🏷️ *Loan:* ${l.title || dirText}\n↔️ *Direction:* ${dirText}\n💰 *Repayment Amount:* ${M.money(repayment.amount, l.currency.symbol)}\n📉 *Remaining Balance:* ${M.money(l.outstanding_principal, l.currency.symbol)}\n📅 *Payment Date:* ${M.date(repayment.date)}\n✅ *Status:* PAID\n\n-- Verified by MyLedger Personal Finance`;

                    document.querySelector('#btnCopyLoanWhatsApp').onclick = () => {
                        navigator.clipboard.writeText(rawText).then(() => {
                            M.toast('Loan repayment receipt copied to clipboard! Ready to paste into WhatsApp.',
                                'success');
                        }).catch(() => {
                            M.toast('Copy failed. Please copy manually.', 'warning');
                        });
                    };
                } else {
                    document.querySelector('#receiptLoanTitle').textContent = l.title || dirText;
                    document.querySelector('#receiptLoanPerson').textContent = personName;
                    document.querySelector('#receiptLoanAmount').textContent = M.money(l.principal, l.currency.symbol);
                    document.querySelector('#receiptLoanDirection').textContent = dirText;
                    document.querySelector('#receiptLoanOutstanding').textContent = M.money(l.outstanding_principal, l
                        .currency.symbol);
                    document.querySelector('#receiptLoanStartDate').textContent = M.date(l.start_date);
                    document.querySelector('#receiptLoanDueDate').textContent = l.due_date ? M.date(l.due_date) : 'N/A';
                    document.querySelector('#receiptLoanStatus').textContent = String(l.status).toUpperCase();

                    const rawText =
                        `🧾 *Loan Statement / Receipt*\n📌 *Person:* ${personName}\n🏷️ *Title:* ${l.title || 'Loan'}\n↔️ *Type:* ${dirText}\n💰 *Principal:* ${M.money(l.principal, l.currency.symbol)}\n📉 *Outstanding:* ${M.money(l.outstanding_principal, l.currency.symbol)}\n📅 *Start Date:* ${M.date(l.start_date)}\n📆 *Due Date:* ${l.due_date ? M.date(l.due_date) : 'N/A'}\n✅ *Status:* ${String(l.status).toUpperCase()}\n\n-- Verified by MyLedger Personal Finance`;

                    document.querySelector('#btnCopyLoanWhatsApp').onclick = () => {
                        navigator.clipboard.writeText(rawText).then(() => {
                            M.toast('Loan text copied to clipboard! Ready to paste into WhatsApp.',
                                'success');
                        }).catch(() => {
                            M.toast('Copy failed. Please copy manually.', 'warning');
                        });
                    };
                }

                M.modal('loanReceiptModal');
            }

            document.querySelector('#loanDirectionFilter').onchange = load;

            document.querySelector('#newLoan').onclick = () => {
                form.reset();
                form.id.value = '';
                form.start_date.value = '{{ now()->toDateString() }}';
                form.querySelector('.modal-title').textContent = 'New loan';
                document.querySelectorAll('.create-only').forEach(x => x.classList.remove('d-none'));
                document.querySelectorAll('.edit-only').forEach(x => x.classList.add('d-none'));
                form.direction.disabled = false;
                form.person_id.disabled = false;
                form.principal.disabled = false;
                form.start_date.disabled = false;
                form.account_id.required = true;
                refs().then(() => M.modal('loanModal'));
            };

            body.onclick = e => {
                const editBtn = e.target.closest('.edit-loan'),
                    repayBtn = e.target.closest('.repay'),
                    shareBtn = e.target.closest('.share-loan');

                if (shareBtn) {
                    const l = loans.find(x => String(x.id) === shareBtn.dataset.id);
                    if (l) showLoanReceiptModal(l);
                    return;
                }

                if (editBtn) {
                    const l = loans.find(x => String(x.id) === editBtn.dataset.id);
                    form.reset();
                    form.id.value = l.id;
                    form.querySelector('.modal-title').textContent = 'Edit loan details';
                    document.querySelectorAll('.create-only').forEach(x => x.classList.add('d-none'));
                    document.querySelectorAll('.edit-only').forEach(x => x.classList.remove('d-none'));
                    form.direction.disabled = false;
                    form.person_id.disabled = false;
                    form.principal.disabled = false;
                    form.start_date.disabled = false;
                    form.account_id.required = false;
                    refs().then(() => {
                        form.direction.value = M.typeValue(l.direction);
                        form.person_id.value = l.person_id;
                        form.principal.value = l.principal;
                        form.start_date.value = l.start_date ? String(l.start_date).slice(0, 10) : '';
                        form.due_date.value = l.due_date ? String(l.due_date).slice(0, 10) : '';
                        form.interest_rate.value = l.interest_rate || 0;
                        form.interest_type.value = l.interest_type || 'none';
                        form.title.value = l.title || '';
                        form.status.value = l.status;
                        form.notes.value = l.notes || '';
                        M.modal('loanModal');
                    });
                    return;
                }

                if (repayBtn) {
                    const loan = loans.find(x => String(x.id) === repayBtn.dataset.id);
                    repay.reset();
                    repay.loan_id.value = loan.id;
                    repay.principal_amount.value = loan.outstanding_principal;
                    repay.paid_at.value = '{{ now()->toDateString() }}';
                    refs().then(() => M.modal('repayModal'));
                }
            };

            form.onsubmit = async e => {
                e.preventDefault();
                const id = form.id.value,
                    d = M.payload(form);
                try {
                    if (id) {
                        delete d.id;
                        delete d.account_id;
                        await M.request(`/ajax/loans/${id}`, {
                            method: 'PATCH',
                            body: JSON.stringify(d)
                        });
                        M.toast('Loan details updated.');
                    } else {
                        await M.request('/ajax/loans', {
                            method: 'POST',
                            body: JSON.stringify(d)
                        });
                        M.toast('Loan recorded.');
                    }
                    M.modal('loanModal', 'hide');
                    load();
                } catch (err) {
                    M.errors(form, err);
                }
            };

            repay.onsubmit = async e => {
                e.preventDefault();
                const d = M.payload(repay),
                    id = d.loan_id;
                delete d.loan_id;
                try {
                    await M.request(`/ajax/loans/${id}/repay`, {
                        method: 'POST',
                        body: JSON.stringify(d)
                    });
                    M.modal('repayModal', 'hide');
                    M.toast('Repayment recorded.');
                    await load();
                    const updatedLoan = loans.find(x => String(x.id) === String(id));
                    if (updatedLoan) {
                        showLoanReceiptModal(updatedLoan, {
                            amount: d.principal_amount,
                            date: d.paid_at
                        });
                    }
                } catch (err) {
                    M.errors(repay, err);
                }
            };

            Promise.all([refs(), load()]);
        })();
    </script>
@endpush
