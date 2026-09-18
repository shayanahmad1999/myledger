@extends('layouts.app')
@section('title', 'Calendar')
@section('page-title', 'Financial Calendar & Timeline')
@section('page-subtitle', 'Visual monthly timeline of cash flows, recurring bills, loan dues, and committee turns.')

@section('content')
    <div class="surface-card p-3 mb-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm" id="btnPrevMonth"><i class="bi bi-chevron-left"></i></button>
                <h3 class="h5 fw-bold mb-0" id="calendarTitle" style="min-width: 180px; text-align: center;">Month Year</h3>
                <button class="btn btn-outline-secondary btn-sm" id="btnNextMonth"><i
                        class="bi bi-chevron-right"></i></button>
                <button class="btn btn-soft-primary btn-sm ms-2" id="btnToday">Today</button>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <span class="badge bg-success-subtle text-success border">Income / Received</span>
                <span class="badge bg-danger-subtle text-danger border">Expense / Given</span>
                <span class="badge bg-primary-subtle text-primary border">Committee Round</span>
                <span class="badge bg-warning-subtle text-warning border">Loan Due</span>
            </div>
        </div>
    </div>

    <div class="surface-card p-4">
        <div class="table-responsive">
            <table class="table table-bordered align-top mb-0" style="table-layout: fixed; min-width: 800px;">
                <thead class="table-light text-center">
                    <tr>
                        <th style="width: 14.28%;">Mon</th>
                        <th style="width: 14.28%;">Tue</th>
                        <th style="width: 14.28%;">Wed</th>
                        <th style="width: 14.28%;">Thu</th>
                        <th style="width: 14.28%;">Fri</th>
                        <th style="width: 14.28%;">Sat</th>
                        <th style="width: 14.28%;">Sun</th>
                    </tr>
                </thead>
                <tbody id="calendarGridBody">
                    <!-- Dynamic Days Grid -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Event Detail Modal -->
    <div class="modal fade" id="eventDetailModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="eventModalTitle">Event Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="p-3 bg-light rounded mb-3 text-center">
                        <div class="h3 fw-bold mb-1" id="eventModalAmount"{{ auth()->user()->settings->currency->symbol }} 0</div>
                        <div class="small text-secondary" id="eventModalDate">Date</div>
                    </div>
                    <div class="list-group list-group-flush border rounded" id="eventModalDetails">
                        <!-- Dynamic details -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (() => {
            const M = window.MyLedger;
            let currentDate = new Date();
            let events = [];

            const titleEl = document.querySelector('#calendarTitle');
            const gridBody = document.querySelector('#calendarGridBody');

            async function loadEvents() {
                const year = currentDate.getFullYear();
                const month = currentDate.getMonth();

                const start = new Date(year, month, 1);
                const end = new Date(year, month + 1, 0);

                titleEl.textContent = new Intl.DateTimeFormat('en-US', {
                    month: 'long',
                    year: 'numeric'
                }).format(currentDate);

                try {
                    events = await M.request(
                        `/ajax/calendar-events?start=${start.toISOString().slice(0,10)}&end=${end.toISOString().slice(0,10)}`
                        );
                    renderCalendarGrid(year, month);
                } catch (e) {
                    M.toast(e.message, 'danger');
                }
            }

            function renderCalendarGrid(year, month) {
                const firstDay = new Date(year, month, 1);
                const lastDay = new Date(year, month + 1, 0);

                // Adjust for Monday start (0: Mon, 6: Sun)
                let startingDay = (firstDay.getDay() + 6) % 7;
                let monthLength = lastDay.getDate();

                let html = '';
                let day = 1;

                const todayStr = new Date().toISOString().slice(0, 10);

                // 6 rows maximum
                for (let i = 0; i < 6; i++) {
                    html += '<tr>';
                    for (let j = 0; j < 7; j++) {
                        if (i === 0 && j < startingDay) {
                            html += '<td class="bg-body-tertiary opacity-50" style="height: 110px;"></td>';
                        } else if (day > monthLength) {
                            html += '<td class="bg-body-tertiary opacity-50" style="height: 110px;"></td>';
                        } else {
                            const dateStr =
                                `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                            const isToday = dateStr === todayStr;

                            const dayEvents = events.filter(e => e.date === dateStr);

                            html += `
                        <td class="${isToday ? 'bg-primary-subtle' : ''}" style="height: 110px; vertical-align: top;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold small ${isToday ? 'badge bg-primary' : 'text-secondary'}">${day}</span>
                                ${dayEvents.length ? `<span class="badge bg-secondary-subtle text-secondary" style="font-size:10px;">${dayEvents.length}</span>` : ''}
                            </div>
                            <div class="d-flex flex-column gap-1 overflow-auto" style="max-height: 80px;">
                                ${dayEvents.slice(0, 3).map(ev => `
                                        <div class="badge bg-${ev.color}-subtle text-${ev.color} border text-truncate text-start calendar-event-item p-1" data-id="${ev.id}" style="cursor:pointer; font-size:11px;" title="${M.esc(ev.title)}">
                                            ${M.esc(ev.title)} (${M.money(ev.amount, ev.details.currency)})
                                        </div>
                                    `).join('')}
                                ${dayEvents.length > 3 ? `<span class="text-secondary small" style="font-size:10px;">+${dayEvents.length - 3} more</span>` : ''}
                            </div>
                        </td>
                    `;
                            day++;
                        }
                    }
                    html += '</tr>';
                    if (day > monthLength) break;
                }

                gridBody.innerHTML = html;

                gridBody.querySelectorAll('.calendar-event-item').forEach(el => {
                    el.onclick = () => {
                        const ev = events.find(x => x.id === el.dataset.id);
                        if (!ev) return;

                        document.querySelector('#eventModalTitle').textContent = ev.title;
                        document.querySelector('#eventModalAmount').textContent = M.money(ev.amount, ev.details.currency);
                        document.querySelector('#eventModalDate').textContent =
                            `${M.date(ev.date)} · ${ev.type.toUpperCase()}`;

                        let detailsHtml = '';
                        Object.entries(ev.details || {}).forEach(([k, v]) => {
                            if (v) {
                                detailsHtml += `
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <span class="text-secondary text-capitalize">${k.replaceAll('_',' ')}</span>
                                <strong class="text-end">${M.esc(String(v))}</strong>
                            </div>
                        `;
                            }
                        });

                        document.querySelector('#eventModalDetails').innerHTML = detailsHtml ||
                            '<div class="p-3 text-center text-secondary small">No additional details available.</div>';
                        M.modal('eventDetailModal');
                    };
                });
            }

            document.querySelector('#btnPrevMonth').onclick = () => {
                currentDate.setMonth(currentDate.getMonth() - 1);
                loadEvents();
            };

            document.querySelector('#btnNextMonth').onclick = () => {
                currentDate.setMonth(currentDate.getMonth() + 1);
                loadEvents();
            };

            document.querySelector('#btnToday').onclick = () => {
                currentDate = new Date();
                loadEvents();
            };

            loadEvents();
        })();
    </script>
@endpush
