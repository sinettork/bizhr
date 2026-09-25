<x-layouts::app title="Employment types">
    <x-workspace-command-bar title="Employment types" icon="fa-id-card" context="Organization &amp; Compliance">
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
        <x-slot:actions>
            <div class="d-flex align-items-center gap-2">
                @can('employment-type.create')
                    <button type="button" class="btn btn-primary btn-sm px-3" data-bs-toggle="offcanvas" data-bs-target="#typeForm" id="openCreateDrawerBtn">
                        <i class="fa-solid fa-plus me-1"></i><span>Add employment type</span>
                    </button>
                @endcan
            </div>
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

    {{-- Purpose-driven Context & Operational Distribution Strip (Archetype E) --}}
    @php
        $totalTypes = $types->total();
        $totalStaff = $types->sum('employees_count');
        $activeCount = $types->filter(fn($t) => $t->is_active)->count();
    @endphp

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-4">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-body-secondary fw-semibold text-uppercase">Configured Types</div>
                            <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($totalTypes) }}</div>
                        </div>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-body-tertiary text-primary" style="width:38px;height:38px">
                            <i class="fa-solid fa-id-card"></i>
                        </span>
                    </div>
                    <div class="small text-body-secondary mt-2">
                        <span class="badge text-bg-success-subtle text-success border border-success-subtle">{{ $activeCount }} Active</span>
                        @if($totalTypes - $activeCount > 0)
                            <span class="badge text-bg-secondary-subtle text-secondary border border-secondary-subtle ms-1">{{ $totalTypes - $activeCount }} Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="small text-body-secondary fw-semibold text-uppercase">Assigned Staff</div>
                            <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($totalStaff) }}</div>
                        </div>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-body-tertiary text-info" style="width:38px;height:38px">
                            <i class="fa-solid fa-users"></i>
                        </span>
                    </div>
                    <div class="small text-body-secondary mt-2">Active employees linked to classifications</div>
                </div>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-body p-3">
                    <div class="d-flex align-items-start gap-2">
                        <i class="fa-solid fa-circle-info text-primary mt-1"></i>
                        <div>
                            <div class="small fw-semibold text-dark">Policy &amp; Statutory Role</div>
                            <div class="small text-body-secondary mt-1">
                                Worker types define statutory benefit profiles, probation terms, and contract renewal workflows across BizHR.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Full-Width Master Directory Table --}}
    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 250px;">Employment Type</th>
                        <th>Code</th>
                        <th>Description &amp; Policy Notes</th>
                        <th>Staff Coverage</th>
                        <th>Order</th>
                        <th>Status</th>
                        <th class="text-end pe-3" style="min-width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($types as $type)
                        <tr id="rowType{{ $type->id }}" class="type-row">
                            <td class="ps-3">
                                <div class="fw-semibold text-dark">{{ $type->name }}</div>
                            </td>
                            <td>
                                <span class="badge text-bg-light border font-monospace text-uppercase px-2 py-1">{{ $type->code }}</span>
                            </td>
                            <td>
                                <span class="small text-body-secondary">{{ $type->description ?: 'No policy notes specified' }}</span>
                            </td>
                            <td>
                                @if($type->employees_count > 0)
                                    <span class="badge text-bg-primary-subtle text-primary border border-primary-subtle fw-semibold">
                                        <i class="fa-solid fa-user me-1"></i>{{ number_format($type->employees_count) }} staff
                                    </span>
                                @else
                                    <span class="badge text-bg-light border text-body-secondary">
                                        0 staff
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="small font-monospace text-body-secondary">{{ $type->sort_order }}</span>
                            </td>
                            <td>
                                <span class="badge text-bg-{{ $type->is_active ? 'success' : 'secondary' }}">
                                    {{ $type->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                @can('employment-type.edit')
                                    <button 
                                        type="button" 
                                        class="btn btn-action-link btn-sm px-2 me-1 js-edit-type-btn"
                                        data-id="{{ $type->id }}"
                                        data-name="{{ $type->name }}"
                                        data-code="{{ $type->code }}"
                                        data-sort-order="{{ $type->sort_order }}"
                                        data-description="{{ $type->description }}"
                                        data-is-active="{{ $type->is_active ? '1' : '0' }}"
                                        data-update-url="{{ route('employment-types.update', $type) }}"
                                        title="Edit {{ $type->name }}"
                                    >
                                        <i class="fa-solid fa-pen-to-square text-primary"></i><span>Edit</span>
                                    </button>
                                @endcan

                                @can('employment-type.delete')
                                    @if($type->employees_count > 0)
                                        <button type="button" class="btn btn-action-link btn-sm text-body-tertiary px-2" title="Cannot delete: {{ $type->employees_count }} employees currently assigned" disabled>
                                            <i class="fa-solid fa-lock"></i>
                                        </button>
                                    @else
                                        <form method="POST" action="{{ route('employment-types.destroy', $type) }}" class="d-inline" data-confirm="Delete this unreferenced employment type?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-action-link btn-sm text-danger px-2" title="Delete {{ $type->name }}">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state class="py-5 px-3" icon="fa-id-card" title="No employment types configured" message="Add your company's first employment classification using the button above." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$types" />
    </div>

    {{-- Slide-over Offcanvas Drawer: Add / Edit Type (Archetype E) --}}
    @canany(['employment-type.create', 'employment-type.edit'])
        <div class="offcanvas offcanvas-end" tabindex="-1" id="typeForm" aria-labelledby="formTitle" style="width: 440px;">
            <div class="offcanvas-header border-bottom py-3 px-4 bg-body-tertiary">
                <h5 class="offcanvas-title fw-bold text-dark fs-6 d-flex align-items-center" id="formTitle">
                    <i class="fa-solid fa-circle-plus text-primary me-2"></i>Add employment type
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <form id="employmentTypeForm" method="POST" action="{{ route('employment-types.store') }}">
                @csrf
                <div id="methodSpoofingContainer"></div>
                <div class="offcanvas-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark" for="typeName">
                            Classification Name <span class="text-danger">*</span>
                        </label>
                        <input class="form-control" id="typeName" name="name" placeholder="e.g. Full-time, Probation, Contract, Intern" required autocomplete="off">
                        <div class="form-text small text-body-secondary">The primary label visible on employee contracts and payslips.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark" for="typeCode">
                            Classification Code
                        </label>
                        <input class="form-control font-monospace" id="typeCode" name="code" placeholder="Auto-generated if left blank (e.g. FULL-TIME)" autocomplete="off">
                        <div class="form-text small text-body-secondary">Unique system identifier for payroll and reporting.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark" for="typeSortOrder">
                            Display Sort Order
                        </label>
                        <input class="form-control" id="typeSortOrder" type="number" name="sort_order" value="0" min="0">
                        <div class="form-text small text-body-secondary">Order in which this option appears in employee dropdowns.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark" for="typeDescription">
                            Description &amp; Policy Details
                        </label>
                        <textarea class="form-control" id="typeDescription" name="description" rows="3" placeholder="Brief notes on benefits eligibility, notice period policies, or legal classification..."></textarea>
                    </div>

                    <div class="p-3 border rounded-3 bg-body-tertiary mb-3">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" id="typeIsActive" name="is_active" value="1" checked>
                            <label class="form-check-label small fw-semibold text-dark" for="typeIsActive">Active Status</label>
                        </div>
                        <div class="small text-body-secondary mt-1">Inactive types cannot be assigned to new hires or contract renewals.</div>
                    </div>
                </div>
                <div class="offcanvas-footer border-top p-3 px-4 bg-body-tertiary d-flex align-items-center justify-content-between gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="offcanvas">Cancel</button>
                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" name="save_action" value="new" class="btn btn-outline-primary btn-sm" id="saveAndNewBtn">
                            <i class="fa-solid fa-plus me-1"></i>Save &amp; new
                        </button>
                        <button type="submit" name="save_action" value="save" class="btn btn-primary btn-sm px-3" id="saveBtn">
                            <i class="fa-solid fa-check me-1"></i>Save type
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endcanany

    @push('scripts')
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            document.addEventListener('DOMContentLoaded', () => {
                const form = document.getElementById('employmentTypeForm');
                const drawerEl = document.getElementById('typeForm');
                if (!form || !drawerEl) return;

                const drawer = bootstrap.Offcanvas.getOrCreateInstance(drawerEl);
                const storeUrl = @json(route('employment-types.store'));
                const formTitle = document.getElementById('formTitle');
                const methodContainer = document.getElementById('methodSpoofingContainer');
                const saveBtn = document.getElementById('saveBtn');
                const saveAndNewBtn = document.getElementById('saveAndNewBtn');

                const nameInput = document.getElementById('typeName');
                const codeInput = document.getElementById('typeCode');
                const sortOrderInput = document.getElementById('typeSortOrder');
                const descInput = document.getElementById('typeDescription');
                const activeCheck = document.getElementById('typeIsActive');

                const resetToCreate = () => {
                    form.action = storeUrl;
                    methodContainer.innerHTML = '';
                    form.reset();
                    sortOrderInput.value = '0';
                    activeCheck.checked = true;

                    formTitle.innerHTML = '<i class="fa-solid fa-circle-plus text-primary me-2"></i>Add employment type';
                    saveBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i>Save type';
                    if (saveAndNewBtn) saveAndNewBtn.classList.remove('d-none');

                    document.querySelectorAll('.type-row').forEach(r => r.classList.remove('table-primary'));
                };

                document.getElementById('openCreateDrawerBtn')?.addEventListener('click', () => {
                    resetToCreate();
                });

                document.addEventListener('click', (e) => {
                    const btn = e.target.closest('.js-edit-type-btn');
                    if (!btn) return;

                    const id = btn.dataset.id;
                    const name = btn.dataset.name || '';
                    const code = btn.dataset.code || '';
                    const sortOrder = btn.dataset.sortOrder || '0';
                    const description = btn.dataset.description || '';
                    const isActive = btn.dataset.isActive === '1';
                    const updateUrl = btn.dataset.updateUrl;

                    // Set form action & spoof PUT method
                    form.action = updateUrl;
                    methodContainer.innerHTML = '<input type="hidden" name="_method" value="PUT">';

                    // Populate fields
                    nameInput.value = name;
                    codeInput.value = code;
                    sortOrderInput.value = sortOrder;
                    descInput.value = description;
                    activeCheck.checked = isActive;

                    // Update UI state
                    formTitle.innerHTML = `<i class="fa-solid fa-pen-to-square text-primary me-2"></i>Edit employment type`;
                    saveBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i>Update type';
                    if (saveAndNewBtn) saveAndNewBtn.classList.add('d-none');

                    // Highlight editing row
                    document.querySelectorAll('.type-row').forEach(r => r.classList.remove('table-primary'));
                    const targetRow = document.getElementById(`rowType${id}`);
                    if (targetRow) targetRow.classList.add('table-primary');

                    drawer.show();
                    setTimeout(() => nameInput.focus(), 300);
                });
            });
        </script>
    @endpush
</x-layouts::app>
