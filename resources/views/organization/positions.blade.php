<x-layouts::app title="Positions">
    <x-workspace-command-bar title="Positions" icon="fa-briefcase">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search position or code" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms">
                </div>
                <select class="form-select reference-status" name="department_id" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                    <option value="">All departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->name }}{{ $department->branch?->name ? ' — '.$department->branch->name : '' }}</option>
                    @endforeach
                </select>
                <select class="form-select reference-status" name="status" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                    <option value="">All statuses</option>
                    <option value="1" @selected(request('status') === '1')>Active</option>
                    <option value="0" @selected(request('status') === '0')>Inactive</option>
                </select>
                <button class="btn btn-primary reference-search-button">Search</button>
            </form>
        </x-slot:filters>
        <x-slot:actions><x-list-actions :add-target="auth()->user()->can('position.create') ? '#positionForm' : null" add-label="Add position" /></x-slot:actions>
    </x-workspace-command-bar>

    @if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th class="ps-3">Position</th><th>Department</th><th>Branch</th><th>Salary range</th><th>Status</th><th class="text-end pe-3">Actions</th></tr></thead>
                <tbody>
                    @forelse($positions as $position)
                        <tr>
                            <td class="ps-3"><div class="fw-medium">{{ $position->title }}</div><small class="text-body-secondary">{{ $position->code }}{{ $position->is_manager_position ? ' · Manager role' : '' }}</small></td>
                            <td>{{ $position->department?->name ?: '—' }}</td>
                            <td>{{ $position->branch?->name ?: '—' }}</td>
                            <td>{{ $position->minimum_salary || $position->maximum_salary ? number_format($position->minimum_salary ?? 0, 2).' – '.number_format($position->maximum_salary ?? 0, 2) : '—' }}</td>
                            <td><span class="status-text text-bg-{{ $position->is_active ? 'success' : 'secondary' }}">{{ $position->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-end pe-3">
                                <div class="d-inline-flex">
                                    <x-entity-action-menu
                                        :can-edit="auth()->user()->can('position.edit')"
                                        :can-delete="auth()->user()->can('position.delete')"
                                        edit-target="#editPosition{{ $position->id }}"
                                        :delete-url="route('positions.destroy', $position)"
                                        delete-confirm="Delete this position? Referenced positions cannot be deleted."
                                        aria-label="Actions for {{ $position->title }}"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state class="py-5 px-3" icon="fa-briefcase" title="No positions found" message="Adjust the filters or add a position." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$positions" />
    </div>

    @can('position.create')
        <div class="modal fade" id="positionForm" tabindex="-1"><div class="modal-dialog modal-lg"><form class="modal-content" method="POST" action="{{ route('positions.store') }}">@csrf<div class="modal-header"><h2 class="modal-title fs-5">Add position</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body"><x-position-fields :branches="$branches" :departments="$departments" /><div class="mt-3"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_manager_position" value="1"><span class="form-check-label">Manager position</span></label><label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><span class="form-check-label">Active</span></label></div></div><x-form-save-actions :allow-save-new="true" save-label="Save & close" new-label="Save & new" /></form></div></div>
    @endcan

    @can('position.edit')
        @foreach($positions as $position)
            <div class="modal fade" id="editPosition{{ $position->id }}" tabindex="-1"><div class="modal-dialog modal-lg"><form class="modal-content" method="POST" action="{{ route('positions.update', $position) }}">@csrf @method('PUT')<div class="modal-header"><h2 class="modal-title fs-5">Edit position</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body"><x-position-fields :branches="$branches" :departments="$departments" :position="$position" /><div class="mt-3"><label class="form-check"><input class="form-check-input" type="checkbox" name="is_manager_position" value="1" @checked($position->is_manager_position)><span class="form-check-label">Manager position</span></label><label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($position->is_active)><span class="form-check-label">Active</span></label></div></div><x-form-save-actions save-label="Save & close" /></form></div></div>
        @endforeach
    @endcan
</x-layouts::app>
