<x-layouts::app title="Branches">
    <x-workspace-command-bar title="Branches" icon="fa-code-branch">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search branch, code or city" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms">
                </div>
                <select class="form-select reference-status" name="status" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                    <option value="">All statuses</option>
                    <option value="1" @selected(request('status') === '1')>Active</option>
                    <option value="0" @selected(request('status') === '0')>Inactive</option>
                </select>
                <button class="btn btn-primary reference-search-button">Search</button>
            </form>
        </x-slot:filters>
        <x-slot:actions>
            <x-list-actions :add-target="auth()->user()->can('branch.create') ? '#branchForm' : null" add-label="Add branch" />
        </x-slot:actions>
    </x-workspace-command-bar>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="reference-list" data-list-container>
        @if($branches->count())
            <div class="px-3 py-2 border-bottom bg-body-tertiary d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <div class="fw-semibold text-dark">Branch network</div>
                    <div class="small text-body-secondary">Locations, workforce and operating contacts.</div>
                </div>
                <span class="badge status-counter">{{ number_format($branches->total()) }} total</span>
            </div>

            <div class="row g-2 p-3">
                @foreach($branches as $branch)
                    <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                        <article class="card h-100 shadow-none branch-entity-card">
                            <div class="card-body p-3 d-flex flex-column">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div class="d-flex align-items-start gap-2 min-w-0">
                                        <span class="page-icon flex-shrink-0" style="width:38px;height:38px;font-size:.82rem;"><i class="fa-solid fa-building"></i></span>
                                        <div class="min-w-0">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <h2 class="h6 mb-0 text-dark text-truncate">{{ $branch->name }}</h2>
                                                @if($branch->is_head_office)
                                                    <span class="badge text-bg-primary">Head office</span>
                                                @endif
                                                <span class="badge text-bg-{{ $branch->is_active ? 'success' : 'secondary' }}">{{ $branch->is_active ? 'Active' : 'Inactive' }}</span>
                                            </div>
                                            <div class="small text-body-secondary mt-1 text-truncate">
                                                <i class="fa-solid fa-location-dot me-1"></i>{{ $branch->city ?: 'Location not set' }}
                                            </div>
                                            <div class="small text-body-secondary text-truncate">{{ $branch->code }}</div>
                                        </div>
                                    </div>

                                    <div class="dropdown flex-shrink-0">
                                        <button class="btn btn-action-link btn-sm px-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Branch actions">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            @can('branch.edit')
                                                <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#editBranch{{ $branch->id }}"><i class="fa-solid fa-pen me-2"></i>Edit branch</button></li>
                                            @endcan
                                            @can('branch.delete')
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" action="{{ route('branches.destroy',$branch) }}" data-confirm="Delete this unreferenced branch?">
                                                        @csrf @method('DELETE')
                                                        <button class="dropdown-item text-danger" type="submit"><i class="fa-solid fa-trash me-2"></i>Delete branch</button>
                                                    </form>
                                                </li>
                                            @endcan
                                        </ul>
                                    </div>
                                </div>

                                <div class="small mt-3">
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                        <span class="text-body-secondary">Workforce footprint</span>
                                        <strong class="text-dark">{{ number_format($branch->employees_count) }} emp · {{ number_format($branch->departments_count) }} dept</strong>
                                    </div>
                                    <div class="progress" style="height:4px;">
                                        @php($footprint = min(100, max(8, ($branch->employees_count * 8) + ($branch->departments_count * 6))))
                                        <div class="progress-bar" role="progressbar" style="width: {{ $footprint }}%" aria-label="Workforce footprint"></div>
                                    </div>
                                </div>

                                <hr class="my-3">

                                <div class="row g-0 text-center mt-auto">
                                    <div class="col-4 px-1">
                                        <div class="fw-bold text-dark">{{ number_format($branch->employees_count) }}</div>
                                        <div class="small text-body-secondary">Employees</div>
                                    </div>
                                    <div class="col-4 px-1 border-start border-end">
                                        <div class="fw-bold text-dark">{{ number_format($branch->departments_count) }}</div>
                                        <div class="small text-body-secondary">Departments</div>
                                    </div>
                                    <div class="col-4 px-1">
                                        <div class="fw-semibold text-dark text-truncate" title="{{ $branch->manager_name ?: 'Not assigned' }}">{{ $branch->manager_name ?: '—' }}</div>
                                        <div class="small text-body-secondary">Manager</div>
                                    </div>
                                </div>

                                <div class="small text-body-secondary text-truncate mt-3 pt-2 border-top" title="{{ $branch->phone ?: $branch->email ?: 'Not provided' }}">
                                    <i class="fa-solid fa-phone me-1"></i>{{ $branch->phone ?: $branch->email ?: 'No primary contact' }}
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
            <x-pagination-footer :paginator="$branches" />
        @else
            <div class="empty-state py-5 px-3">
                <i class="fa-solid fa-building-circle-xmark fa-2xl d-block mb-3 text-primary"></i>
                <h2 class="h6 mb-1">No branches found</h2>
                <p class="text-body-secondary mb-0">Try a different search or status filter, or add the first branch.</p>
            </div>
        @endif
    </div>

    @can('branch.create')
        <div class="modal fade" id="branchForm" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="{{ route('branches.store') }}">
                    @csrf
                    <div class="modal-header"><h2 class="modal-title fs-5">Add branch</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Name <span class="text-danger">*</span></label><input class="form-control" name="name" required></div>
                            <div class="col-md-6"><label class="form-label">Code</label><input class="form-control" name="code" placeholder="auto-generate"></div>
                            <div class="col-md-6"><label class="form-label">Manager</label><input class="form-control" name="manager_name"></div>
                            <div class="col-md-6"><label class="form-label">City</label><input class="form-control" name="city"></div>
                            <div class="col-md-6"><label class="form-label">Phone</label><input class="form-control" name="phone"></div>
                            <div class="col-md-6"><label class="form-label">Email</label><input class="form-control" type="email" name="email"></div>
                            <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="address"></textarea></div>
                            <div class="col-12">
                                <label class="form-check"><input class="form-check-input" type="checkbox" name="is_head_office" value="1"><span class="form-check-label">Head office</span></label>
                                <label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" checked><span class="form-check-label">Active</span></label>
                            </div>
                        </div>
                    </div>
                    <x-form-save-actions :allow-save-new="true" save-label="Save & close" new-label="Save & new" />
                </form>
            </div>
        </div>
    @endcan

    @can('branch.edit')
        @foreach($branches as $branch)
            <div class="modal fade" id="editBranch{{ $branch->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('branches.update',$branch) }}">
                        @csrf @method('PUT')
                        <div class="modal-header"><h2 class="modal-title fs-5">Edit branch</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">
                            <label class="form-label">Name</label><input class="form-control mb-3" name="name" value="{{ $branch->name }}" required>
                            <label class="form-label">Code</label><input class="form-control mb-3" name="code" value="{{ $branch->code }}" placeholder="auto-generate">
                            <label class="form-label">Manager</label><input class="form-control mb-3" name="manager_name" value="{{ $branch->manager_name }}">
                            <label class="form-check"><input class="form-check-input" type="checkbox" name="is_head_office" value="1" @checked($branch->is_head_office)><span class="form-check-label">Head office</span></label>
                            <label class="form-check"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($branch->is_active)><span class="form-check-label">Active</span></label>
                        </div>
                        <x-form-save-actions save-label="Save & close" />
                    </form>
                </div>
            </div>
        @endforeach
    @endcan
</x-layouts::app>
