<x-layouts::app title="Employees">
    <x-workspace-command-bar title="Employee directory" icon="fa-users" context="People">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET" action="{{ route('employees.index') }}">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="search" value="{{ $search }}" placeholder="Search name, code, phone or email" autocomplete="off">
                </div>

                <select class="form-select reference-status" name="branch" aria-label="Filter by branch">
                    <option value="">All branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected($branchId === $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>

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
