@extends('layouts.app')
@section('title', 'Categories & Tags')
@section('page-title', 'Categories & tags')
@section('page-subtitle', 'Organize income, expenses and transaction labels for cleaner reporting.')
@section('content')
    <div class="page-actions justify-content-between">
        <div class="btn-group"><button class="btn btn-outline-secondary active category-filter"
                data-type="expense">Expenses</button><button class="btn btn-outline-secondary category-filter"
                data-type="income">Income</button></div>
        <button class="btn btn-primary" id="newCategory"><i class="bi bi-plus-lg me-2"></i>New category</button>
    </div>
    <div class="surface-card table-card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Parent</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="categoryRows"></tbody>
            </table>
        </div>
    </div>
    <div class="surface-card table-card mt-4">
        <div class="card-header-clean">
            <h2>Transaction tags</h2><button class="btn btn-sm btn-soft-primary" id="newTag"><i
                    class="bi bi-plus-lg me-1"></i>New tag</button>
        </div>
        <div class="card-body-clean">
            <div class="d-flex flex-wrap gap-2" id="tagList"></div>
        </div>
    </div>

    <div class="modal fade" id="categoryModal">
        <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" id="categoryForm">
                <div class="modal-header">
                    <h5 class="modal-title">New category</h5><button class="btn-close" data-bs-dismiss="modal"
                        type="button"></button>
                </div>
                <div class="modal-body"><input type="hidden" name="id">
                    <div class="mb-3"><label class="form-label">Name</label><input class="form-control" name="name"
                            required></div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Type</label><select class="form-select"
                                name="type">
                                <option value="expense">Expense</option>
                                <option value="income">Income</option>
                            </select></div>
                        <div class="col-md-6 create-parent"><label class="form-label">Parent (optional)</label><select
                                class="form-select" name="parent_id"></select></div>
                    </div>
                </div>
                <div class="modal-footer"><button class="btn btn-light" type="button"
                        data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="tagModal">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <form class="modal-content" id="tagForm">
                <div class="modal-header">
                    <h5 class="modal-title">New tag</h5><button class="btn-close" type="button"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Tag name</label><input class="form-control" name="name"
                            required></div>
                    <div><label class="form-label">Color</label><input class="form-control form-control-color w-100"
                            name="color" type="color" value="#427a9d"></div>
                </div>
                <div class="modal-footer"><button class="btn btn-light" type="button"
                        data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save</button></div>
            </form>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        (() => {
            const M = MyLedger,
                body = document.querySelector('#categoryRows'),
                form = document.querySelector('#categoryForm'),
                tagList = document.querySelector('#tagList'),
                tagForm = document.querySelector('#tagForm');
            let type = 'expense',
                items = [];
            async function load() {
                try {
                    items = await M.request('/ajax/categories?type=' + type);
                    body.innerHTML = items.length ? items.map(c =>
                            `<tr><td class="fw-semibold">${M.esc(c.name)}</td><td class="text-capitalize">${M.esc(c.type)}</td><td>${M.esc(items.find(x=>x.id===c.parent_id)?.name||'—')}</td><td class="text-end"><button class="btn btn-sm btn-icon edit" data-id="${c.id}"><i class="bi bi-pencil"></i></button> <button class="btn btn-sm btn-icon text-danger archive" data-id="${c.id}"><i class="bi bi-archive"></i></button></td></tr>`
                            ).join('') :
                        '<tr><td colspan="4"><div class="empty-state"><i class="bi bi-tags"></i>No categories.</div></td></tr>'
                } catch (e) {
                    M.toast(e.message, 'danger')
                }
            }
            async function loadTags() {
                try {
                    const tags = await M.request('/ajax/tags');
                    tagList.innerHTML = tags.length ? tags.map(t =>
                        `<span class="badge rounded-pill border d-inline-flex align-items-center gap-2 py-2 px-3"><i class="bi bi-tag"></i>${M.esc(t.name)}<button class="btn btn-sm p-0 border-0 tag-delete" data-id="${t.id}" aria-label="Delete"><i class="bi bi-x"></i></button></span>`
                        ).join('') : '<span class="small text-secondary">No tags yet.</span>'
                } catch (e) {
                    M.toast(e.message, 'danger')
                }
            }
            document.querySelectorAll('.category-filter').forEach(b => b.onclick = () => {
                type = b.dataset.type;
                document.querySelectorAll('.category-filter').forEach(x => x.classList.toggle('active', x ===
                    b));
                load()
            });
            document.querySelector('#newCategory').onclick = () => {
                form.reset();
                form.type.value = type;
                form.type.disabled = false;
                document.querySelector('.create-parent').classList.remove('d-none');
                M.fillSelect(form.parent_id, items, {
                    placeholder: 'No parent'
                });
                M.modal('categoryModal')
            };
            body.onclick = async e => {
                const edit = e.target.closest('.edit'),
                    archive = e.target.closest('.archive');
                if (edit) {
                    const c = items.find(x => String(x.id) === edit.dataset.id);
                    form.reset();
                    form.id.value = c.id;
                    form.name.value = c.name;
                    form.type.value = c.type;
                    form.type.disabled = true;
                    document.querySelector('.create-parent').classList.add('d-none');
                    M.modal('categoryModal')
                }
                if (archive && confirm('Archive this category?')) {
                    try {
                        await M.request('/ajax/categories/' + archive.dataset.id, {
                            method: 'DELETE'
                        });
                        M.toast('Category archived.');
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
                if (id) {
                    delete d.type;
                    delete d.parent_id
                }
                try {
                    await M.request(id ? '/ajax/categories/' + id : '/ajax/categories', {
                        method: id ? 'PATCH' : 'POST',
                        body: JSON.stringify(d)
                    });
                    M.modal('categoryModal', 'hide');
                    M.toast('Category saved.');
                    load()
                } catch (err) {
                    M.errors(form, err)
                }
            };
            document.querySelector('#newTag').onclick = () => {
                tagForm.reset();
                tagForm.color.value = '#427a9d';
                M.modal('tagModal')
            };
            tagForm.onsubmit = async e => {
                e.preventDefault();
                try {
                    await M.request('/ajax/tags', {
                        method: 'POST',
                        body: JSON.stringify(M.payload(tagForm))
                    });
                    M.modal('tagModal', 'hide');
                    M.toast('Tag created.');
                    loadTags()
                } catch (err) {
                    M.errors(tagForm, err)
                }
            };
            tagList.onclick = async e => {
                const b = e.target.closest('.tag-delete');
                if (!b || !confirm('Delete this tag?')) return;
                try {
                    await M.request('/ajax/tags/' + b.dataset.id, {
                        method: 'DELETE'
                    });
                    M.toast('Tag deleted.');
                    loadTags()
                } catch (err) {
                    M.toast(err.message, 'danger')
                }
            };
            load();
            loadTags();
        })();
    </script>
@endpush
