@extends('layouts.app')
@section('title', 'Committees')
@section('page-title', 'Committees (Bisi / ROSCA)')
@section('page-subtitle', 'Manage rotating savings pools, member turn schedules, contributions, and pot payouts.')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div></div>
    <button class="btn btn-primary" id="btnNewCommittee">
        <i class="bi bi-plus-lg me-2"></i>New Committee
    </button>
</div>

<!-- KPI Summary Cards -->
<div class="row g-3 mb-4" id="statsContainer">
    <div class="col-sm-6 col-xl-3">
        <div class="surface-card p-3 d-flex align-items-center gap-3">
            <div class="rounded-circle p-3 text-primary bg-primary-subtle fs-4 d-flex align-items-center justify-content-center" style="width:52px;height:52px;">
                <i class="bi bi-diagram-3-fill"></i>
            </div>
            <div>
                <div class="text-secondary small fw-medium">Active Committees</div>
                <div class="h4 mb-0 fw-bold" id="statActiveCount">0</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="surface-card p-3 d-flex align-items-center gap-3">
            <div class="rounded-circle p-3 text-success bg-success-subtle fs-4 d-flex align-items-center justify-content-center" style="width:52px;height:52px;">
                <i class="bi bi-cash-stack"></i>
            </div>
            <div>
                <div class="text-secondary small fw-medium">Monthly Contribution</div>
                <div class="h4 mb-0 fw-bold" id="statMonthlyContribution">Rs 0</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="surface-card p-3 d-flex align-items-center gap-3">
            <div class="rounded-circle p-3 text-info bg-info-subtle fs-4 d-flex align-items-center justify-content-center" style="width:52px;height:52px;">
                <i class="bi bi-wallet2"></i>
            </div>
            <div>
                <div class="text-secondary small fw-medium">Total Pool Value</div>
                <div class="h4 mb-0 fw-bold" id="statTotalPool">Rs 0</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="surface-card p-3 d-flex align-items-center gap-3">
            <div class="rounded-circle p-3 text-warning bg-warning-subtle fs-4 d-flex align-items-center justify-content-center" style="width:52px;height:52px;">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <div class="text-secondary small fw-medium">Pending Payments</div>
                <div class="h4 mb-0 fw-bold" id="statPendingPayments">0</div>
            </div>
        </div>
    </div>
</div>

<!-- Committees Cards Container -->
<div class="row g-3" id="committeesGrid">
    <div class="col-12">
        <div class="surface-card p-5 text-center text-secondary">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2">Loading committees...</div>
        </div>
    </div>
</div>

<!-- Modal: New Committee -->
<div class="modal fade" id="committeeModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form class="modal-content" id="committeeForm">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-diagram-3-fill me-2"></i>Create New Committee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Committee Name <span class="text-danger">*</span></label>
                        <input class="form-control" name="name" placeholder="e.g. Office Monthly Committee 2026" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Contribution / Member <span class="text-danger">*</span></label>
                        <input class="form-control" name="contribution_amount" type="number" min="1" step="0.01" placeholder="5000" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Frequency <span class="text-danger">*</span></label>
                        <select class="form-select" name="frequency" required>
                            <option value="monthly" selected>Monthly</option>
                            <option value="weekly">Weekly</option>
                            <option value="biweekly">Bi-weekly</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input class="form-control" name="start_date" type="date" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Your Role <span class="text-danger">*</span></label>
                        <select class="form-select" name="my_role" required>
                            <option value="manager" selected>Manager / Organizer</option>
                            <option value="member">Participant / Member</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Link Your Person Record</label>
                        <select class="form-select" name="my_person_id">
                            <option value="">Select yourself (Optional)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0 fw-bold">Member Slots (Minimum 2) <span class="text-danger">*</span></label>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddMemberSlot">
                            <i class="bi bi-plus-lg me-1"></i>Add Member Slot
                        </button>
                    </div>
                    <div class="table-responsive border rounded">
                        <table class="table table-sm align-middle mb-0" id="membersTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Link Contact / Name</th>
                                    <th style="width: 140px;">Turn Round #</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="membersTableBody">
                                <!-- Dynamic rows -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea class="form-control" name="notes" rows="2" placeholder="Any committee rules or notes..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Create Committee</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Committee Detail & Rounds Matrix -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title mb-0" id="detailModalTitle">Committee Details</h5>
                    <div class="small text-secondary" id="detailModalSubtitle"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="bg-light p-3 border-bottom d-flex flex-wrap gap-4 align-items-center justify-content-between">
                    <div class="d-flex gap-4">
                        <div>
                            <div class="text-secondary small">Contribution / Member</div>
                            <div class="fw-bold" id="detailContribution">Rs 0</div>
                        </div>
                        <div>
                            <div class="text-secondary small">Total Members</div>
                            <div class="fw-bold" id="detailMembersCount">0</div>
                        </div>
                        <div>
                            <div class="text-secondary small">Total Pool per Round</div>
                            <div class="fw-bold text-success" id="detailPoolAmount">Rs 0</div>
                        </div>
                        <div>
                            <div class="text-secondary small">Frequency</div>
                            <div class="fw-bold text-capitalize" id="detailFrequency">Monthly</div>
                        </div>
                    </div>
                    <div style="min-width: 200px;">
                        <div class="d-flex justify-content-between small text-secondary mb-1">
                            <span>Round Progress</span>
                            <span id="detailProgressPercent">0%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" id="detailProgressBar" style="width: 0%;"></div>
                        </div>
                    </div>
                </div>

                <div class="p-3">
                    <ul class="nav nav-tabs mb-3" id="detailTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabRounds" type="button">
                                <i class="bi bi-calendar-check me-1"></i>Rounds & Payouts Schedule
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabMembers" type="button">
                                <i class="bi bi-people me-1"></i>Members & Turns
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <!-- Tab 1: Rounds -->
                        <div class="tab-pane fade show active" id="tabRounds">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Round #</th>
                                            <th>Due Date</th>
                                            <th>Turn Recipient (Winner)</th>
                                            <th>Collection Status</th>
                                            <th>Payout Status</th>
                                            <th class="text-end">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody id="roundsTableBody">
                                        <!-- Dynamic rounds rows -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Tab 2: Members -->
                        <div class="tab-pane fade" id="tabMembers">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 60px;">Slot #</th>
                                            <th>Member Name</th>
                                            <th>Linked Contact</th>
                                            <th>Scheduled Payout Round</th>
                                        </tr>
                                    </thead>
                                    <tbody id="membersListBody">
                                        <!-- Dynamic members list -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Manage Round Member Payments -->
<div class="modal fade" id="roundPaymentsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roundPaymentsTitle">Record Round Contributions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info py-2 mb-3 d-flex align-items-center justify-content-between">
                    <div><i class="bi bi-info-circle me-2"></i>Select an account below if you wish to automatically create a ledger transaction for paid contributions.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Deposit Account (Optional Ledger Integration)</label>
                    <select class="form-select" id="paymentAccountId">
                        <option value="">Do not post ledger transaction (Record status only)</option>
                    </select>
                </div>
                <div class="table-responsive border rounded">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Member</th>
                                <th>Contribution Amount</th>
                                <th>Payment Date</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody id="roundPaymentsTableBody">
                            <!-- Dynamic payments -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Disburse Payout -->
<div class="modal fade" id="payoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" id="payoutForm">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-award-fill me-2 text-warning"></i>Disburse Round Payout</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="round_id">
                <div class="mb-3">
                    <label class="form-label">Turn Recipient (Winning Member) <span class="text-danger">*</span></label>
                    <select class="form-select" name="winner_member_id" required></select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Payout Amount</label>
                    <input class="form-control" id="payoutAmountDisplay" readonly>
                </div>
                <div class="mb-3">
                    <label class="form-label">Payout Date <span class="text-danger">*</span></label>
                    <input class="form-control" name="payout_date" type="date" value="{{ now()->toDateString() }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Payment Account (Optional Ledger Integration)</label>
                    <select class="form-select" name="account_id">
                        <option value="">Do not post ledger transaction</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Notes (Optional)</label>
                    <textarea class="form-control" name="notes" rows="2" placeholder="e.g. Transferred via bank transfer"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle me-1"></i>Confirm & Mark Paid</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Printable / Shareable Receipt -->
<div class="modal fade" id="receiptModal" tabindex="-1" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-receipt me-2 text-primary"></i>Payment Receipt</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="border rounded-4 p-4 bg-light text-center" id="receiptCardContent">
                    <div class="mb-2 text-primary fs-3"><i class="bi bi-check-circle-fill"></i></div>
                    <h4 class="h5 fw-bold mb-1" id="receiptTitle">Committee Payment Receipt</h4>
                    <div class="text-secondary small mb-3" id="receiptCommitteeName">Committee Name</div>
                    <div class="h3 fw-bold text-success mb-3" id="receiptAmount">Rs 0</div>
                    <div class="border-top border-bottom py-3 text-start small">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary">Member:</span>
                            <strong id="receiptMember">Ali</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary">Type / Round:</span>
                            <strong id="receiptType">Round #1 Contribution</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-secondary">Payment Date:</span>
                            <span id="receiptDate">Sep 16, 2026</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-secondary">Status:</span>
                            <span class="badge bg-success" id="receiptStatus">PAID</span>
                        </div>
                    </div>
                    <div class="small text-secondary mt-3">Verified by MyLedger Personal Finance</div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <button type="button" class="btn btn-outline-success" id="btnCopyWhatsApp">
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
    const M = window.MyLedger;
    let committees = [], people = [], accounts = [];
    let currentCommittee = null, currentRound = null;

    // Elements
    const grid = document.querySelector('#committeesGrid');
    const formCommittee = document.querySelector('#committeeForm');
    const membersTableBody = document.querySelector('#membersTableBody');
    const payoutForm = document.querySelector('#payoutForm');

    async function loadRefs() {
        try {
            const [pRes, aRes] = await Promise.all([
                M.request('/ajax/people'),
                M.request('/ajax/accounts')
            ]);
            people = pRes || [];
            accounts = aRes || [];

            // Fill self-person select
            M.fillSelect(formCommittee.my_person_id, people, { placeholder: 'Select yourself (Optional)' });
            M.fillSelect(document.querySelector('#paymentAccountId'), accounts, { placeholder: 'Select account for deposit...' });
            M.fillSelect(payoutForm.account_id, accounts, { placeholder: 'Select account for withdrawal...' });
        } catch (e) {
            console.error('Error loading references:', e);
        }
    }

    async function loadCommittees() {
        try {
            const res = await M.request('/ajax/committees');
            committees = res.committees || [];
            const stats = res.stats || {};

            // Render stats
            document.querySelector('#statActiveCount').textContent = stats.active_committees || 0;
            document.querySelector('#statMonthlyContribution').textContent = M.money(stats.total_monthly_contribution || 0);
            document.querySelector('#statTotalPool').textContent = M.money(stats.total_pool_value || 0);
            document.querySelector('#statPendingPayments').textContent = stats.pending_payments_count || 0;

            // Render cards
            if (!committees.length) {
                grid.innerHTML = `
                    <div class="col-12">
                        <div class="surface-card p-5 text-center">
                            <i class="bi bi-diagram-3 text-secondary display-4 d-block mb-3"></i>
                            <h5 class="fw-bold">No committees yet</h5>
                            <p class="text-secondary max-w-md mx-auto">Create a committee to start tracking rotating savings pools, member turns, and monthly contributions.</p>
                            <button class="btn btn-primary mt-2" onclick="document.querySelector('#btnNewCommittee').click()">
                                <i class="bi bi-plus-lg me-1"></i>Create First Committee
                            </button>
                        </div>
                    </div>
                `;
                return;
            }

            grid.innerHTML = committees.map(c => {
                const progress = c.progress_percent || 0;
                const statusBadge = c.status === 'completed'
                    ? '<span class="badge bg-success">Completed</span>'
                    : '<span class="badge bg-primary">Active</span>';
                const roleBadge = c.my_role === 'manager'
                    ? '<span class="badge bg-info text-dark ms-1">Manager</span>'
                    : '<span class="badge bg-secondary ms-1">Member</span>';

                return `
                    <div class="col-md-6 col-xl-4">
                        <div class="surface-card h-100 p-4 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h3 class="h6 fw-bold mb-0 text-truncate" title="${M.esc(c.name)}">${M.esc(c.name)}</h3>
                                    <div>${statusBadge}${roleBadge}</div>
                                </div>
                                <div class="text-secondary small mb-3">
                                    Started ${M.date(c.start_date)} · ${c.total_members} Member Slots
                                </div>

                                <div class="bg-light p-3 rounded mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-secondary small">Contribution / Slot</span>
                                        <span class="fw-bold text-primary">${M.money(c.contribution_amount)}</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-secondary small">Pot / Round (${c.frequency})</span>
                                        <span class="fw-bold text-success">${M.money(c.total_pool_amount)}</span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small text-secondary mb-1">
                                        <span>Rounds Progress</span>
                                        <span>${c.completed_rounds_count} / ${c.total_members} Rounds (${progress}%)</span>
                                    </div>
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" style="width: ${progress}%;"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 pt-2 border-top mt-3">
                                <button class="btn btn-sm btn-primary flex-fill btn-manage" data-id="${c.id}">
                                    <i class="bi bi-gear-fill me-1"></i>Manage Rounds
                                </button>
                                <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${c.id}" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        } catch (e) {
            M.toast(e.message, 'danger');
        }
    }

    // Dynamic Member Slots in Create Modal
    function renderMemberSlotRows(count = 5) {
        let html = '';
        for (let i = 1; i <= count; i++) {
            html += `
                <tr class="member-slot-row">
                    <td class="fw-bold text-secondary text-center slot-num">${i}</td>
                    <td>
                        <div class="input-group input-group-sm">
                            <select class="form-select member-person-select" name="members[${i-1}][person_id]">
                                <option value="">Select contact...</option>
                                ${people.map(p => `<option value="${p.id}">${M.esc(p.name)}</option>`).join('')}
                            </select>
                            <input class="form-control member-name-input" name="members[${i-1}][name]" placeholder="Or enter member name" value="Member #${i}">
                        </div>
                    </td>
                    <td>
                        <input class="form-control form-control-sm member-turn-input" type="number" min="1" max="100" name="members[${i-1}][payout_round_no]" value="${i}">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-slot"><i class="bi bi-x-circle-fill fs-6"></i></button>
                    </td>
                </tr>
            `;
        }
        membersTableBody.innerHTML = html;
        attachSlotRowEvents();
    }

    function attachSlotRowEvents() {
        membersTableBody.querySelectorAll('.member-person-select').forEach(select => {
            select.onchange = (e) => {
                const row = e.target.closest('tr');
                const text = e.target.options[e.target.selectedIndex]?.text;
                const nameInput = row.querySelector('.member-name-input');
                if (e.target.value && text) {
                    nameInput.value = text;
                }
            };
        });

        membersTableBody.querySelectorAll('.btn-remove-slot').forEach(btn => {
            btn.onclick = (e) => {
                const rows = membersTableBody.querySelectorAll('tr');
                if (rows.length <= 2) {
                    M.toast('A committee must have at least 2 members.', 'warning');
                    return;
                }
                e.target.closest('tr').remove();
                reindexSlots();
            };
        });
    }

    function reindexSlots() {
        const rows = membersTableBody.querySelectorAll('tr');
        rows.forEach((row, idx) => {
            const num = idx + 1;
            row.querySelector('.slot-num').textContent = num;
            row.querySelector('.member-person-select').name = `members[${idx}][person_id]`;
            row.querySelector('.member-name-input').name = `members[${idx}][name]`;
            row.querySelector('.member-turn-input').name = `members[${idx}][payout_round_no]`;
        });
    }

    document.querySelector('#btnAddMemberSlot').onclick = () => {
        const rows = membersTableBody.querySelectorAll('tr');
        const nextNum = rows.length + 1;
        const tr = document.createElement('tr');
        tr.className = 'member-slot-row';
        tr.innerHTML = `
            <td class="fw-bold text-secondary text-center slot-num">${nextNum}</td>
            <td>
                <div class="input-group input-group-sm">
                    <select class="form-select member-person-select" name="members[${nextNum-1}][person_id]">
                        <option value="">Select contact...</option>
                        ${people.map(p => `<option value="${p.id}">${M.esc(p.name)}</option>`).join('')}
                    </select>
                    <input class="form-control member-name-input" name="members[${nextNum-1}][name]" placeholder="Or enter member name" value="Member #${nextNum}">
                </div>
            </td>
            <td>
                <input class="form-control form-control-sm member-turn-input" type="number" min="1" max="100" name="members[${nextNum-1}][payout_round_no]" value="${nextNum}">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-link text-danger p-0 btn-remove-slot"><i class="bi bi-x-circle-fill fs-6"></i></button>
            </td>
        `;
        membersTableBody.appendChild(tr);
        attachSlotRowEvents();
    };

    // Open Create Committee Modal
    document.querySelector('#btnNewCommittee').onclick = () => {
        formCommittee.reset();
        renderMemberSlotRows(5);
        M.modal('committeeModal');
    };

    // Submit Create Committee
    formCommittee.onsubmit = async (e) => {
        e.preventDefault();
        const payload = M.payload(formCommittee);

        // Extract members array from member slot rows
        const members = [];
        membersTableBody.querySelectorAll('tr').forEach((row, idx) => {
            const personId = row.querySelector('.member-person-select')?.value || null;
            const name = row.querySelector('.member-name-input')?.value || '';
            const payoutRoundNo = row.querySelector('.member-turn-input')?.value || null;
            if (name.trim() || personId) {
                members.push({
                    person_id: personId ? parseInt(personId) : null,
                    name: name.trim() || `Member #${idx + 1}`,
                    payout_round_no: payoutRoundNo ? parseInt(payoutRoundNo) : (idx + 1)
                });
            }
        });

        payload.members = members;

        if (!payload.my_person_id) payload.my_person_id = null;
        if (!payload.currency_id) payload.currency_id = null;

        // Remove flattened input keys from payload
        Object.keys(payload).forEach(key => {
            if (key.startsWith('members[')) delete payload[key];
        });

        try {
            await M.request('/ajax/committees', {
                method: 'POST',
                body: JSON.stringify(payload)
            });
            M.modal('committeeModal', 'hide');
            M.toast('Committee created successfully!');
            loadCommittees();
        } catch (err) {
            M.errors(formCommittee, err);
        }
    };

    // Card Actions
    grid.onclick = async (e) => {
        const btnManage = e.target.closest('.btn-manage');
        const btnDelete = e.target.closest('.btn-delete');

        if (btnDelete) {
            if (confirm('Are you sure you want to delete this committee? All round records will be removed.')) {
                try {
                    await M.request(`/ajax/committees/${btnDelete.dataset.id}`, { method: 'DELETE' });
                    M.toast('Committee deleted.');
                    loadCommittees();
                } catch (err) {
                    M.toast(err.message, 'danger');
                }
            }
            return;
        }

        if (btnManage) {
            openDetailModal(btnManage.dataset.id);
        }
    };

    // Open Detail Modal
    async function openDetailModal(id) {
        try {
            currentCommittee = await M.request(`/ajax/committees/${id}`);

            document.querySelector('#detailModalTitle').textContent = currentCommittee.name;
            document.querySelector('#detailModalSubtitle').textContent = `Started ${M.date(currentCommittee.start_date)} · Status: ${currentCommittee.status.toUpperCase()}`;

            document.querySelector('#detailContribution').textContent = M.money(currentCommittee.contribution_amount);
            document.querySelector('#detailMembersCount').textContent = currentCommittee.total_members;
            document.querySelector('#detailPoolAmount').textContent = M.money(currentCommittee.total_pool_amount);
            document.querySelector('#detailFrequency').textContent = currentCommittee.frequency;

            const progress = currentCommittee.progress_percent || 0;
            document.querySelector('#detailProgressPercent').textContent = `${progress}%`;
            document.querySelector('#detailProgressBar').style.width = `${progress}%`;

            renderRoundsTable(currentCommittee);
            renderMembersList(currentCommittee);

            M.modal('detailModal');
        } catch (err) {
            M.toast(err.message, 'danger');
        }
    }

    function showReceiptModal({ title, committeeName, amount, memberName, type, date, status }) {
        document.querySelector('#receiptTitle').textContent = title;
        document.querySelector('#receiptCommitteeName').textContent = committeeName;
        document.querySelector('#receiptAmount').textContent = M.money(amount);
        document.querySelector('#receiptMember').textContent = memberName;
        document.querySelector('#receiptType').textContent = type;
        document.querySelector('#receiptDate').textContent = M.date(date);
        document.querySelector('#receiptStatus').textContent = (status || 'PAID').toUpperCase();

        const rawText = `🧾 *${title}*\n📌 *Committee:* ${committeeName}\n👤 *Member:* ${memberName}\n💰 *Amount:* ${M.money(amount)}\n📅 *Date:* ${M.date(date)}\n✅ *Status:* ${(status || 'PAID').toUpperCase()}\n\n-- Verified by MyLedger`;

        document.querySelector('#btnCopyWhatsApp').onclick = () => {
            navigator.clipboard.writeText(rawText).then(() => {
                M.toast('Receipt text copied to clipboard! Ready to paste into WhatsApp.', 'success');
            }).catch(() => {
                M.toast('Copy failed. Please copy manually.', 'warning');
            });
        };

        M.modal('receiptModal');
    }

    function renderRoundsTable(c) {
        const tbody = document.querySelector('#roundsTableBody');
        tbody.innerHTML = c.rounds.map(r => {
            const winnerName = r.winner_member ? r.winner_member.name : 'Unassigned';
            const isPayoutPaid = r.payout_status === 'paid';

            const paidPaymentsCount = (r.payments || []).filter(p => p.status === 'paid').length;
            const totalMembersCount = c.total_members;
            const collectionBadge = paidPaymentsCount === totalMembersCount
                ? `<span class="badge bg-success">Full (${paidPaymentsCount}/${totalMembersCount})</span>`
                : `<span class="badge bg-warning text-dark">${paidPaymentsCount}/${totalMembersCount} Collected</span>`;

            const payoutBadge = isPayoutPaid
                ? `<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Disbursed on ${M.date(r.payout_date)}</span>`
                : `<span class="badge bg-secondary">Pending</span>`;

            return `
                <tr>
                    <td class="fw-bold">Round #${r.round_number}</td>
                    <td>${M.date(r.due_date)}</td>
                    <td class="fw-medium text-primary">${M.esc(winnerName)}</td>
                    <td>${collectionBadge}</td>
                    <td>${payoutBadge}</td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary btn-record-payments" data-round-id="${r.id}">
                                <i class="bi bi-cash-stack me-1"></i>Contributions
                            </button>
                            ${!isPayoutPaid ? `
                                <button class="btn btn-success btn-disburse-payout" data-round-id="${r.id}">
                                    <i class="bi bi-award me-1"></i>Disburse Payout
                                </button>
                            ` : `
                                <button class="btn btn-outline-success btn-payout-receipt" data-round-id="${r.id}" title="View Receipt">
                                    <i class="bi bi-receipt"></i>
                                </button>
                            `}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        tbody.querySelectorAll('.btn-record-payments').forEach(btn => {
            btn.onclick = () => openRoundPaymentsModal(btn.dataset.roundId);
        });

        tbody.querySelectorAll('.btn-disburse-payout').forEach(btn => {
            btn.onclick = () => openPayoutModal(btn.dataset.roundId);
        });

        tbody.querySelectorAll('.btn-payout-receipt').forEach(btn => {
            btn.onclick = () => {
                const r = c.rounds.find(x => String(x.id) === String(btn.dataset.roundId));
                if (!r) return;
                showReceiptModal({
                    title: 'Committee Round Pot Disbursed Receipt',
                    committeeName: c.name,
                    amount: r.payout_amount,
                    memberName: r.winner_member ? r.winner_member.name : 'Winner',
                    type: `Round #${r.round_number} Pot Disbursed`,
                    date: r.payout_date || r.due_date,
                    status: 'Disbursed & Paid'
                });
            };
        });
    }

    function renderMembersList(c) {
        const tbody = document.querySelector('#membersListBody');
        tbody.innerHTML = c.members.map(m => `
            <tr>
                <td class="fw-bold">${m.slot_number}</td>
                <td class="fw-medium">${M.esc(m.name)}</td>
                <td>${m.person ? M.esc(m.person.name) : '<span class="text-secondary">—</span>'}</td>
                <td><span class="badge bg-info text-dark">Round #${m.payout_round_no}</span></td>
            </tr>
        `).join('');
    }

    // Record Member Payments Modal
    function openRoundPaymentsModal(roundId) {
        currentRound = currentCommittee.rounds.find(r => String(r.id) === String(roundId));
        if (!currentRound) return;

        document.querySelector('#roundPaymentsTitle').textContent = `Record Contributions - Round #${currentRound.round_number} (${M.date(currentRound.due_date)})`;

        const tbody = document.querySelector('#roundPaymentsTableBody');
        tbody.innerHTML = (currentRound.payments || []).map(p => {
            const isPaid = p.status === 'paid';
            const memberName = p.member ? p.member.name : 'Member';

            return `
                <tr>
                    <td class="fw-medium">${M.esc(memberName)}</td>
                    <td class="fw-bold">${M.money(p.amount)}</td>
                    <td>${isPaid ? M.date(p.paid_at) : '<span class="text-secondary">—</span>'}</td>
                    <td>
                        ${isPaid ? '<span class="badge bg-success">Paid</span>' : '<span class="badge bg-warning text-dark">Pending</span>'}
                    </td>
                    <td class="text-end">
                        <div class="btn-group btn-group-sm">
                            <button class="btn ${isPaid ? 'btn-outline-secondary' : 'btn-success'} btn-toggle-payment" data-member-id="${p.committee_member_id}" data-current-status="${p.status}">
                                ${isPaid ? 'Mark Pending' : '<i class="bi bi-check-lg me-1"></i>Mark Paid'}
                            </button>
                            ${isPaid ? `
                                <button class="btn btn-outline-primary btn-payment-receipt" data-member-id="${p.committee_member_id}">
                                    <i class="bi bi-receipt"></i>
                                </button>
                            ` : ''}
                        </div>
                    </td>
                </tr>
            `;
        }).join('');

        tbody.querySelectorAll('.btn-toggle-payment').forEach(btn => {
            btn.onclick = async () => {
                const memberId = btn.dataset.memberId;
                const newStatus = btn.dataset.currentStatus === 'paid' ? 'pending' : 'paid';
                const accountId = document.querySelector('#paymentAccountId').value;

                try {
                    await M.request(`/ajax/committees/rounds/${currentRound.id}/members/${memberId}/payment`, {
                        method: 'POST',
                        body: JSON.stringify({
                            status: newStatus,
                            paid_at: newStatus === 'paid' ? new Date().toISOString().slice(0, 10) : null,
                            account_id: accountId || null
                        })
                    });

                    M.toast(newStatus === 'paid' ? 'Payment recorded as paid!' : 'Payment marked pending.');
                    // Refresh current committee details
                    openDetailModal(currentCommittee.id);
                    openRoundPaymentsModal(currentRound.id);
                } catch (err) {
                    M.toast(err.message, 'danger');
                }
            };
        });

        tbody.querySelectorAll('.btn-payment-receipt').forEach(btn => {
            btn.onclick = () => {
                const p = currentRound.payments.find(x => String(x.committee_member_id) === String(btn.dataset.memberId));
                if (!p) return;
                showReceiptModal({
                    title: 'Committee Contribution Receipt',
                    committeeName: currentCommittee.name,
                    amount: p.amount,
                    memberName: p.member ? p.member.name : 'Member',
                    type: `Round #${currentRound.round_number} Contribution`,
                    date: p.paid_at || currentRound.due_date,
                    status: p.status
                });
            };
        });

        M.modal('roundPaymentsModal');
    }

    // Open Disburse Payout Modal
    function openPayoutModal(roundId) {
        currentRound = currentCommittee.rounds.find(r => String(r.id) === String(roundId));
        if (!currentRound) return;

        payoutForm.reset();
        payoutForm.round_id.value = currentRound.id;
        document.querySelector('#payoutAmountDisplay').value = M.money(currentRound.payout_amount);

        // Fill members select
        M.fillSelect(payoutForm.winner_member_id, currentCommittee.members, {
            placeholder: 'Select Turn Recipient',
            selected: currentRound.winner_member_id
        });

        M.modal('payoutModal');
    }

    // Submit Disburse Payout
    payoutForm.onsubmit = async (e) => {
        e.preventDefault();
        const payload = M.payload(payoutForm);
        const roundId = payload.round_id;
        delete payload.round_id;

        try {
            await M.request(`/ajax/committees/rounds/${roundId}/payout`, {
                method: 'POST',
                body: JSON.stringify(payload)
            });
            M.modal('payoutModal', 'hide');
            M.toast('Round payout disbursed successfully!');
            openDetailModal(currentCommittee.id);
            loadCommittees();
        } catch (err) {
            M.errors(payoutForm, err);
        }
    };

    // Initialize Page
    Promise.all([loadRefs(), loadCommittees()]);
})();
</script>
@endpush
