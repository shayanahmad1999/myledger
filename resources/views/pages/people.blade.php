@extends('layouts.app')
@section('title', 'People')
@section('page-title', 'People')
@section('page-subtitle', 'Contacts used for loans and personal finance records.')
@section('content')
    <div class="page-actions justify-content-end"><button class="btn btn-primary" id="newPerson"><i
                class="bi bi-person-plus me-2"></i>Add person</button></div>
    <div class="surface-card table-card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Loans</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="peopleRows"></tbody>
            </table>
        </div>
    </div>
    <div class="modal fade" id="personModal">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="personForm">
                <div class="modal-header">
                    <h5 class="modal-title">Add person</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body"><input type="hidden" name="id">
                    <div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name"
                            required></div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control"
                                name="phone"></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input class="form-control"
                                name="email" type="email"></div>
                    </div>
                    <div class="mt-3"><label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal"
                        type="button">Cancel</button><button class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        (() => {
            const M = MyLedger,
                body = document.querySelector('#peopleRows'),
                form = document.querySelector('#personForm');
            let items = [];
            async function load() {
                try {
                    items = await M.request('/ajax/people');
                    body.innerHTML = items.length ? items.map(p =>
                            `<tr><td class="fw-semibold">${M.esc(p.name)}</td><td>${M.esc(p.phone||'—')}</td><td>${M.esc(p.email||'—')}</td><td>${p.loans_count||0}</td><td class="text-end"><button class="btn btn-sm btn-icon edit" data-id="${p.id}"><i class="bi bi-pencil"></i></button> <button class="btn btn-sm btn-icon text-danger delete" data-id="${p.id}"><i class="bi bi-trash"></i></button></td></tr>`
                            ).join('') :
                        '<tr><td colspan="5"><div class="empty-state"><i class="bi bi-people"></i>No people yet.</div></td></tr>';
                } catch (e) {
                    M.toast(e.message, 'danger')
                }
            }
            document.querySelector('#newPerson').onclick = () => {
                form.reset();
                form.id.value = '';
                form.querySelector('.modal-title').textContent = 'Add person';
                M.modal('personModal')
            };
            body.onclick = async e => {
                const edit = e.target.closest('.edit'),
                    del = e.target.closest('.delete');
                if (edit) {
                    const p = items.find(x => String(x.id) === edit.dataset.id);
                    Object.keys(p).forEach(k => {
                        if (form.elements[k] && p[k] != null) form.elements[k].value = p[k]
                    });
                    form.querySelector('.modal-title').textContent = 'Edit person';
                    M.modal('personModal')
                }
                if (del && confirm('Delete this person?')) {
                    try {
                        await M.request('/ajax/people/' + del.dataset.id, {
                            method: 'DELETE'
                        });
                        M.toast('Person deleted.');
                        load()
                    } catch (err) {
                        M.toast(err.message, 'danger')
                    }
                }
            };
            form.onsubmit = async e => {
                e.preventDefault();
                const d = M.payload(form),
                    id = d.id;
                delete d.id;
                try {
                    await M.request(id ? '/ajax/people/' + id : '/ajax/people', {
                        method: id ? 'PATCH' : 'POST',
                        body: JSON.stringify(d)
                    });
                    M.modal('personModal', 'hide');
                    M.toast('Saved.');
                    load()
                } catch (err) {
                    M.errors(form, err)
                }
            };
            load()
        })();
    </script>
@endpush
