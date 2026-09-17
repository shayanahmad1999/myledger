@extends('layouts.app')
@section('title', 'Accounts')
@section('page-title', 'Accounts')
@section('page-subtitle', 'Bank accounts, cash, wallets, savings and investments.')
@section('content')
<div class="page-actions justify-content-between">
    <div class="btn-group"><button class="btn btn-outline-secondary active" id="activeFilter">Active</button><button class="btn btn-outline-secondary" id="allFilter">Include archived</button></div>
    <button class="btn btn-primary" id="newAccount"><i class="bi bi-plus-lg me-2"></i>New account</button>
</div>
<div class="row g-3" id="accountCards"></div>

<div class="modal fade" id="accountModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><form class="modal-content" id="accountForm"><div class="modal-header"><h5 class="modal-title">Add account</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body">
    <input type="hidden" name="id">
    <div class="mb-3"><label class="form-label">Account name</label><input class="form-control" name="name" required></div>
    <div class="row g-3"><div class="col-md-6"><label class="form-label">Type</label><select class="form-select" name="type" required><option value="cash">Cash</option><option value="bank">Bank</option><option value="mobile_wallet">Mobile wallet</option><option value="savings">Savings</option><option value="investment">Investment</option></select></div><div class="col-md-6"><label class="form-label">Institution</label><input class="form-control" name="institution"></div></div>
    <div class="row g-3 mt-0"><div class="col-md-6"><label class="form-label">Currency</label><select class="form-select" name="currency_id" required></select></div><div class="col-md-6"><label class="form-label">Last 4 digits</label><input class="form-control" name="last_four" maxlength="4" inputmode="numeric"></div></div>
    <div id="accountRateNotice"></div>
    <div class="row g-3 mt-0 create-only"><div class="col-md-6"><label class="form-label">Opening balance</label><input class="form-control" name="opening_balance" type="number" min="0" step="0.01"></div><div class="col-md-6"><label class="form-label">Opening balance date</label><input class="form-control" name="opening_balance_date" type="date" value="{{ now()->toDateString() }}"></div></div>
    <div class="form-check form-switch mt-3"><input class="form-check-input" type="checkbox" name="include_in_net_worth" checked><label class="form-check-label">Include in net worth</label></div>
</div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Save account</button></div></form></div></div>
@endsection
@push('scripts')
<script>
(() => {
    const M=MyLedger, cards=document.querySelector('#accountCards'), form=document.querySelector('#accountForm'); let includeArchived=false, items=[], currencies=[];
    function updateAccountRateNotice(){ M.renderCurrencyRateNotice(document.querySelector('#accountRateNotice'), form.opening_balance.value, form.currency_id.value); }
    async function refs(){ try { currencies=await M.getCurrencies(); M.fillSelect(form.currency_id, currencies, { label: c=>`${c.code} (${c.symbol})` }); updateAccountRateNotice(); } catch(e){} }
    form.currency_id.onchange=updateAccountRateNotice;
    form.opening_balance.oninput=updateAccountRateNotice;
    async function load(){ try { items=await M.request('/ajax/accounts?include_archived='+(includeArchived?1:0)); cards.innerHTML=items.length?items.map(a=>`<div class="col-md-6 col-xl-4"><div class="surface-card p-4 h-100 ${a.is_archived?'opacity-50':''}"><div class="d-flex justify-content-between"><div class="account-icon"><i class="bi bi-${M.typeValue(a.type)==='bank'?'bank':'wallet2'}"></i></div><div class="dropdown"><button class="btn btn-sm btn-icon" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button><ul class="dropdown-menu dropdown-menu-end"><li><button class="dropdown-item edit-account" data-id="${a.id}"><i class="bi bi-pencil me-2"></i>Edit</button></li>${!a.is_archived?`<li><button class="dropdown-item text-danger archive-account" data-id="${a.id}"><i class="bi bi-archive me-2"></i>Archive</button></li>`:''}</ul></div></div><div class="account-balance">${M.money(a.balance)}</div><h3 class="h6 mb-1 mt-2">${M.esc(a.name)}</h3><div class="small text-secondary">${M.esc(a.institution||M.typeValue(a.type).replaceAll('_',' '))}${a.last_four?' · •••• '+M.esc(a.last_four):''}</div></div></div>`).join(''):'<div class="col-12"><div class="surface-card empty-state"><i class="bi bi-bank"></i>No accounts found.</div></div>'; } catch(e){M.toast(e.message,'danger');}}
    document.querySelector('#newAccount').onclick=()=>{form.reset();form.id.value='';form.include_in_net_worth.checked=true;document.querySelectorAll('.create-only').forEach(x=>x.classList.remove('d-none'));form.querySelector('.modal-title').textContent='Add account';refs().then(()=>M.modal('accountModal'));};
    document.querySelector('#activeFilter').onclick=()=>{includeArchived=false;document.querySelector('#activeFilter').classList.add('active');document.querySelector('#allFilter').classList.remove('active');load();};
    document.querySelector('#allFilter').onclick=()=>{includeArchived=true;document.querySelector('#allFilter').classList.add('active');document.querySelector('#activeFilter').classList.remove('active');load();};
    cards.onclick=async e=>{const edit=e.target.closest('.edit-account'), archive=e.target.closest('.archive-account'); if(edit){const a=items.find(x=>String(x.id)===edit.dataset.id);form.reset();form.id.value=a.id;form.name.value=a.name;form.type.value=M.typeValue(a.type);form.institution.value=a.institution||'';form.last_four.value=a.last_four||'';form.include_in_net_worth.checked=!!a.include_in_net_worth;document.querySelectorAll('.create-only').forEach(x=>x.classList.add('d-none'));form.querySelector('.modal-title').textContent='Edit account';refs().then(()=>{form.currency_id.value=a.currency_id;M.modal('accountModal');});} if(archive && confirm('Archive this account?')){try{await M.request(`/ajax/accounts/${archive.dataset.id}`,{method:'DELETE'});M.toast('Account archived.');load();}catch(err){M.toast(err.message,'danger');}}};
    form.onsubmit=async e=>{e.preventDefault();M.clearErrors(form);const id=form.id.value, data=M.payload(form);delete data.id; try{await M.request(id?`/ajax/accounts/${id}`:'/ajax/accounts',{method:id?'PATCH':'POST',body:JSON.stringify(data)});M.modal('accountModal','hide');M.toast(id?'Account updated.':'Account created.');load();}catch(err){M.errors(form,err);}};
    Promise.all([refs(),load()]);
})();
</script>
@endpush
