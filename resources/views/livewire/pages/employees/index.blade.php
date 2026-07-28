<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">គ្រប់គ្រងបុគ្គលិក</h1>
        <flux:button wire:click="resetForm" variant="primary" icon="plus">
            បង្កើតបុគ្គលិក
        </flux:button>
    </div>

    <!-- Search and Filters -->
    <flux:card>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <flux:input
                wire:model.live="search"
                placeholder="ស្វាគមន៍ employee code, នាម, អ៉ីមែល"
                icon="magnifying-glass"
            />

            <flux:select
                wire:model.live="filterBranch"
                label="សាខា"
            >
                <option value="">ទាំងអស់</option>
                @foreach ($this->filterBranches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </flux:select>

            <flux:select
                wire:model.live="filterDepartment"
                label="ផ្នែក"
            >
                <option value="">ទាំងអស់</option>
                @foreach ($this->filterDepartments as $department)
                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                @endforeach
            </flux:select>

            <flux:select
                wire:model.live="filterStatus"
                label="ស្ថានភាព"
            >
                <option value="">ទាំងអស់</option>
                <option value="Draft">ព្រាង</option>
                <option value="Active">សកម្ម</option>
                <option value="On probation">កំឡុងពេលសាកល្បង</option>
                <option value="On leave">នៅលើ​ឈប់សម្រាក</option>
                <option value="Suspended">ផ្អាក</option>
                <option value="Resigned">ដាច់ឈប់ការងារ</option>
                <option value="Terminated">ឈប់ការងារ</option>
                <option value="Retired">ចូលនិវត្ត</option>
            </flux:select>
        </div>
    </flux:card>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Employees List -->
        <div class="lg:col-span-2">
            <flux:card>
                @if ($this->employees->count())
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">Employee Code</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">នាម</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">ផ្នែក</th>
                                    <th class="px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-white">ស្ថានភាព</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-white">សកម្ម</th>
                                    <th class="px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-white">សកម្មភាព</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($this->employees as $employee)
                                    <tr class="border-b border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="px-4 py-3 text-sm font-mono text-gray-700 dark:text-gray-300">
                                            {{ $employee->employee_code }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                            <div class="font-medium">{{ $employee->first_name }} {{ $employee->last_name }}</div>
                                            @if ($employee->full_name_km)
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $employee->full_name_km }}</div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                            {{ $employee->department->name ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <flux:badge
                                                :variant="match($employee->employment_status) {
                                                    'Active' => 'info',
                                                    'On probation' => 'warning',
                                                    'Resigned' => 'danger',
                                                    'Terminated' => 'danger',
                                                    default => 'default',
                                                }"
                                            >
                                                {{ $employee->employment_status }}
                                            </flux:badge>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <button
                                                wire:click="toggleStatus({{ $employee->id }})"
                                                class="inline-flex items-center justify-center"
                                            >
                                                @if ($employee->is_active)
                                                    <flux:icon.check-circle class="w-5 h-5 text-green-500" />
                                                @else
                                                    <flux:icon.x-circle class="w-5 h-5 text-red-500" />
                                                @endif
                                            </button>
                                        </td>
                                        <td class="px-4 py-3 text-center space-x-2">
                                            <flux:button
                                                variant="ghost"
                                                size="sm"
                                                :href="route('employees.show', $employee)"
                                                wire:navigate
                                                icon="eye"
                                            />
                                            <button
                                                wire:click="edit({{ $employee->id }})"
                                                class="inline-flex items-center justify-center p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded"
                                            >
                                                <flux:icon.pencil class="w-4 h-4 text-blue-500" />
                                            </button>
                                            <flux:button
                                                variant="danger"
                                                size="sm"
                                                wire:click="$dispatch('deleteEmployee', { id: {{ $employee->id }} })"
                                                icon="trash"
                                            >
                                            </flux:button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $this->employees->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <flux:icon.inbox class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                        <p class="text-gray-500 dark:text-gray-400">មិនមានរកដ៏ដែលផ្គូផ្គង</p>
                    </div>
                @endif
            </flux:card>
        </div>

        <!-- Form -->
        <div>
            <flux:card>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                    {{ $this->employeeId ? 'កែប្រែបុគ្គលិក' : 'បង្កើតបុគ្គលិកថ្មី' }}
                </h2>

                <form wire:submit="save" class="space-y-4">
                    <!-- Branch -->
                    <flux:select
                        wire:model="branch_id"
                        label="សាខា"
                        required
                    >
                        @foreach ($this->branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </flux:select>
                    @error('branch_id')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror

                    <!-- Department -->
                    <flux:select
                        wire:model="department_id"
                        label="ផ្នែក"
                        required
                    >
                        @foreach ($this->departments as $department)
                            <option value="{{ $department->id }}">{{ $department->name }}</option>
                        @endforeach
                    </flux:select>
                    @error('department_id')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror

                    <!-- Position -->
                    <flux:select
                        wire:model="position_id"
                        label="មុខតំណែង"
                    >
                        <option value="">-- ជ្រើសរើស --</option>
                        @foreach ($this->positions as $position)
                            <option value="{{ $position->id }}">{{ $position->title }}</option>
                        @endforeach
                    </flux:select>

                    <!-- Employment Type -->
                    <flux:select
                        wire:model="employment_type_id"
                        label="ប្រភេទការងារ"
                    >
                        <option value="">-- ជ្រើសរើស --</option>
                        @foreach ($this->employmentTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                        @endforeach
                    </flux:select>

                    <!-- Employee Code -->
                    <flux:input
                        wire:model="employee_code"
                        label="Employee Code"
                        required
                    />
                    @error('employee_code')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror

                    <!-- First Name -->
                    <flux:input
                        wire:model="first_name"
                        label="នាមត្រកូល"
                        required
                    />
                    @error('first_name')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror

                    <!-- Last Name -->
                    <flux:input
                        wire:model="last_name"
                        label="ឈ្មោះ"
                        required
                    />
                    @error('last_name')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror

                    <!-- Full Name KM -->
                    <flux:input
                        wire:model="full_name_km"
                        label="ឈ្មោះពេញលេញ (ខ្មែរ)"
                    />

                    <!-- Hire Date -->
                    <flux:input
                        type="date"
                        wire:model="hire_date"
                        label="ថ្ងៃលក់ការងារ"
                        required
                    />
                    @error('hire_date')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror

                    <!-- Employment Status -->
                    <flux:select
                        wire:model="employment_status"
                        label="ស្ថានភាព"
                        required
                    >
                        <option value="Active">សកម្ម</option>
                        <option value="Draft">ព្រាង</option>
                        <option value="On probation">កំឡុងពេលសាកល្បង</option>
                        <option value="On leave">នៅលើ​ឈប់សម្រាក</option>
                        <option value="Suspended">ផ្អាក</option>
                        <option value="Resigned">ដាច់ឈប់ការងារ</option>
                        <option value="Terminated">ឈប់ការងារ</option>
                        <option value="Retired">ចូលនិវត្ត</option>
                    </flux:select>

                    <!-- Is Active -->
                    <flux:checkbox
                        wire:model="is_active"
                        label="សកម្ម"
                    />

                    <!-- Buttons -->
                    <div class="flex gap-2 pt-4">
                        <flux:button
                            type="submit"
                            variant="primary"
                            class="flex-1"
                        >
                            {{ $this->employeeId ? 'ធ្វើបច្ចុប្បន្នភាព' : 'បង្កើត' }}
                        </flux:button>
                        @if ($this->employeeId)
                            <flux:button
                                wire:click="resetForm"
                                variant="ghost"
                                class="flex-1"
                            >
                                ដូច
                            </flux:button>
                        @endif
                    </div>
                </form>
            </flux:card>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <flux:modal name="deleteEmployee" variant="danger">
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">លុបបុគ្គលិក?</h2>
            <p class="text-gray-600 dark:text-gray-400">តើអ្នកប្រាកដថាចង់លុបបុគ្គលិកនេះ? សកម្មភាពនេះមិនអាចត្រឡប់វិញបានទេ។</p>
            <div class="flex gap-2 justify-end">
                <flux:button wire:click="$dispatch('closeModal', ['deleteEmployee'])" variant="ghost">ជម្រើស</flux:button>
                <flux:button
                    variant="danger"
                    wire:click="delete($event.detail.id); $dispatch('closeModal', ['deleteEmployee'])"
                >
                    លុប
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
