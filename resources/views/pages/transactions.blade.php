@extends('layouts.app')
@section('title', 'Transactions')
@section('page-title', 'Transactions')
@section('page-subtitle', 'Search and manage income, expenses and transfers.')
@section('content')
<div class="surface-card table-card" data-base-currency-id="{{ auth()->user()->settings?->base_currency_id }}">
    <div class="filter-bar">
        <div class="row g-2 align-items-end">
            <div class="col-md-3"><label class="form-label">Search</label><input id="filterSearch" class="form-control" placeholder="Description or reference"></div>
            <div class="col-6 col-md-2"><label class="form-label">From</label><input id="filterFrom" class="form-control" type="date"></div>
            <div class="col-6 col-md-2"><label class="form-label">To</label><input id="filterTo" class="form-control" type="date"></div>
            <div class="col-md-2"><label class="form-label">Type</label><select id="filterType" class="form-select"><option value="">All</option><option value="expense">Expense</option><option value="income">Income</option><option value="transfer">Transfer</option><option value="loan_given">Loan given</option><option value="loan_taken">Loan taken</option></select></div>
            <div class="col-md-3 d-flex gap-2"><button class="btn btn-outline-secondary flex-fill" id="applyFilters">Apply</button><button class="btn btn-primary flex-fill" id="addTransaction"><i class="bi bi-plus-lg me-1"></i>Add</button></div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table align-middle"><thead><tr><th>Date</th><th>Transaction</th><th>Account</th><th>Reference</th><th class="text-end">Amount</th><th>Status</th><th></th></tr></thead><tbody id="transactionRows"></tbody></table>
    </div>
    <div class="d-flex justify-content-between align-items-center p-3 border-top" id="transactionPager"></div>
</div>

<div class="modal fade" id="transactionModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form class="modal-content" id="transactionForm"><div class="modal-header"><h5 class="modal-title">Add transaction</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">
    <div class="mb-3"><label class="form-label">Transaction type</label><div class="btn-group w-100" role="group"><input class="btn-check" type="radio" name="type" id="txExpense" value="expense" checked><label class="btn btn-outline-danger" for="txExpense">Expense</label><input class="btn-check" type="radio" name="type" id="txIncome" value="income"><label class="btn btn-outline-success" for="txIncome">Income</label><input class="btn-check" type="radio" name="type" id="txTransfer" value="transfer"><label class="btn btn-outline-primary" for="txTransfer">Transfer</label></div></div>
    <div class="row g-3"><div class="col-md-6"><label class="form-label">Amount</label><input class="form-control" name="amount" id="txAmountInput" type="number" min="0.01" step="0.01" required></div><div class="col-md-6"><label class="form-label">Date</label><input class="form-control" name="transaction_date" type="date" value="{{ now()->toDateString() }}" required></div></div>
    <div id="txCurrencyRateNotice"></div>
    <div class="mt-3"><label class="form-label">Currency</label><select class="form-select" name="currency_id" id="txCurrencySelect"></select></div>
    <div class="mt-3 tx-source"><label class="form-label">From account</label><select class="form-select" name="source_account_id"></select></div>
    <div class="mt-3 tx-destination d-none"><label class="form-label">To account</label><select class="form-select" name="destination_account_id"></select></div>
    <div class="mt-3 tx-category"><label class="form-label">Category</label><select class="form-select" name="category_id"></select></div>
    <div class="row g-3 mt-0"><div class="col-md-6"><label class="form-label">Person / payee</label><select class="form-select" name="person_id"></select></div><div class="col-md-6"><label class="form-label">Tags</label><select class="form-select" name="tag_ids[]" multiple size="2"></select></div></div>
    <div class="mt-3"><label class="form-label">Description</label><input class="form-control" name="description" maxlength="255" placeholder="What was this for?"></div>
    <div class="row g-3 mt-0"><div class="col-md-6"><label class="form-label">Reference</label><input class="form-control" name="reference_no" maxlength="60"></div><div class="col-md-6"><label class="form-label">Notes</label><input class="form-control" name="notes" maxlength="5000"></div></div>
</div><div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Post transaction</button></div></form></div></div>

<div class="modal fade" id="transactionViewModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Transaction details</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body" id="transactionDetail"></div></div></div></div>
@endsection
@push('scripts')
<script>
(() => {
    const M=MyLedger, rows=document.querySelector('#transactionRows'), pager=document.querySelector('#transactionPager'), form=document.querySelector('#transactionForm');
    let page=1, accounts=[], categories=[], people=[], tags=[], currencies=[];
    const typeOf=t=>M.typeValue(t.type);
    async function bootstrapData(){
        [accounts,categories,people,tags,currencies]=await Promise.all([
            M.request('/ajax/accounts'),
            M.request('/ajax/categories'),
            M.request('/ajax/people'),
            M.request('/ajax/tags'),
            M.request('/ajax/currencies')
        ]);
        loadCurrencies();
        refreshForm();
    }
    function getBaseCurrencyId() {
        return document.querySelector('.surface-card.table-card')?.dataset?.baseCurrencyId || null;
    }
    function updateRateNotice(){
        const amt=form.querySelector('[name="amount"]').value;
        const type=form.querySelector('[name="type"]:checked').value;
        const accId=type==='income'?form.querySelector('[name="destination_account_id"]').value:form.querySelector('[name="source_account_id"]').value;
        const acc=accounts.find(a=>String(a.id)===String(accId));
        M.renderCurrencyRateNotice(document.querySelector('#txCurrencyRateNotice'),amt,acc?.currency_id);
    }
    function filterAccountsByCurrency(currencyId) {
        return accounts.filter(a => String(a.currency_id) === String(currencyId));
    }
    function refreshForm(){
        const type=form.querySelector('[name="type"]:checked').value;
        const src=form.querySelector('[name="source_account_id"]'), dst=form.querySelector('[name="destination_account_id"]'), cat=form.querySelector('[name="category_id"]');
        const currencySelect = form.querySelector('[name="currency_id"]');
        const selectedCurrencyId = currencySelect.value || getBaseCurrencyId();
        const filteredAccounts = filterAccountsByCurrency(selectedCurrencyId);
        
        M.fillSelect(src, filteredAccounts, {placeholder:'Select account'});
        M.fillSelect(dst, filteredAccounts, {placeholder:'Select account'});
        M.fillSelect(cat, categories.filter(c=>c.type===type), {placeholder:'Select category'});
        M.fillSelect(form.person_id, people, {placeholder:'No person / payee'});
        M.fillSelect(form.querySelector('[name="tag_ids[]"]'), tags, {placeholder:'No tags'});
        form.querySelector('.tx-source').classList.toggle('d-none', type==='income');
        form.querySelector('.tx-destination').classList.toggle('d-none', type==='expense');
        form.querySelector('.tx-category').classList.toggle('d-none', type==='transfer');
        updateRateNotice();
    }
    // Load currencies into dropdown and set default
    function loadCurrencies() {
        const currencySelect = form.querySelector('[name="currency_id"]');
        M.fillSelect(currencySelect, currencies, {
            value: 'id',
            label: c => `${c.code} (${c.symbol})`,
            placeholder: 'Select currency...',
            selected: getBaseCurrencyId()
        });
        currencySelect.onchange = refreshForm;
    }
    form.querySelector('[name="amount"]').oninput=updateRateNotice;
    form.querySelector('[name="source_account_id"]').onchange=updateRateNotice;
    form.querySelector('[name="destination_account_id"]').onchange=updateRateNotice;
    async function load(p=1){ page=p; const qs=M.query({page,search:document.querySelector('#filterSearch').value,from:document.querySelector('#filterFrom').value,to:document.querySelector('#filterTo').value,type:document.querySelector('#filterType').value,per_page:20}); try{const data=await M.request('/ajax/transactions?'+qs);rows.innerHTML=data.data.length?data.data.map(t=>{const type=typeOf(t);const sign=type==='income'?'+':type==='expense'?'-':'';const account=t.source_account?.name||t.destination_account?.name||t.person?.name||'—';return `<tr><td>${M.date(t.transaction_date)}</td><td><div class="fw-semibold">${M.esc(t.description||t.category?.name||type.replaceAll('_',' '))}</div><div class="small text-secondary text-capitalize">${M.esc(type.replaceAll('_',' '))}</div></td><td>${M.esc(account)}</td><td class="small text-secondary">${M.esc(t.reference_no)}</td><td class="text-end ${type==='income'?'money-positive':type==='expense'?'money-negative':''}">${sign}${M.money(t.amount, t.currency.symbol)}</td><td><span class="badge ${t.status==='posted'?'text-bg-success':'text-bg-secondary'}">${M.esc(t.status)}</span></td><td class="text-end"><button class="btn btn-sm btn-icon view-tx" data-id="${t.id}"><i class="bi bi-eye"></i></button></td></tr>`}).join(''):'<tr><td colspan="7"><div class="empty-state"><i class="bi bi-receipt"></i>No transactions found.</div></td></tr>'; pager.innerHTML=`<span class="small text-secondary">Page ${data.current_page} of ${data.last_page} · ${data.total} records</span><div class="btn-group"><button class="btn btn-sm btn-outline-secondary prev" ${!data.prev_page_url?'disabled':''}>Previous</button><button class="btn btn-sm btn-outline-secondary next" ${!data.next_page_url?'disabled':''}>Next</button></div>`;pager.querySelector('.prev')?.addEventListener('click',()=>load(page-1));pager.querySelector('.next')?.addEventListener('click',()=>load(page+1));}catch(e){M.toast(e.message,'danger');}}
    document.querySelectorAll('[name="type"]').forEach(r=>r.onchange=refreshForm);
    document.querySelector('#applyFilters').onclick=()=>load(1);
    document.querySelector('#addTransaction').onclick=()=>{form.reset();form.transaction_date.value='{{ now()->toDateString() }}';refreshForm();M.modal('transactionModal');};
    form.onsubmit=async e=>{e.preventDefault();M.clearErrors(form);const data=M.payload(form),type=data.type;delete data.type;if(type==='expense')delete data.destination_account_id;if(type==='income')delete data.source_account_id;if(type==='transfer')delete data.category_id;const currencyId=data.currency_id||document.querySelector('[name="currency_id"]').value||getBaseCurrencyId();if(currencyId)data.currency_id=currencyId;try{await M.request(`/ajax/transactions/${type}`,{method:'POST',body:JSON.stringify(data)});M.modal('transactionModal','hide');M.toast('Transaction posted.');load(1);}catch(err){M.errors(form,err);}};
    rows.onclick=async e=>{const btn=e.target.closest('.view-tx');if(!btn)return;try{const t=await M.request('/ajax/transactions/'+btn.dataset.id);document.querySelector('#transactionDetail').innerHTML=`<div class="row g-3"><div class="col-md-4"><div class="text-secondary small">Reference</div><div class="fw-semibold">${M.esc(t.reference_no)}</div></div><div class="col-md-4"><div class="text-secondary small">Date</div><div class="fw-semibold">${M.date(t.transaction_date)}</div></div><div class="col-md-4"><div class="text-secondary small">Amount</div><div class="fw-semibold">${M.money(t.amount, t.currency.symbol)}</div></div><div class="col-12"><div class="text-secondary small">Description</div><div>${M.esc(t.description||'—')}</div></div></div><hr><h6>Ledger entries</h6><div class="table-responsive"><table class="table table-sm"><thead><tr><th>Account</th><th class="text-end">Debit</th><th class="text-end">Credit</th></tr></thead><tbody>${t.entries.map(x=>`<tr><td>${M.esc(x.account?.name||'')}</td><td class="text-end">${M.money(x.debit, x.currency.symbol)}</td><td class="text-end">${M.money(x.credit, x.currency.symbol)}</td></tr>`).join('')}</tbody></table></div><hr><div class="d-flex justify-content-between align-items-center mb-2"><h6 class="mb-0">Attachments</h6><label class="btn btn-sm btn-outline-secondary mb-0"><i class="bi bi-paperclip me-1"></i>Add file<input type="file" class="d-none" id="txAttachment"></label></div><div id="txAttachments">${t.attachments?.length?t.attachments.map(a=>`<div class="d-flex justify-content-between align-items-center py-2 border-bottom"><a href="/ajax/attachments/${a.id}/download" class="text-decoration-none"><i class="bi bi-file-earmark me-2"></i>${M.esc(a.original_name)}</a><button class="btn btn-sm text-danger delete-attachment" data-id="${a.id}"><i class="bi bi-trash"></i></button></div>`).join(''):'<div class="small text-secondary">No attachments.</div>'}</div>${t.status==='posted'?`<div class="text-end mt-3"><button class="btn btn-outline-danger btn-sm" id="reverseTx">Reverse transaction</button></div>`:''}`;M.modal('transactionViewModal');document.querySelector('#txAttachment')?.addEventListener('change',async ev=>{const file=ev.target.files?.[0];if(!file)return;const fd=new FormData();fd.append('file',file);fd.append('financial_transaction_id',t.id);try{await M.request('/ajax/attachments',{method:'POST',body:fd});M.toast('Attachment uploaded.');M.modal('transactionViewModal','hide');btn.click();}catch(err){M.toast(err.message,'danger')}});document.querySelectorAll('.delete-attachment').forEach(x=>x.addEventListener('click',async()=>{if(!confirm('Delete this attachment?'))return;try{await M.request('/ajax/attachments/'+x.dataset.id,{method:'DELETE'});M.toast('Attachment deleted.');M.modal('transactionViewModal','hide');btn.click();}catch(err){M.toast(err.message,'danger')}}));document.querySelector('#reverseTx')?.addEventListener('click',async()=>{if(!confirm('Reverse this transaction? This keeps an immutable audit trail.'))return;try{await M.request(`/ajax/transactions/${t.id}/reverse`,{method:'POST',body:JSON.stringify({date:new Date().toISOString().slice(0,10)})});M.modal('transactionViewModal','hide');M.toast('Transaction reversed.');load(page);}catch(err){M.toast(err.message,'danger');}});}catch(err){M.toast(err.message,'danger');}};
    Promise.all([bootstrapData(),load(1)]).then(()=>{const requested=new URLSearchParams(location.search).get('new');if(['expense','income','transfer'].includes(requested)){form.querySelector(`[name="type"][value="${requested}"]`).checked=true;refreshForm();M.modal('transactionModal');}}).catch(e=>M.toast(e.message,'danger'));
})();
</script>
@endpush
