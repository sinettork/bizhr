<x-layouts::app title="Employees">
    <x-workspace-command-bar title="Employee directory" icon="fa-users" context="People">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET" action="{{ route('employees.index') }}">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="search" value="{{ $search }}" placeholder="Search name, code, phone or email" autocomplete="off">
                </div>

                @if($branchId > 0)
                    <input type="hidden" name="branch" value="{{ $branchId }}">
                @endif

                <select class="form-select reference-status" name="department" aria-label="Filter by department">
                    <option value="">All departments</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected($departmentId === $department->id)>{{ $department->name }}{{ $department->branch?->name ? ' · '.$department->branch->name : '' }}</option>
                    @endforeach
                </select>

                <select class="form-select reference-status" name="status" aria-label="Filter by employment status">
                    <option value="">All statuses</option>
                    @foreach($statuses as $statusOption)
                        <option value="{{ $statusOption }}" @selected($status === $statusOption)>{{ $statusOption }}</option>
                    @endforeach
                </select>

                <button class="btn btn-primary reference-search-button" type="submit">Filter</button>

                @if(filled($search) || $branchId > 0 || $departmentId > 0 || filled($status))
                    <a class="btn btn-action-link btn-sm" href="{{ route('employees.index') }}"><i class="fa-solid fa-rotate-left"></i><span>Clear</span></a>
                @endif
            </form>
        </x-slot:filters>
        <x-slot:actions>
            <x-list-actions
                add-label="Add employee"
                :add-target="auth()->user()->can('employee.create') ? route('employees.create') : null"
                :show-export="false"
                :show-compact="true"
                :show-columns="true"
            />
        </x-slot:actions>
    </x-workspace-command-bar>

    {{-- KPI Summary Strip --}}
    @if(auth()->user()->can('employee.view'))
        <div class="row g-2 g-md-3 mb-3">
            @php
                $kpis = [
                    [
                        'label'   => 'Active Employees',
                        'value'   => number_format($kpiMetrics['total_active']),
                        'icon'    => 'fa-users',
                        'color'   => 'text-success',
                        'bg'      => '#f0fdf4',
                        'border'  => '#bbf7d0',
                        'href'    => route('employees.index', ['status' => 'Active']),
                    ],
                    [
                        'label'   => 'Inactive / Archived',
                        'value'   => number_format($kpiMetrics['total_inactive']),
                        'icon'    => 'fa-user-slash',
                        'color'   => 'text-secondary',
                        'bg'      => '#f8f9fa',
                        'border'  => '#dee2e6',
                        'href'    => route('employees.index', ['status' => 'Inactive']),
                    ],
                    [
                        'label'   => 'On Leave Today',
                        'value'   => number_format($kpiMetrics['on_leave_today']),
                        'icon'    => 'fa-calendar-minus',
                        'color'   => 'text-info',
                        'bg'      => '#eff6ff',
                        'border'  => '#bfdbfe',
                        'href'    => route('leave.requests.review'),
                    ],
                    [
                        'label'   => 'Probation Ending ≤30d',
                        'value'   => number_format($kpiMetrics['probation_ending']),
                        'icon'    => 'fa-hourglass-half',
                        'color'   => $kpiMetrics['probation_ending'] > 0 ? 'text-warning' : 'text-secondary',
                        'bg'      => $kpiMetrics['probation_ending'] > 0 ? '#fffbeb' : '#f8f9fa',
                        'border'  => $kpiMetrics['probation_ending'] > 0 ? '#fde68a' : '#dee2e6',
                        'href'    => route('employees.index'),
                    ],
                    [
                        'label'   => 'Contract Expiring ≤30d',
                        'value'   => number_format($kpiMetrics['contract_expiring']),
                        'icon'    => 'fa-file-circle-exclamation',
                        'color'   => $kpiMetrics['contract_expiring'] > 0 ? 'text-danger' : 'text-secondary',
                        'bg'      => $kpiMetrics['contract_expiring'] > 0 ? '#fff1f2' : '#f8f9fa',
                        'border'  => $kpiMetrics['contract_expiring'] > 0 ? '#fecdd3' : '#dee2e6',
                        'href'    => route('employees.index'),
                    ],
                ];
            @endphp
            @foreach($kpis as $kpi)
                <div class="col-6 col-md-4 col-lg">
                    <a href="{{ $kpi['href'] }}" class="d-flex align-items-center gap-3 p-3 rounded text-decoration-none employee-kpi-card h-100"
                        style="background:{{ $kpi['bg'] }}; border: 1px solid {{ $kpi['border'] }}; border-radius: .35rem !important; transition: filter 120ms, border-color 120ms;">
                        <span class="{{ $kpi['color'] }}" style="font-size: 1.35rem; width: 36px; text-align:center; flex-shrink:0;">
                            <i class="fa-solid {{ $kpi['icon'] }}"></i>
                        </span>
                        <div class="min-w-0">
                            <div class="fw-bold text-dark lh-1 mb-1" style="font-size: 1.15rem;">{{ $kpi['value'] }}</div>
                            <div class="text-body-secondary lh-1 text-truncate" style="font-size: .68rem; font-weight: 600;">{{ strtoupper($kpi['label']) }}</div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif

    @include('employees._list', ['employees' => $employees])

    @push('scripts')
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            (() => {
                const employeeList = () => document.querySelector('#list-content');

                const selectedRows = () => {
                    const list = employeeList();
                    return list ? [...list.querySelectorAll('.employee-row-select:checked')] : [];
                };

                const syncSelection = () => {
                    const list = employeeList();
                    if (!list) return;

                    const selected = selectedRows();
                    const rowCheckboxes = [...list.querySelectorAll('.employee-row-select')];
                    const selectAll = list.querySelector('.employee-select-all');
                    const bar = list.querySelector('[data-employee-selection-bar]');
                    const count = list.querySelector('[data-employee-selected-count]');

                    if (count) count.textContent = selected.length;
                    if (bar) {
                        bar.classList.toggle('d-none', selected.length === 0);
                        bar.classList.toggle('d-flex', selected.length > 0);
                    }

                    if (selectAll) {
                        selectAll.checked = rowCheckboxes.length > 0 && selected.length === rowCheckboxes.length;
                        selectAll.indeterminate = selected.length > 0 && selected.length < rowCheckboxes.length;
                    }
                };

                const exportSelected = () => {
                    const list = employeeList();
                    const selected = selectedRows();
                    if (!list || selected.length === 0) return;

                    const table = list.querySelector('table');
                    if (!table) return;

                    const headers = [...table.querySelectorAll('thead th')]
                        .slice(1, -1)
                        .map((cell) => `"${cell.innerText.trim().replaceAll('"', '""')}"`);
                    const rows = [headers.join(',')];

                    selected.forEach((checkbox) => {
                        const cells = [...checkbox.closest('tr').querySelectorAll('td')]
                            .slice(1, -1)
                            .map((cell) => `"${cell.innerText.trim().replaceAll('"', '""')}"`);
                        rows.push(cells.join(','));
                    });

                    const file = new Blob([rows.join('\n')], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(file);
                    link.download = `employees-selected-${new Date().toISOString().slice(0, 10)}.csv`;
                    link.click();
                    URL.revokeObjectURL(link.href);
                };

                document.addEventListener('change', (event) => {
                    const list = employeeList();
                    if (!list || !list.contains(event.target)) return;

                    if (event.target.matches('.employee-select-all')) {
                        list.querySelectorAll('.employee-row-select').forEach((checkbox) => {
                            checkbox.checked = event.target.checked;
                        });
                    }

                    if (event.target.matches('.employee-row-select, .employee-select-all')) {
                        syncSelection();
                    }
                });

                document.addEventListener('click', (event) => {
                    const exportButton = event.target.closest('[data-employee-export-selected]');
                    if (exportButton) {
                        exportSelected();
                        return;
                    }

                    const clearButton = event.target.closest('[data-employee-clear-selection]');
                    if (clearButton) {
                        employeeList()?.querySelectorAll('.employee-row-select, .employee-select-all').forEach((checkbox) => {
                            checkbox.checked = false;
                            checkbox.indeterminate = false;
                        });
                        syncSelection();
                    }
                });

                document.body.addEventListener('htmx:afterSwap', syncSelection);
                syncSelection();
            })();
        </script>
    @endpush
</x-layouts::app>
