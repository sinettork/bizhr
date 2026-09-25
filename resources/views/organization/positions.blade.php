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
        <x-slot:actions>
            <x-list-actions :show-compact="false" :show-export="false" />
        </x-slot:actions>
    </x-workspace-command-bar>

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
            <i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
            <i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="row g-3">
        {{-- Left Column: Fast Create / Edit Master Form --}}
        @canany(['position.create', 'position.edit'])
            <div class="col-lg-4 col-xl-4">
                <div class="card border shadow-sm sticky-top" style="top: 1rem; border-radius: .35rem; z-index: 10;">
                    <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-2 px-3 border-bottom">
                        <span class="fw-bold text-dark fs-6" id="formTitle">
                            <i class="fa-solid fa-circle-plus text-primary me-2"></i>Add position
                        </span>
                        <button type="button" class="btn btn-action-link btn-sm d-none" id="resetPositionFormBtn" title="Cancel edit and add new">
                            <i class="fa-solid fa-rotate-left me-1"></i>New
                        </button>
                    </div>
                    <form id="positionForm" method="POST" action="{{ route('positions.store') }}">
                        @csrf
                        <div id="methodSpoofingContainer"></div>
                        <div class="card-body p-3">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-dark" for="posTitle">Title <span class="text-danger">*</span></label>
                                <input class="form-control form-control-sm" id="posTitle" name="title" placeholder="e.g. Software Engineer, Sales Manager" required autocomplete="off">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-dark" for="posCode">Code</label>
                                <input class="form-control form-control-sm" id="posCode" name="code" placeholder="Auto-generated if blank" autocomplete="off">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-dark" for="posDepartment">Department</label>
                                <select class="form-select form-select-sm" id="posDepartment" name="department_id">
                                    <option value="">None</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-dark" for="posBranch">Branch</label>
                                <select class="form-select form-select-sm" id="posBranch" name="branch_id">
                                    <option value="">None</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row gx-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-dark" for="posMinSalary">Min Salary</label>
                                    <input class="form-control form-control-sm" id="posMinSalary" name="minimum_salary" type="number" step="0.01" min="0" placeholder="0.00">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-dark" for="posMaxSalary">Max Salary</label>
                                    <input class="form-control form-control-sm" id="posMaxSalary" name="maximum_salary" type="number" step="0.01" min="0" placeholder="0.00">
                                </div>
                            </div>

                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="posIsManager" name="is_manager_position" value="1">
                                <label class="form-check-label small fw-medium" for="posIsManager">Manager position</label>
                            </div>

                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="posIsActive" name="is_active" value="1" checked>
                                <label class="form-check-label small fw-medium" for="posIsActive">Active status</label>
                            </div>
                        </div>
                        <div class="card-footer bg-body-tertiary px-3 py-2 border-top d-flex align-items-center justify-content-between gap-2">
                            <button type="submit" name="save_action" value="new" class="btn btn-outline-secondary btn-sm" id="saveAndNewBtn">
                                <i class="fa-solid fa-plus me-1"></i>Save & new
                            </button>
                            <button type="submit" name="save_action" value="save" class="btn btn-primary btn-sm px-3" id="saveBtn">
                                <i class="fa-solid fa-check me-1"></i>Save position
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endcanany

        {{-- Right Column: Master Directory Table --}}
        <div class="{{ auth()->user()->canany(['position.create', 'position.edit']) ? 'col-lg-8 col-xl-8' : 'col-12' }}">
            <div class="reference-list" data-list-container>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Position</th>
                                <th>Department</th>
                                <th>Branch</th>
                                <th>Salary range</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($positions as $position)
                                <tr id="rowPos{{ $position->id }}" class="pos-row">
                                    <td class="ps-3">
                                        <div class="fw-semibold text-dark">{{ $position->title }}</div>
                                        <div class="small text-body-secondary font-monospace">{{ $position->code }} {!! $position->is_manager_position ? '&middot; <span class="text-primary">Manager</span>' : '' !!}</div>
                                    </td>
                                    <td>
                                        <span class="small">{{ $position->department?->name ?: '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="small">{{ $position->branch?->name ?: '—' }}</span>
                                    </td>
                                    <td>
                                        <span class="small text-body-secondary">
                                            {{ $position->minimum_salary || $position->maximum_salary ? number_format($position->minimum_salary ?? 0, 2).' – '.number_format($position->maximum_salary ?? 0, 2) : '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-{{ $position->is_active ? 'success' : 'secondary' }}">{{ $position->is_active ? 'Active' : 'Inactive' }}</span>
                                    </td>
                                    <td class="text-end pe-3 text-nowrap">
                                        @can('position.edit')
                                            <button 
                                                type="button" 
                                                class="btn btn-action-link btn-sm px-2 me-1 js-edit-pos-btn"
                                                data-id="{{ $position->id }}"
                                                data-title="{{ $position->title }}"
                                                data-code="{{ $position->code }}"
                                                data-department-id="{{ $position->department_id }}"
                                                data-branch-id="{{ $position->branch_id }}"
                                                data-min-salary="{{ $position->minimum_salary }}"
                                                data-max-salary="{{ $position->maximum_salary }}"
                                                data-is-manager="{{ $position->is_manager_position ? '1' : '0' }}"
                                                data-is-active="{{ $position->is_active ? '1' : '0' }}"
                                                data-update-url="{{ route('positions.update', $position) }}"
                                                title="Edit {{ $position->title }}"
                                            >
                                                <i class="fa-solid fa-pen-to-square text-primary"></i><span>Edit</span>
                                            </button>
                                        @endcan

                                        @can('position.delete')
                                            <form method="POST" action="{{ route('positions.destroy', $position) }}" class="d-inline" data-confirm="Delete this position? Referenced positions cannot be deleted.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-action-link btn-sm text-danger px-2" title="Delete {{ $position->title }}">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <x-empty-state class="py-5 px-3" icon="fa-briefcase" title="No positions found" message="Adjust the filters or add a position using the form on the left." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-pagination-footer :paginator="$positions" />
            </div>
        </div>
    </div>

    @push('scripts')
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('positionForm');
                if (!form) return;

                const storeUrl = @json(route('positions.store'));
                const formTitle = document.getElementById('formTitle');
                const resetBtn = document.getElementById('resetPositionFormBtn');
                const methodContainer = document.getElementById('methodSpoofingContainer');
                const saveBtn = document.getElementById('saveBtn');
                const saveAndNewBtn = document.getElementById('saveAndNewBtn');

                // Form Inputs
                const titleInput = document.getElementById('posTitle');
                const codeInput = document.getElementById('posCode');
                const deptSelect = document.getElementById('posDepartment');
                const branchSelect = document.getElementById('posBranch');
                const minSalaryInput = document.getElementById('posMinSalary');
                const maxSalaryInput = document.getElementById('posMaxSalary');
                const managerCheck = document.getElementById('posIsManager');
                const activeCheck = document.getElementById('posIsActive');

                const resetToCreate = () => {
                    form.action = storeUrl;
                    methodContainer.innerHTML = '';
                    form.reset();
                    
                    // Reset Checkboxes manually if needed
                    managerCheck.checked = false;
                    activeCheck.checked = true;

                    formTitle.innerHTML = '<i class="fa-solid fa-circle-plus text-primary me-2"></i>Add position';
                    saveBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i>Save position';
                    if (saveAndNewBtn) saveAndNewBtn.classList.remove('d-none');
                    resetBtn.classList.add('d-none');

                    document.querySelectorAll('.pos-row').forEach(r => r.classList.remove('table-primary'));
                    titleInput.focus();
                };

                resetBtn?.addEventListener('click', resetToCreate);

                document.addEventListener('click', (e) => {
                    const btn = e.target.closest('.js-edit-pos-btn');
                    if (!btn) return;

                    const dataset = btn.dataset;

                    // Set form action & spoof PUT method
                    form.action = dataset.updateUrl;
                    methodContainer.innerHTML = '<input type="hidden" name="_method" value="PUT">';

                    // Populate fields
                    titleInput.value = dataset.title || '';
                    codeInput.value = dataset.code || '';
                    deptSelect.value = dataset.departmentId || '';
                    branchSelect.value = dataset.branchId || '';
                    minSalaryInput.value = dataset.minSalary || '';
                    maxSalaryInput.value = dataset.maxSalary || '';
                    
                    managerCheck.checked = dataset.isManager === '1';
                    activeCheck.checked = dataset.isActive === '1';

                    // Update UI state
                    formTitle.innerHTML = `<i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit position`;
                    saveBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i>Update position';
                    if (saveAndNewBtn) saveAndNewBtn.classList.add('d-none');
                    resetBtn.classList.remove('d-none');

                    // Highlight editing row
                    document.querySelectorAll('.pos-row').forEach(r => r.classList.remove('table-primary'));
                    const targetRow = document.getElementById(`rowPos${dataset.id}`);
                    if (targetRow) targetRow.classList.add('table-primary');

                    titleInput.focus();
                });
            });
        </script>
    @endpush
</x-layouts::app>