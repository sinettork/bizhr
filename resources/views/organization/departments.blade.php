<x-layouts::app title="Departments">
    <x-workspace-command-bar title="Departments" icon="fa-sitemap">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <div class="input-group reference-search"><span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search department or code" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms"></div>
                <select class="form-select reference-status" name="branch_id" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change"><option value="">All branches</option>@foreach($branches as $branch)<option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>@endforeach</select>
                <select class="form-select reference-status" name="status" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change"><option value="">All statuses</option><option value="1" @selected(request('status') === '1')>Active</option><option value="0" @selected(request('status') === '0')>Inactive</option></select>
                <button class="btn btn-primary reference-search-button">Search</button>
            </form>
        </x-slot:filters>
        <x-slot:actions><x-list-actions :add-target="auth()->user()->can('department.create') ? '#departmentForm' : null" add-label="Add department" /></x-slot:actions>
    </x-workspace-command-bar>
    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="reference-list" data-list-container>
        <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th class="ps-3">Department</th><th>Branch</th><th>Manager</th><th>Employees</th><th>Status</th><th class="text-end pe-3">Actions</th></tr></thead><tbody>
            @forelse($departments as $department)
                <tr><td class="ps-3"><div class="fw-medium">{{ $department->name }}</div><small class="text-body-secondary">{{ $department->code }}</small></td><td>{{ $department->branch?->name ?: 'Company-wide' }}</td><td>{{ $department->manager_name ?: '—' }}</td><td>{{ $department->employees_count }}</td><td><span class="badge text-bg-{{ $department->is_active ? 'success' : 'secondary' }}">{{ $department->is_active ? 'Active' : 'Inactive' }}</span></td><td class="text-end pe-3">
                    @can('department.edit')<button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#editDepartment{{ $department->id }}"><i class="fa-solid fa-pen"></i><span>Edit</span></button>@endcan
                    @can('department.delete')<form class="d-inline" method="POST" action="{{ route('departments.destroy', $department) }}" data-confirm="Delete this department? Referenced departments cannot be deleted.">@csrf @method('DELETE')<button class="btn btn-action-link btn-sm text-danger" type="submit"><i class="fa-solid fa-trash"></i><span>Delete</span></button></form>@endcan
                </td></tr>
            @empty<tr><td class="text-center text-body-secondary py-5" colspan="6">No departments.</td></tr>@endforelse
        </tbody></table></div><x-pagination-footer :paginator="$departments" />
    </div>

    @can('department.create')
        <div class="modal fade" id="departmentForm" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('departments.store') }}">@csrf<div class="modal-header"><h2 class="modal-title fs-5">Add department</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body"><x-department-fields :branches="$branches" /><label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><span class="form-check-label">Active</span></label></div><x-form-save-actions :allow-save-new="true" save-label="Save & close" new-label="Save & new" /></form></div></div>
    @endcan
    @can('department.edit')
        @foreach($departments as $department)
            <div class="modal fade" id="editDepartment{{ $department->id }}" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('departments.update', $department) }}">@csrf @method('PUT')<div class="modal-header"><h2 class="modal-title fs-5">Edit department</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body"><x-department-fields :branches="$branches" :department="$department" /><label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($department->is_active)><span class="form-check-label">Active</span></label></div><x-form-save-actions save-label="Save & close" /></form></div></div>
        @endforeach
    @endcan
</x-layouts::app>