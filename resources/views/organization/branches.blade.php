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
        @canany(['branch.create', 'branch.edit'])
            <div class="col-lg-4 col-xl-4">
                <div class="card border shadow-sm sticky-top" style="top: 1rem; border-radius: .35rem; z-index: 10;">
                    <div class="card-header bg-body-tertiary d-flex align-items-center justify-content-between py-2 px-3 border-bottom">
                        <span class="fw-bold text-dark fs-6" id="formTitle">
                            <i class="fa-solid fa-circle-plus text-primary me-2"></i>Add branch
                        </span>
                        <button type="button" class="btn btn-action-link btn-sm d-none" id="resetBranchFormBtn" title="Cancel edit and add new">
                            <i class="fa-solid fa-rotate-left me-1"></i>New
                        </button>
                    </div>
                    <form id="branchForm" method="POST" action="{{ route('branches.store') }}">
                        @csrf
                        <div id="methodSpoofingContainer"></div>
                        <div class="card-body p-3">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-dark" for="branchName">Name <span class="text-danger">*</span></label>
                                <input class="form-control form-control-sm" id="branchName" name="name" placeholder="e.g. Downtown Office, Westside Hub" required autocomplete="off">
                            </div>

                            <div class="row gx-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-dark" for="branchCode">Code</label>
                                    <input class="form-control form-control-sm" id="branchCode" name="code" placeholder="Auto-generate" autocomplete="off">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-dark" for="branchCity">City</label>
                                    <input class="form-control form-control-sm" id="branchCity" name="city" placeholder="e.g. New York">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-dark" for="branchManager">Manager</label>
                                <input class="form-control form-control-sm" id="branchManager" name="manager_name" placeholder="Branch manager's name">
                            </div>

                            <div class="row gx-2 mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-dark" for="branchPhone">Phone</label>
                                    <input class="form-control form-control-sm" id="branchPhone" name="phone" placeholder="Contact number">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold text-dark" for="branchEmail">Email</label>
                                    <input class="form-control form-control-sm" id="branchEmail" type="email" name="email" placeholder="Branch email">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-dark" for="branchAddress">Address</label>
                                <textarea class="form-control form-control-sm" id="branchAddress" name="address" rows="2" placeholder="Full street address"></textarea>
                            </div>

                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="branchIsHeadOffice" name="is_head_office" value="1">
                                <label class="form-check-label small fw-medium" for="branchIsHeadOffice">Head office</label>
                            </div>

                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="branchIsActive" name="is_active" value="1" checked>
                                <label class="form-check-label small fw-medium" for="branchIsActive">Active status</label>
                            </div>
                        </div>
                        <div class="card-footer bg-body-tertiary px-3 py-2 border-top d-flex align-items-center justify-content-between gap-2">
                            <button type="submit" name="save_action" value="new" class="btn btn-outline-secondary btn-sm" id="saveAndNewBtn">
                                <i class="fa-solid fa-plus me-1"></i>Save & new
                            </button>
                            <button type="submit" name="save_action" value="save" class="btn btn-primary btn-sm px-3" id="saveBtn">
                                <i class="fa-solid fa-check me-1"></i>Save branch
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endcanany

        {{-- Right Column: Master Directory Table --}}
        <div class="{{ auth()->user()->canany(['branch.create', 'branch.edit']) ? 'col-lg-8 col-xl-8' : 'col-12' }}">
            <div class="reference-list border rounded shadow-sm bg-white" data-list-container>
                
                @if($branches->count())
                    <div class="px-3 py-2 border-bottom bg-body-tertiary d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <div class="fw-semibold text-dark">Branch network</div>
                            <div class="small text-body-secondary">Locations, workforce and operating contacts.</div>
                        </div>
                        <span class="badge text-bg-secondary">{{ number_format($branches->total()) }} total</span>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Branch</th>
                                <th>Location & Contact</th>
                                <th>Manager</th>
                                <th>Workforce</th>
                                <th>Status</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($branches as $branch)
                                <tr id="rowBranch{{ $branch->id }}" class="branch-row">
                                    <td class="ps-3">
                                        <div class="fw-semibold text-dark">{{ $branch->name }}</div>
                                        <div class="small text-body-secondary font-monospace">
                                            {{ $branch->code }} {!! $branch->is_head_office ? '&middot; <span class="text-primary fw-medium">Head office</span>' : '' !!}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="small fw-medium text-dark"><i class="fa-solid fa-location-dot me-1 text-body-secondary"></i>{{ $branch->city ?: 'Unspecified city' }}</div>
                                        <div class="small text-body-secondary text-truncate" style="max-width: 180px;" title="{{ $branch->phone ?: $branch->email ?: 'Not provided' }}">
                                            {{ $branch->phone ?: $branch->email ?: 'No contact' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="small">{{ $branch->manager_name ?: '—' }}</span>
                                    </td>
                                    <td>
                                        <div class="small"><span class="fw-medium">{{ number_format($branch->employees_count) }}</span> staff</div>
                                        <div class="small text-body-secondary">{{ number_format($branch->departments_count) }} depts</div>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-{{ $branch->is_active ? 'success' : 'secondary' }}">{{ $branch->is_active ? 'Active' : 'Inactive' }}</span>
                                    </td>
                                    <td class="text-end pe-3 text-nowrap">
                                        @can('branch.edit')
                                            <button 
                                                type="button" 
                                                class="btn btn-action-link btn-sm px-2 me-1 js-edit-branch-btn"
                                                data-id="{{ $branch->id }}"
                                                data-name="{{ $branch->name }}"
                                                data-code="{{ $branch->code }}"
                                                data-city="{{ $branch->city }}"
                                                data-manager="{{ $branch->manager_name }}"
                                                data-phone="{{ $branch->phone }}"
                                                data-email="{{ $branch->email }}"
                                                data-address="{{ $branch->address }}"
                                                data-is-head-office="{{ $branch->is_head_office ? '1' : '0' }}"
                                                data-is-active="{{ $branch->is_active ? '1' : '0' }}"
                                                data-update-url="{{ route('branches.update', $branch) }}"
                                                title="Edit {{ $branch->name }}"
                                            >
                                                <i class="fa-solid fa-pen-to-square text-primary"></i><span>Edit</span>
                                            </button>
                                        @endcan

                                        @can('branch.delete')
                                            <form method="POST" action="{{ route('branches.destroy', $branch) }}" class="d-inline" data-confirm="Delete this unreferenced branch?">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-action-link btn-sm text-danger px-2" title="Delete {{ $branch->name }}">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <x-empty-state class="py-5 px-3" icon="fa-building-circle-xmark" title="No branches found" message="Adjust the filters or add a branch using the form on the left." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <x-pagination-footer :paginator="$branches" />
            </div>
        </div>
    </div>

    @push('scripts')
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('branchForm');
                if (!form) return;

                const storeUrl = @json(route('branches.store'));
                const formTitle = document.getElementById('formTitle');
                const resetBtn = document.getElementById('resetBranchFormBtn');
                const methodContainer = document.getElementById('methodSpoofingContainer');
                const saveBtn = document.getElementById('saveBtn');
                const saveAndNewBtn = document.getElementById('saveAndNewBtn');

                // Form Inputs
                const nameInput = document.getElementById('branchName');
                const codeInput = document.getElementById('branchCode');
                const managerInput = document.getElementById('branchManager');
                const cityInput = document.getElementById('branchCity');
                const phoneInput = document.getElementById('branchPhone');
                const emailInput = document.getElementById('branchEmail');
                const addressInput = document.getElementById('branchAddress');
                const headOfficeCheck = document.getElementById('branchIsHeadOffice');
                const activeCheck = document.getElementById('branchIsActive');

                const resetToCreate = () => {
                    form.action = storeUrl;
                    methodContainer.innerHTML = '';
                    form.reset();
                    
                    headOfficeCheck.checked = false;
                    activeCheck.checked = true;

                    formTitle.innerHTML = '<i class="fa-solid fa-circle-plus text-primary me-2"></i>Add branch';
                    saveBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i>Save branch';
                    if (saveAndNewBtn) saveAndNewBtn.classList.remove('d-none');
                    resetBtn.classList.add('d-none');

                    document.querySelectorAll('.branch-row').forEach(r => r.classList.remove('table-primary'));
                    nameInput.focus();
                };

                resetBtn?.addEventListener('click', resetToCreate);

                document.addEventListener('click', (e) => {
                    const btn = e.target.closest('.js-edit-branch-btn');
                    if (!btn) return;

                    const dataset = btn.dataset;

                    // Set form action & spoof PUT method
                    form.action = dataset.updateUrl;
                    methodContainer.innerHTML = '<input type="hidden" name="_method" value="PUT">';

                    // Populate fields
                    nameInput.value = dataset.name || '';
                    codeInput.value = dataset.code || '';
                    managerInput.value = dataset.manager || '';
                    cityInput.value = dataset.city || '';
                    phoneInput.value = dataset.phone || '';
                    emailInput.value = dataset.email || '';
                    addressInput.value = dataset.address || '';
                    
                    headOfficeCheck.checked = dataset.isHeadOffice === '1';
                    activeCheck.checked = dataset.isActive === '1';

                    // Update UI state
                    formTitle.innerHTML = `<i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit branch`;
                    saveBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i>Update branch';
                    if (saveAndNewBtn) saveAndNewBtn.classList.add('d-none');
                    resetBtn.classList.remove('d-none');

                    // Highlight editing row
                    document.querySelectorAll('.branch-row').forEach(r => r.classList.remove('table-primary'));
                    const targetRow = document.getElementById(`rowBranch${dataset.id}`);
                    if (targetRow) targetRow.classList.add('table-primary');

                    nameInput.focus();
                });
            });
        </script>
    @endpush
</x-layouts::app>