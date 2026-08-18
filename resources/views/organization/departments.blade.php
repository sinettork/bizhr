<x-layouts::app title="Departments">
    <x-workspace-command-bar title="Departments" icon="fa-sitemap">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search department or code" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms">
                </div>
                <select class="form-select reference-status" name="branch_id" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                    <option value="">All branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
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
        <x-slot:actions>
            <x-list-actions :add-target="auth()->user()->can('department.create') ? '#departmentForm' : null" add-label="Add department" />
        </x-slot:actions>
    </x-workspace-command-bar>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="reference-list" data-list-container>
        @if($departments->count())
            <div class="px-3 py-2 border-bottom bg-body-tertiary d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <div class="fw-semibold text-dark">Organization structure</div>
                    <div class="small text-body-secondary">Departments grouped by branch for quick workforce scanning.</div>
                </div>
                <span class="badge status-counter">{{ number_format($departments->total()) }} total</span>
            </div>

            @php
                $departmentGroups = $departments->getCollection()->groupBy(fn ($department) => $department->branch?->name ?: 'Company-wide');
            @endphp

            <div class="p-3">
                @foreach($departmentGroups as $branchName => $group)
                    <section class="mb-4 last-child-mb-0">
                        <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <span class="page-icon flex-shrink-0" style="width:30px;height:30px;font-size:.72rem;"><i class="fa-solid fa-building"></i></span>
                                <div class="min-w-0">
                                    <h2 class="h6 mb-0 text-truncate">{{ $branchName }}</h2>
                                    <div class="small text-body-secondary">{{ $group->count() }} department{{ $group->count() === 1 ? '' : 's' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="row row-cols-1 row-cols-md-2 row-cols-xl-4 row-cols-xxl-5 g-2">
                            @foreach($group as $department)
                                <div class="col">
                                    <article class="card h-100 shadow-none department-compact-card">
                                        <div class="card-body p-2 p-lg-3">
                                            <div class="d-flex align-items-start justify-content-between gap-2">
                                                <div class="d-flex align-items-start gap-2 min-w-0">
                                                    <span class="page-icon flex-shrink-0" style="width:34px;height:34px;font-size:.76rem;"><i class="fa-solid fa-sitemap"></i></span>
                                                    <div class="min-w-0">
                                                        <h3 class="h6 mb-1 text-dark text-truncate" title="{{ $department->name }}">{{ $department->name }}</h3>
                                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                                            <span class="status-text text-bg-{{ $department->is_active ? 'success' : 'secondary' }}">{{ $department->is_active ? 'Active' : 'Inactive' }}</span>
                                                            <span class="small text-body-secondary text-truncate"><i class="fa-solid fa-code me-1"></i>{{ $department->code ?: 'No code' }}</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <x-entity-action-menu
                                                    :can-edit="auth()->user()->can('department.edit')"
                                                    :can-delete="auth()->user()->can('department.delete')"
                                                    edit-target="#editDepartment{{ $department->id }}"
                                                    :delete-url="route('departments.destroy', $department)"
                                                    delete-confirm="Delete this department? Referenced departments cannot be deleted."
                                                    aria-label="Actions for {{ $department->name }}"
                                                />
                                            </div>

                                            <hr class="my-2">

                                            <div class="row g-0 text-center">
                                                <div class="col-4 px-1">
                                                    <div class="fw-bold text-dark">{{ number_format($department->employees_count) }}</div>
                                                    <div class="small text-body-secondary">Employees</div>
                                                </div>
                                                <div class="col-4 px-1 border-start border-end">
                                                    <div class="fw-semibold text-dark text-truncate" title="{{ $department->manager_name ?: 'Not assigned' }}">{{ $department->manager_name ?: '—' }}</div>
                                                    <div class="small text-body-secondary">Manager</div>
                                                </div>
                                                <div class="col-4 px-1">
                                                    <div class="fw-semibold text-dark text-truncate" title="{{ $department->branch?->name ?: 'Company-wide' }}">{{ $department->branch?->code ?: '—' }}</div>
                                                    <div class="small text-body-secondary">Branch</div>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
            <x-pagination-footer :paginator="$departments" />
        @else
            <x-empty-state class="py-5 px-3" icon="fa-sitemap" title="No departments found" message="Adjust the branch or status filters, or add a department to build your organization structure." />
        @endif
    </div>

    @can('department.create')
        <div class="modal fade" id="departmentForm" tabindex="-1">
            <div class="modal-dialog">
                <form class="modal-content" method="POST" action="{{ route('departments.store') }}">
                    @csrf
                    <div class="modal-header"><h2 class="modal-title fs-5">Add department</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body"><x-department-fields :branches="$branches" /><label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><span class="form-check-label">Active</span></label></div>
                    <x-form-save-actions :allow-save-new="true" save-label="Save & close" new-label="Save & new" />
                </form>
            </div>
        </div>
    @endcan

    @can('department.edit')
        @foreach($departments as $department)
            <div class="modal fade" id="editDepartment{{ $department->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('departments.update', $department) }}">
                        @csrf @method('PUT')
                        <div class="modal-header"><h2 class="modal-title fs-5">Edit department</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body"><x-department-fields :branches="$branches" :department="$department" /><label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($department->is_active)><span class="form-check-label">Active</span></label></div>
                        <x-form-save-actions save-label="Save & close" />
                    </form>
                </div>
            </div>
        @endforeach
    @endcan
</x-layouts::app>
