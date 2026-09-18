@extends('layouts.app')
@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('page-subtitle', 'Budget warnings, loan due dates and finance reminders.')
@section('content')
    <div class="page-actions justify-content-end"><button class="btn btn-outline-secondary" id="markAllRead"><i
                class="bi bi-check2-all me-2"></i>Mark all read</button></div>
    <div class="surface-card" id="notificationList">
        <div class="empty-state"><i class="bi bi-bell"></i>Loading notifications…</div>
    </div>
@endsection
@push('scripts')
    <script>
        (() => {
            const M = MyLedger,
                list = document.querySelector('#notificationList');
            async function load() {
                try {
                    const d = await M.request('/ajax/notifications');
                    list.innerHTML = d.data.length ? d.data.map(n => {
                            let data = n.data;
                            try {
                                if (typeof data === 'string') data = JSON.parse(data)
                            } catch {}
                            return `<div class="p-3 p-md-4 border-bottom ${n.read_at?'opacity-75':''}"><div class="d-flex gap-3"><div class="transaction-icon"><i class="bi bi-${n.read_at?'bell':'bell-fill'}"></i></div><div class="flex-grow-1"><div class="fw-semibold">${M.esc(data?.title||'Finance reminder')}</div><div class="text-secondary small mt-1">${M.esc(data?.message||data?.body||'You have a new notification.')}</div><div class="small text-secondary mt-2">${M.dateTime(n.created_at)}</div></div>${!n.read_at?`<button class="btn btn-sm btn-outline-secondary mark-read" data-id="${n.id}">Mark read</button>`:''}</div></div>`
                        }).join('') :
                        '<div class="empty-state"><i class="bi bi-bell-slash"></i>You are all caught up.</div>'
                } catch (e) {
                    M.toast(e.message, 'danger')
                }
            }
            list.onclick = async e => {
                const b = e.target.closest('.mark-read');
                if (!b) return;
                try {
                    await M.request('/ajax/notifications/' + b.dataset.id + '/read', {
                        method: 'POST',
                        body: '{}'
                    });
                    load()
                } catch (err) {
                    M.toast(err.message, 'danger')
                }
            };
            document.querySelector('#markAllRead').onclick = async () => {
                try {
                    await M.request('/ajax/notifications/read-all', {
                        method: 'POST',
                        body: '{}'
                    });
                    M.toast('Notifications marked as read.');
                    load()
                } catch (e) {
                    M.toast(e.message, 'danger')
                }
            };
            load()
        })();
    </script>
@endpush
