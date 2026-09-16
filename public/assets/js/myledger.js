window.MyLedger = (() => {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';

    async function request(url, options = {}) {
        const opts = { credentials: 'same-origin', ...options };
        const headers = new Headers(opts.headers || {});
        if (!(opts.body instanceof FormData)) headers.set('Content-Type', 'application/json');
        headers.set('Accept', 'application/json');
        headers.set('X-CSRF-TOKEN', csrf);
        opts.headers = headers;
        const response = await fetch(url, opts);
        const contentType = response.headers.get('content-type') || '';
        const data = contentType.includes('application/json') ? await response.json() : await response.text();
        if (!response.ok) {
            const error = new Error(data?.message || `Request failed (${response.status})`);
            error.status = response.status;
            error.errors = data?.errors || {};
            throw error;
        }
        return data;
    }

    function payload(form, extras = {}) {
        const fd = new FormData(form);
        const obj = {};
        for (const [key, value] of fd.entries()) {
            if (value === '') continue;
            if (key.endsWith('[]')) {
                const clean = key.slice(0, -2);
                obj[clean] ??= [];
                obj[clean].push(value);
            } else obj[key] = value;
        }
        form.querySelectorAll('input[type="checkbox"][name]').forEach(el => obj[el.name] = el.checked);
        return { ...obj, ...extras };
    }

    function money(value, symbol = 'Rs') {
        const number = Number(value || 0);
        return `${symbol} ${number.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 })}`;
    }

    function date(value) {
        if (!value) return '—';
        const d = new Date(`${String(value).slice(0,10)}T00:00:00`);
        return Number.isNaN(d.getTime()) ? value : new Intl.DateTimeFormat(undefined, { day:'2-digit', month:'short', year:'numeric' }).format(d);
    }

    function dateTime(value) {
        if (!value) return '—';
        const d = new Date(value);
        return Number.isNaN(d.getTime()) ? value : new Intl.DateTimeFormat(undefined, { dateStyle:'medium', timeStyle:'short' }).format(d);
    }

    function esc(value = '') {
        return String(value).replace(/[&<>'"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#039;','"':'&quot;'}[c]));
    }

    function toast(message, type = 'success') {
        const id = `toast-${Date.now()}`;
        const color = type === 'danger' ? 'text-bg-danger' : type === 'warning' ? 'text-bg-warning' : 'text-bg-success';
        document.querySelector('#toastContainer')?.insertAdjacentHTML('beforeend', `<div id="${id}" class="toast align-items-center border-0 ${color}" role="alert"><div class="d-flex"><div class="toast-body">${esc(message)}</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button></div></div>`);
        const el = document.getElementById(id); if (!el) return;
        const instance = bootstrap.Toast.getOrCreateInstance(el, { delay: 3500 });
        el.addEventListener('hidden.bs.toast', () => el.remove());
        instance.show();
    }

    function errors(form, error) {
        clearErrors(form);
        const items = Object.entries(error.errors || {});
        if (!items.length) { toast(error.message || 'Something went wrong.', 'danger'); return; }
        items.forEach(([key, messages]) => {
            const field = form.querySelector(`[name="${CSS.escape(key)}"]`);
            if (field) {
                field.classList.add('is-invalid');
                const feedback = document.createElement('div');
                feedback.className = 'invalid-feedback';
                feedback.textContent = Array.isArray(messages) ? messages[0] : messages;
                field.insertAdjacentElement('afterend', feedback);
            }
        });
        toast('Please check the highlighted fields.', 'danger');
    }

    function clearErrors(form) {
        form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        form.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
    }

    function fillSelect(select, items, { value='id', label='name', placeholder='Select...', selected=null } = {}) {
        select.innerHTML = `<option value="">${esc(placeholder)}</option>` + items.map(item => `<option value="${esc(item[value])}" ${String(item[value]) === String(selected) ? 'selected' : ''}>${esc(typeof label === 'function' ? label(item) : item[label])}</option>`).join('');
    }

    function typeValue(value) {
        if (value && typeof value === 'object' && 'value' in value) return value.value;
        return value;
    }

    function modal(id, action='show') {
        const el = document.getElementById(id); if (!el) return null;
        const instance = bootstrap.Modal.getOrCreateInstance(el);
        if (action === 'hide') instance.hide(); else instance.show();
        return instance;
    }

    function query(params) {
        const qs = new URLSearchParams();
        Object.entries(params).forEach(([k,v]) => { if (v !== '' && v !== null && v !== undefined) qs.set(k, v); });
        return qs.toString();
    }

    return { request, payload, money, date, dateTime, esc, toast, errors, clearErrors, fillSelect, typeValue, modal, query };
})();
