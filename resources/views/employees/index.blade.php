<x-layouts::app title="Employees">
    <x-workspace-command-bar title="Employee directory" icon="fa-users" context="People">
        <x-slot:filters>
            <x-filter-bar
                searchPlaceholder="Search employee code, name, email"
                :hasStatus="true"
                :statusOptions="$statuses"
                :statusValue="$status"
                hxTarget="#list-content"
            />
        </x-slot:filters>
        <x-slot:actions><x-list-actions add-label="Add employee" :add-target="auth()->user()->can('employee.create') ? route('employees.create') : null" :show-export="auth()->user()->can('employee.view')" :show-compact="true" :show-columns="true" /></x-slot:actions>
    </x-workspace-command-bar>

    <!-- Export dropdown (hidden, populated by JS) -->
    <div id="employeeExportMenu" class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="position: absolute; min-width: 150px; font-size: .8125rem; padding: .25rem;">
        <button class="dropdown-item rounded-2 px-2 py-1" type="button" data-export-type="selected"><i class="fa-solid fa-check-square me-2"></i>Export selected</button>
        <button class="dropdown-item rounded-2 px-2 py-1" type="button" data-export-type="all"><i class="fa-solid fa-download me-2"></i>Export all</button>
    </div>

    <div class="reference-list" data-list-container id="list-content">
        @include('employees._table', ['employees' => $employees])
    </div>

    @push('scripts')
        <script nonce="{{ request()->attributes->get('csp_nonce') }}">
            document.addEventListener('DOMContentLoaded', function () {
                const selectAll = document.querySelector('.employee-select-all');
                const rowCheckboxes = document.querySelectorAll('.employee-row-select');

                if (!selectAll || rowCheckboxes.length === 0) {
                    return;
                }

                selectAll.addEventListener('change', function () {
                    rowCheckboxes.forEach(function (checkbox) {
                        checkbox.checked = selectAll.checked;
                    });
                });

                rowCheckboxes.forEach(function (checkbox) {
                    checkbox.addEventListener('change', function () {
                        const allChecked = Array.from(rowCheckboxes).every(function (item) {
                            return item.checked;
                        });

                        selectAll.checked = allChecked;
                    });
                });
            });
        </script>
    @endpush
</x-layouts::app>
