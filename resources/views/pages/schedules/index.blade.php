<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\WorkShift;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('កាលវិភាគបុគ្គលិក')] class extends Component
{
    use WithPagination;

    public int $companyId;

    public ?int $scheduleId = null;

    public bool $showForm = false;

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    public string $search = '';

    public string $filterBranch = '';

    public string $filterShift = '';

    public string $filterDate = '';

    public string $filterRestDay = '';

    /*
    |--------------------------------------------------------------------------
    | Form fields
    |--------------------------------------------------------------------------
    */

    public string $employee_id = '';

    public string $work_shift_id = '';

    public string $work_date = '';

    public bool $is_rest_day = false;

    public string $notes = '';

    public function mount(): void
    {
        $company = Company::query()->firstOrFail();

        $this->companyId = $company->id;

        $this->work_date = now()->format('Y-m-d');
    }

    /*
    |--------------------------------------------------------------------------
    | Livewire hooks
    |--------------------------------------------------------------------------
    */

    public function updated(
        string $property,
        mixed $value
    ): void {
        if (
            in_array(
                $property,
                [
                    'search',
                    'filterBranch',
                    'filterShift',
                    'filterDate',
                    'filterRestDay',
                ],
                true
            )
        ) {
            $this->resetPage();

            unset($this->schedules);
        }

        if ($property === 'employee_id') {
            unset($this->selectedEmployee);

            $this->resetValidation('employee_id');
        }

        if (
            $property === 'is_rest_day'
            && $value
        ) {
            $this->work_shift_id = '';

            $this->resetValidation(
                'work_shift_id'
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    protected function rules(): array
    {
        return [
            'employee_id' => [
                'required',
                'integer',

                Rule::exists('employees', 'id')
                    ->where(
                        fn ($query) => $query
                            ->where(
                                'company_id',
                                $this->companyId
                            )
                            ->whereNull('deleted_at')
                    ),
            ],

            'work_shift_id' => [
                Rule::requiredIf(
                    fn () => ! $this->is_rest_day
                ),

                'nullable',
                'integer',

                Rule::exists('work_shifts', 'id')
                    ->where(
                        fn ($query) => $query
                            ->where(
                                'company_id',
                                $this->companyId
                            )
                    ),
            ],

            'work_date' => [
                'required',
                'date',

                Rule::unique(
                    'employee_schedules',
                    'work_date'
                )
                    ->where(
                        fn ($query) => $query
                            ->where(
                                'employee_id',
                                $this->employee_id
                            )
                    )
                    ->ignore($this->scheduleId),
            ],

            'is_rest_day' => [
                'boolean',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'employee_id.required' =>
                'សូមជ្រើសរើសបុគ្គលិក។',

            'employee_id.exists' =>
                'បុគ្គលិកដែលបានជ្រើសរើសមិនត្រឹមត្រូវ។',

            'work_shift_id.required' =>
                'សូមជ្រើសរើសវេនការងារ។',

            'work_shift_id.exists' =>
                'វេនការងារដែលបានជ្រើសរើសមិនត្រឹមត្រូវ។',

            'work_date.required' =>
                'សូមបញ្ចូលថ្ងៃធ្វើការ។',

            'work_date.date' =>
                'កាលបរិច្ឆេទមិនត្រឹមត្រូវ។',

            'work_date.unique' =>
                'បុគ្គលិកនេះមានកាលវិភាគសម្រាប់ថ្ងៃនេះរួចហើយ។',

            'notes.max' =>
                'កំណត់សម្គាល់មិនអាចលើសពី ១,០០០ តួអក្សរ។',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Options
    |--------------------------------------------------------------------------
    */

    #[Computed]
    public function branches()
    {
        return Branch::query()
            ->where(
                'company_id',
                $this->companyId
            )
            ->where('is_active', true)
            ->orderByDesc('is_head_office')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function employees()
    {
        return Employee::query()
            ->with([
                'branch',
                'department',
                'position',
            ])
            ->where(
                'company_id',
                $this->companyId
            )
            ->orderByDesc('is_active')
            ->orderBy('employee_code')
            ->get();
    }

    #[Computed]
    public function shifts()
    {
        return WorkShift::query()
            ->where(
                'company_id',
                $this->companyId
            )
            ->orderByDesc('is_active')
            ->orderBy('start_time')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function selectedEmployee(): ?Employee
    {
        if (! $this->employee_id) {
            return null;
        }

        return Employee::query()
            ->with([
                'branch',
                'department',
                'position',
            ])
            ->where(
                'company_id',
                $this->companyId
            )
            ->find($this->employee_id);
    }

    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

    #[Computed]
    public function statistics(): array
    {
        $query = EmployeeSchedule::query()
            ->whereHas(
                'employee',
                fn ($employeeQuery) =>
                    $employeeQuery->where(
                        'company_id',
                        $this->companyId
                    )
            );

        return [
            'total' => (clone $query)->count(),

            'today' => (clone $query)
                ->whereDate(
                    'work_date',
                    today()
                )
                ->count(),

            'working_today' => (clone $query)
                ->whereDate(
                    'work_date',
                    today()
                )
                ->where(
                    'is_rest_day',
                    false
                )
                ->count(),

            'rest_today' => (clone $query)
                ->whereDate(
                    'work_date',
                    today()
                )
                ->where(
                    'is_rest_day',
                    true
                )
                ->count(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Schedule list
    |--------------------------------------------------------------------------
    */

    #[Computed]
    public function schedules()
    {
        return EmployeeSchedule::query()
            ->with([
                'employee.branch',
                'employee.department',
                'employee.position',
                'branch',
                'workShift',
            ])

            ->whereHas(
                'employee',
                fn ($employeeQuery) =>
                    $employeeQuery->where(
                        'company_id',
                        $this->companyId
                    )
            )

            ->when(
                filled($this->search),
                function ($query): void {
                    $search = trim($this->search);

                    $query->whereHas(
                        'employee',
                        function ($employeeQuery) use (
                            $search
                        ): void {
                            $employeeQuery->where(
                                function ($nameQuery) use (
                                    $search
                                ): void {
                                    $nameQuery
                                        ->where(
                                            'employee_code',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'first_name',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'last_name',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'full_name_km',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'full_name_en',
                                            'like',
                                            "%{$search}%"
                                        );
                                }
                            );
                        }
                    );
                }
            )

            ->when(
                filled($this->filterBranch),
                fn ($query) => $query->where(
                    'branch_id',
                    $this->filterBranch
                )
            )

            ->when(
                filled($this->filterShift),
                fn ($query) => $query->where(
                    'work_shift_id',
                    $this->filterShift
                )
            )

            ->when(
                filled($this->filterDate),
                fn ($query) => $query->whereDate(
                    'work_date',
                    $this->filterDate
                )
            )

            ->when(
                $this->filterRestDay !== '',
                fn ($query) => $query->where(
                    'is_rest_day',
                    $this->filterRestDay === '1'
                )
            )

            ->orderByDesc('work_date')
            ->orderBy('branch_id')
            ->orderBy('employee_id')
            ->paginate(15);
    }

    /*
    |--------------------------------------------------------------------------
    | Form actions
    |--------------------------------------------------------------------------
    */

    public function openCreateForm(): void
    {
        $this->authorizeEditing();

        $this->resetForm();

        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->resetForm();

        $this->showForm = false;
    }

    public function save(): void
    {
        $this->authorizeEditing();

        $validated = $this->validate();

        $employee = $this->findEmployee(
            (int) $validated['employee_id']
        );

        $validated['employee_id'] =
            $employee->id;

        $validated['branch_id'] =
            $employee->branch_id;

        $validated['work_shift_id'] =
            $validated['is_rest_day']
                ? null
                : (int) $validated['work_shift_id'];

        $validated['is_rest_day'] =
            (bool) $validated['is_rest_day'];

        $validated['notes'] =
            filled($validated['notes'])
                ? trim($validated['notes'])
                : null;

        if ($this->scheduleId !== null) {
            $schedule = $this->findSchedule(
                $this->scheduleId
            );

            $schedule->update($validated);

            session()->flash(
                'status',
                'បានកែប្រែកាលវិភាគដោយជោគជ័យ។'
            );
        } else {
            EmployeeSchedule::query()
                ->create($validated);

            session()->flash(
                'status',
                'បានបង្កើតកាលវិភាគថ្មីដោយជោគជ័យ។'
            );
        }

        unset($this->schedules);
        unset($this->statistics);
        unset($this->selectedEmployee);

        $this->resetPage();
        $this->resetForm();

        $this->showForm = false;
    }

    public function edit(
        int $scheduleId
    ): void {
        $this->authorizeEditing();

        $schedule = $this->findSchedule(
            $scheduleId
        );

        $this->scheduleId =
            $schedule->id;

        $this->employee_id =
            (string) $schedule->employee_id;

        $this->work_shift_id =
            $schedule->work_shift_id
                ? (string) $schedule->work_shift_id
                : '';

        $this->work_date =
            $schedule->work_date->format(
                'Y-m-d'
            );

        $this->is_rest_day =
            (bool) $schedule->is_rest_day;

        $this->notes =
            (string) $schedule->notes;

        unset($this->selectedEmployee);

        $this->resetValidation();

        $this->showForm = true;
    }

    public function delete(
        int $scheduleId
    ): void {
        $this->authorizeEditing();

        $schedule = $this->findSchedule(
            $scheduleId
        );

        $schedule->delete();

        if (
            $this->scheduleId
            === $scheduleId
        ) {
            $this->resetForm();

            $this->showForm = false;
        }

        unset($this->schedules);
        unset($this->statistics);

        $this->resetPage();

        session()->flash(
            'status',
            'បានលុបកាលវិភាគដោយជោគជ័យ។'
        );
    }

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'filterBranch',
            'filterShift',
            'filterDate',
            'filterRestDay',
        ]);

        unset($this->schedules);

        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset([
            'scheduleId',
            'employee_id',
            'work_shift_id',
            'work_date',
            'is_rest_day',
            'notes',
        ]);

        $this->work_date =
            now()->format('Y-m-d');

        $this->is_rest_day = false;

        unset($this->selectedEmployee);

        $this->resetValidation();
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function authorizeEditing(): void
    {
        abort_unless(
            auth()->user()?->can(
                'schedule.edit'
            ),
            403
        );
    }

    private function findEmployee(
        int $employeeId
    ): Employee {
        return Employee::query()
            ->where(
                'company_id',
                $this->companyId
            )
            ->findOrFail($employeeId);
    }

    private function findSchedule(
        int $scheduleId
    ): EmployeeSchedule {
        return EmployeeSchedule::query()
            ->whereHas(
                'employee',
                fn ($employeeQuery) =>
                    $employeeQuery->where(
                        'company_id',
                        $this->companyId
                    )
            )
            ->findOrFail($scheduleId);
    }
};
?>

<div class="w-full space-y-6 p-4 sm:p-6">
    {{-- Header --}}
    <div
        class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
    >
        <div>
            <h1
                class="text-2xl font-semibold text-zinc-900 dark:text-white"
            >
                កាលវិភាគបុគ្គលិក
            </h1>

            <p
                class="mt-1 text-zinc-600 dark:text-zinc-300"
            >
                កំណត់វេនធ្វើការ ថ្ងៃសម្រាក
                និងកាលវិភាគប្រចាំថ្ងៃរបស់បុគ្គលិក។
            </p>
        </div>

        @can('schedule.edit')
            <flux:button
                type="button"
                variant="primary"
                icon="plus"
                wire:click="openCreateForm"
            >
                បន្ថែមកាលវិភាគ
            </flux:button>
        @endcan
    </div>

    {{-- Messages --}}
    @if (session('status'))
        <div
            class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800 dark:border-green-800 dark:bg-green-950/30 dark:text-green-200"
        >
            {{ session('status') }}
        </div>
    @endif

    {{-- Statistics --}}
    <div
        class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
    >
        <div
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500">
                កាលវិភាគសរុប
            </p>

            <p
                class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white"
            >
                {{ number_format(
                    $this->statistics['total']
                ) }}
            </p>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500">
                កាលវិភាគថ្ងៃនេះ
            </p>

            <p
                class="mt-2 text-3xl font-semibold text-blue-600"
            >
                {{ number_format(
                    $this->statistics['today']
                ) }}
            </p>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500">
                ធ្វើការថ្ងៃនេះ
            </p>

            <p
                class="mt-2 text-3xl font-semibold text-green-600"
            >
                {{ number_format(
                    $this->statistics[
                        'working_today'
                    ]
                ) }}
            </p>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500">
                សម្រាកថ្ងៃនេះ
            </p>

            <p
                class="mt-2 text-3xl font-semibold text-amber-600"
            >
                {{ number_format(
                    $this->statistics[
                        'rest_today'
                    ]
                ) }}
            </p>
        </div>
    </div>

    {{-- Create/Edit form --}}
    @if ($showForm)
        <form
            wire:submit="save"
            class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6"
        >
            <div
                class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2
                        class="text-lg font-semibold text-zinc-900 dark:text-white"
                    >
                        {{ $scheduleId
                            ? 'កែប្រែកាលវិភាគ'
                            : 'បន្ថែមកាលវិភាគថ្មី' }}
                    </h2>

                    <p
                        class="mt-1 text-sm text-zinc-500"
                    >
                        បុគ្គលិកម្នាក់អាចមានកាលវិភាគ
                        តែមួយប៉ុណ្ណោះក្នុងមួយថ្ងៃ។
                    </p>
                </div>

                <flux:button
                    type="button"
                    variant="ghost"
                    wire:click="cancelForm"
                >
                    បិទទម្រង់
                </flux:button>
            </div>

            <div
                class="grid gap-5 md:grid-cols-2 xl:grid-cols-4"
            >
                <flux:select
                    wire:model.live="employee_id"
                    label="បុគ្គលិក"
                    required
                >
                    <flux:select.option value="">
                        ជ្រើសរើសបុគ្គលិក
                    </flux:select.option>

                    @foreach (
                        $this->employees
                        as $employeeOption
                    )
                        @php
                            $optionName =
                                $employeeOption->full_name_km
                                ?: $employeeOption->full_name_en
                                ?: trim(
                                    $employeeOption->first_name
                                    . ' '
                                    . $employeeOption->last_name
                                );
                        @endphp

                        <flux:select.option
                            value="{{ $employeeOption->id }}"
                        >
                            {{ $employeeOption->employee_code }}
                            —
                            {{ $optionName }}

                            @if (! $employeeOption->is_active)
                                (បានបិទ)
                            @endif
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input
                    wire:model="work_date"
                    type="date"
                    label="ថ្ងៃកាលវិភាគ"
                    required
                />

                <flux:select
                    wire:model="work_shift_id"
                    label="វេនការងារ"
                    :disabled="$is_rest_day"
                >
                    <flux:select.option value="">
                        {{ $is_rest_day
                            ? 'ថ្ងៃសម្រាក'
                            : 'ជ្រើសរើសវេន' }}
                    </flux:select.option>

                    @foreach (
                        $this->shifts
                        as $shiftOption
                    )
                        <flux:select.option
                            value="{{ $shiftOption->id }}"
                        >
                            {{ $shiftOption->name }}
                            —
                            {{ substr(
                                $shiftOption->start_time,
                                0,
                                5
                            ) }}
                            -
                            {{ substr(
                                $shiftOption->end_time,
                                0,
                                5
                            ) }}

                            @if (! $shiftOption->is_active)
                                (បានបិទ)
                            @endif
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <div
                    class="flex items-end pb-2"
                >
                    <flux:checkbox
                        wire:model.live="is_rest_day"
                        label="កំណត់ជាថ្ងៃសម្រាក"
                    />
                </div>
            </div>

            @if ($this->selectedEmployee)
                <div
                    class="mt-5 grid gap-4 rounded-xl bg-zinc-50 p-4 text-sm sm:grid-cols-3 dark:bg-zinc-800"
                >
                    <div>
                        <p class="text-zinc-500">
                            សាខា
                        </p>

                        <p
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $this
                                ->selectedEmployee
                                ->branch?->name
                                ?? 'មិនទាន់កំណត់' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-zinc-500">
                            ផ្នែក
                        </p>

                        <p
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $this
                                ->selectedEmployee
                                ->department?->name
                                ?? 'មិនទាន់កំណត់' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-zinc-500">
                            មុខតំណែង
                        </p>

                        <p
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $this
                                ->selectedEmployee
                                ->position?->title
                                ?? 'មិនទាន់កំណត់' }}
                        </p>
                    </div>
                </div>
            @endif

            @if ($is_rest_day)
                <div
                    class="mt-5 rounded-xl bg-amber-50 p-4 text-sm text-amber-800 dark:bg-amber-950/30 dark:text-amber-200"
                >
                    កាលវិភាគនេះនឹងត្រូវកត់ត្រាជាថ្ងៃសម្រាក
                    ហើយមិនត្រូវការជ្រើសរើសវេនការងារទេ។
                </div>
            @endif

            <div class="mt-5">
                <flux:textarea
                    wire:model="notes"
                    label="កំណត់សម្គាល់"
                    placeholder="ព័ត៌មានបន្ថែមអំពីកាលវិភាគ..."
                    rows="3"
                />
            </div>

            <div
                class="mt-6 flex flex-wrap justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700"
            >
                <flux:button
                    type="button"
                    variant="ghost"
                    wire:click="cancelForm"
                >
                    បោះបង់
                </flux:button>

                <flux:button
                    type="submit"
                    variant="primary"
                    wire:loading.attr="disabled"
                    wire:target="save"
                >
                    <span
                        wire:loading.remove
                        wire:target="save"
                    >
                        {{ $scheduleId
                            ? 'រក្សាទុកការកែប្រែ'
                            : 'បង្កើតកាលវិភាគ' }}
                    </span>

                    <span
                        wire:loading
                        wire:target="save"
                    >
                        កំពុងរក្សាទុក...
                    </span>
                </flux:button>
            </div>
        </form>
    @endif

    {{-- Schedule list --}}
    <div
        class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
    >
        {{-- Filters --}}
        <div
            class="border-b border-zinc-200 p-5 dark:border-zinc-700"
        >
            <div
                class="grid gap-3 md:grid-cols-2 xl:grid-cols-[2fr_1fr_1fr_1fr_1fr_auto]"
            >
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    icon="magnifying-glass"
                    placeholder="ស្វែងរកបុគ្គលិក ឬលេខកូដ..."
                    clearable
                />

                <flux:select
                    wire:model.live="filterBranch"
                >
                    <flux:select.option value="">
                        សាខាទាំងអស់
                    </flux:select.option>

                    @foreach (
                        $this->branches
                        as $branch
                    )
                        <flux:select.option
                            value="{{ $branch->id }}"
                        >
                            {{ $branch->name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select
                    wire:model.live="filterShift"
                >
                    <flux:select.option value="">
                        វេនទាំងអស់
                    </flux:select.option>

                    @foreach (
                        $this->shifts
                        as $shift
                    )
                        <flux:select.option
                            value="{{ $shift->id }}"
                        >
                            {{ $shift->name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input
                    wire:model.live="filterDate"
                    type="date"
                />

                <flux:select
                    wire:model.live="filterRestDay"
                >
                    <flux:select.option value="">
                        ប្រភេទថ្ងៃទាំងអស់
                    </flux:select.option>

                    <flux:select.option value="0">
                        ថ្ងៃធ្វើការ
                    </flux:select.option>

                    <flux:select.option value="1">
                        ថ្ងៃសម្រាក
                    </flux:select.option>
                </flux:select>

                <flux:button
                    type="button"
                    variant="ghost"
                    icon="x-mark"
                    wire:click="clearFilters"
                >
                    សម្អាត
                </flux:button>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead
                    class="bg-zinc-50 dark:bg-zinc-800"
                >
                    <tr>
                        <th
                            class="px-5 py-4 font-medium"
                        >
                            ថ្ងៃ
                        </th>

                        <th
                            class="px-5 py-4 font-medium"
                        >
                            បុគ្គលិក
                        </th>

                        <th
                            class="px-5 py-4 font-medium"
                        >
                            សាខា និងផ្នែក
                        </th>

                        <th
                            class="px-5 py-4 font-medium"
                        >
                            វេនការងារ
                        </th>

                        <th
                            class="px-5 py-4 font-medium"
                        >
                            ប្រភេទថ្ងៃ
                        </th>

                        <th
                            class="px-5 py-4 font-medium"
                        >
                            កំណត់សម្គាល់
                        </th>

                        @can('schedule.edit')
                            <th
                                class="px-5 py-4 text-right font-medium"
                            >
                                សកម្មភាព
                            </th>
                        @endcan
                    </tr>
                </thead>

                <tbody
                    class="divide-y divide-zinc-200 dark:divide-zinc-700"
                >
                    @forelse (
                        $this->schedules
                        as $schedule
                    )
                        @php
                            $employeeName =
                                $schedule
                                    ->employee
                                    ->full_name_km
                                ?: $schedule
                                    ->employee
                                    ->full_name_en
                                ?: trim(
                                    $schedule
                                        ->employee
                                        ->first_name
                                    . ' '
                                    . $schedule
                                        ->employee
                                        ->last_name
                                );

                            $isToday =
                                $schedule
                                    ->work_date
                                    ->isToday();

                            $isPast =
                                $schedule
                                    ->work_date
                                    ->isBefore(today());

                            $dayLabel = match (
                                $schedule
                                    ->work_date
                                    ->dayOfWeek
                            ) {
                                0 => 'អាទិត្យ',
                                1 => 'ចន្ទ',
                                2 => 'អង្គារ',
                                3 => 'ពុធ',
                                4 => 'ព្រហស្បតិ៍',
                                5 => 'សុក្រ',
                                6 => 'សៅរ៍',
                            };
                        @endphp

                        <tr
                            wire:key="employee-schedule-{{ $schedule->id }}"
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        >
                            <td class="px-5 py-4">
                                <div
                                    class="font-medium text-zinc-900 dark:text-white"
                                >
                                    {{ $schedule
                                        ->work_date
                                        ->format('d/m/Y') }}
                                </div>

                                <div
                                    class="mt-1 text-xs text-zinc-500"
                                >
                                    {{ $dayLabel }}

                                    @if ($isToday)
                                        · ថ្ងៃនេះ
                                    @elseif ($isPast)
                                        · បានកន្លងផុត
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div
                                    class="font-medium text-zinc-900 dark:text-white"
                                >
                                    {{ $employeeName }}
                                </div>

                                <div
                                    class="mt-1 font-mono text-xs text-zinc-500"
                                >
                                    {{ $schedule
                                        ->employee
                                        ->employee_code }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div>
                                    {{ $schedule
                                        ->branch?->name
                                        ?? '—' }}
                                </div>

                                <div
                                    class="mt-1 text-xs text-zinc-500"
                                >
                                    {{ $schedule
                                        ->employee
                                        ->department?->name
                                        ?? 'មិនមានផ្នែក' }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if (
                                    $schedule->is_rest_day
                                )
                                    <span
                                        class="text-zinc-500"
                                    >
                                        មិនមានវេន
                                    </span>
                                @elseif (
                                    $schedule->workShift
                                )
                                    <div
                                        class="font-medium text-zinc-900 dark:text-white"
                                    >
                                        {{ $schedule
                                            ->workShift
                                            ->name }}
                                    </div>

                                    <div
                                        class="mt-1 text-xs text-zinc-500"
                                    >
                                        {{ substr(
                                            $schedule
                                                ->workShift
                                                ->start_time,
                                            0,
                                            5
                                        ) }}

                                        –

                                        {{ substr(
                                            $schedule
                                                ->workShift
                                                ->end_time,
                                            0,
                                            5
                                        ) }}
                                    </div>
                                @else
                                    <span
                                        class="text-red-600"
                                    >
                                        វេនត្រូវបានលុប
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                @if (
                                    $schedule->is_rest_day
                                )
                                    <flux:badge
                                        size="sm"
                                        color="amber"
                                    >
                                        ថ្ងៃសម្រាក
                                    </flux:badge>
                                @else
                                    <flux:badge
                                        size="sm"
                                        color="green"
                                    >
                                        ថ្ងៃធ្វើការ
                                    </flux:badge>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <p
                                    class="max-w-52 truncate text-zinc-600 dark:text-zinc-300"
                                    title="{{ $schedule->notes }}"
                                >
                                    {{ $schedule->notes
                                        ?: '—' }}
                                </p>
                            </td>

                            @can('schedule.edit')
                                <td class="px-5 py-4">
                                    <div
                                        class="flex justify-end gap-1"
                                    >
                                        <flux:button
                                            type="button"
                                            size="sm"
                                            variant="ghost"
                                            icon="pencil-square"
                                            square
                                            wire:click="edit({{ $schedule->id }})"
                                            title="កែប្រែ"
                                        />

                                        <flux:button
                                            type="button"
                                            size="sm"
                                            variant="danger"
                                            icon="trash"
                                            square
                                            wire:click="delete({{ $schedule->id }})"
                                            wire:confirm="តើអ្នកពិតជាចង់លុបកាលវិភាគនេះមែនទេ?"
                                            title="លុប"
                                        />
                                    </div>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="7"
                                class="px-5 py-14 text-center"
                            >
                                <div
                                    class="font-medium text-zinc-700 dark:text-zinc-200"
                                >
                                    មិនទាន់មានកាលវិភាគ
                                </div>

                                <p
                                    class="mt-2 text-sm text-zinc-500"
                                >
                                    បន្ថែមវេនការងារ
                                    ឬថ្ងៃសម្រាកសម្រាប់បុគ្គលិក។
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->schedules->hasPages())
            <div
                class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-700"
            >
                {{ $this->schedules->links() }}
            </div>
        @endif
    </div>
</div>