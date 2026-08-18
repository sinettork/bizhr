<x-layouts::app title="Employment types">
    <x-workspace-command-bar title="Employment types" icon="fa-id-card">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search employment type or code" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms">
                </div>
                <select class="form-select reference-status" name="status" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                    <option value="">All statuses</option>
                    <option value="1" @selected(request('status') === '1')>Active</option>
                    <option value="0" @selected(request('status') === '0')>Inactive</option>
                </select>
                <button class="btn btn-primary reference-search-button">Search</button>
            </form>
        </x-slot:filters>
        <x-slot:actions><x-list-actions :add-target="auth()->user()->can('employment-type.create') ? '#typeForm' : null" add-label="Add employment type" /></x-slot:actions>
    </x-workspace-command-bar>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th class="ps-3">Employment type</th><th>Description</th><th>Employees</th><th>Status</th><th class="text-end pe-3">Actions</th></tr></thead>
                <tbody>
                    @forelse($types as $type)
                        <tr>
                            <td class="ps-3"><div class="fw-medium">{{ $type->name }}</div><small class="text-body-secondary">{{ $type->code }}</small></td>
                            <td>{{ $type->description ?: '—' }}</td>
                            <td>{{ $type->employees_count }}</td>
                            <td><span class="status-text text-bg-{{ $type->is_active ? 'success' : 'secondary' }}">{{ $type->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-end pe-3">
                                <div class="d-inline-flex">
                                    <x-entity-action-menu
                                        :can-edit="auth()->user()->can('employment-type.edit')"
                                        :can-delete="auth()->user()->can('employment-type.delete')"
                                        edit-target="#editType{{ $type->id }}"
                                        :delete-url="route('employment-types.destroy', $type)"
                                        delete-confirm="Delete this unreferenced employment type?"
                                        aria-label="Actions for {{ $type->name }}"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state class="py-5 px-3" icon="fa-id-card" title="No employment types found" message="Adjust the filters or add an employment type." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$types" />
    </div>

    @can('employment-type.create')
        <div class="modal fade" id="typeForm" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('employment-types.store') }}">@csrf<div class="modal-header"><h2 class="modal-title fs-5">Add employment type</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="form-label">Name <span class="text-danger">*</span></label><input class="form-control mb-3" name="name" required><label class="form-label">Code</label><input class="form-control mb-3" name="code" placeholder="auto-generate"><label class="form-label">Sort order</label><input class="form-control mb-3" type="number" name="sort_order" value="0"><label class="form-label">Description</label><textarea class="form-control mb-3" name="description"></textarea><label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><span class="form-check-label">Active</span></label></div><x-form-save-actions :allow-save-new="true" save-label="Save & close" new-label="Save & new" /></form></div></div>
    @endcan

    @can('employment-type.edit')
        @foreach($types as $type)
            <div class="modal fade" id="editType{{ $type->id }}" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('employment-types.update',$type) }}">@csrf @method('PUT')<div class="modal-header"><h2 class="modal-title fs-5">Edit employment type</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="form-label">Name</label><input class="form-control mb-3" name="name" value="{{ $type->name }}" required><label class="form-label">Code</label><input class="form-control mb-3" name="code" value="{{ $type->code }}" placeholder="auto-generate"><label class="form-label">Sort order</label><input class="form-control mb-3" type="number" name="sort_order" value="{{ $type->sort_order }}"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($type->is_active)><span class="form-check-label">Active</span></label></div><x-form-save-actions save-label="Save & close" /></form></div></div>
        @endforeach
    @endcan
</x-layouts::app>
