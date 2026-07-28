<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('គ្រប់គ្រងមុខតំណែង')] class extends Component
{
    public int $companyId = 0;

    public ?int $positionId = null;

    public string $search = '';

    public string $filterBranch = '';

    public string $filterDepartment = '';

    public string $branch_id = '';

    public string $department_id = '';

    public string $title = '';

    public string $code = '';

    public string $description = '';

    public string $minimum_salary = '';

    public string $maximum_salary = '';

    public bool $is_manager_position = false;

    public bool $is_active = true;

    public string $sort_order = '0';

    public function mount(): mixed
    {
        $company = Company::query()->first();

        if (! $company) {
            return $this->redirectRoute(
                'company.settings',
                navigate: true
            );
        }

        $this->companyId = $company->id;

        $this->setDefaultBranchAndDepartment();

        return null;
    }

    protected function rules(): array
    {
        return [
            'branch_id' => [
                'required',
                'integer',

                Rule::exists('branches', 'id')
                    ->where(
                        fn ($query) => $query->where(
                            'company_id',
                            $this->companyId
                        )
                    ),
            ],

            'department_id' => [
                'required',
                'integer',

                Rule::exists('departments', 'id')
                    ->where(
                        fn ($query) => $query
                            ->where(
                                'company_id',
                                $this->companyId
                            )
                            ->where(
                                'branch_id',
                                $this->branch_id
                            )
                    ),
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'required',
                'string',
                'max:50',

                Rule::unique('positions', 'code')
                    ->where(
                        fn ($query) => $query
                            ->where(
                                'company_id',
                                $this->companyId
                            )
                            ->where(
                                'branch_id',
                                $this->branch_id
                            )
                            ->where(
                                'department_id',
                                $this->department_id
                            )
                    )
                    ->ignore($this->positionId),
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'minimum_salary' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'maximum_salary' => [
                'nullable',
                'numeric',
                'min:0',
                'gte:minimum_salary',
            ],

            'is_manager_position' => [
                'boolean',
            ],

            'is_active' => [
                'boolean',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'branch_id.required' =>
                'សូមជ្រើសរើសសាខា។',

            'branch_id.exists' =>
                'សាខាដែលបានជ្រើសរើសមិនត្រឹមត្រូវ។',

            'department_id.required' =>
                'សូមជ្រើសរើសផ្នែក។',

            'department_id.exists' =>
                'ផ្នែកដែលបានជ្រើសរើសមិនត្រឹមត្រូវ។',

            'title.required' =>
                'សូមបញ្ចូលឈ្មោះមុខតំណែង។',

            'title.max' =>
                'ឈ្មោះមុខតំណែងមិនអាចលើសពី ២៥៥ តួអក្សរ។',

            'code.required' =>
                'សូមបញ្ចូលលេខកូដមុខតំណែង។',

            'code.unique' =>
                'លេខកូដមុខតំណែងនេះត្រូវបានប្រើរួចហើយក្នុងផ្នែកនេះ។',

            'code.max' =>
                'លេខកូដមុខតំណែងមិនអាចលើសពី ៥០ តួអក្សរ។',

            'minimum_salary.numeric' =>
                'ប្រាក់ខែអប្បបរមាត្រូវតែជាលេខ។',

            'minimum_salary.min' =>
                'ប្រាក់ខែអប្បបរមាមិនអាចតិចជាង ០ បានទេ។',

            'maximum_salary.numeric' =>
                'ប្រាក់ខែអតិបរមាត្រូវតែជាលេខ។',

            'maximum_salary.min' =>
                'ប្រាក់ខែអតិបរមាមិនអាចតិចជាង ០ បានទេ។',

            'maximum_salary.gte' =>
                'ប្រាក់ខែអតិបរមាត្រូវធំជាង ឬស្មើប្រាក់ខែអប្បបរមា។',

            'sort_order.required' =>
                'សូមបញ្ចូលលំដាប់បង្ហាញ។',

            'sort_order.integer' =>
                'លំដាប់បង្ហាញត្រូវតែជាចំនួនគត់។',

            'sort_order.min' =>
                'លំដាប់បង្ហាញមិនអាចតិចជាង ០ បានទេ។',
        ];
    }

    #[Computed]
    public function branches()
    {
        return Branch::query()
            ->where(
                'company_id',
                $this->companyId
            )
            ->orderByDesc('is_head_office')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function departments()
    {
        return Department::query()
            ->where(
                'company_id',
                $this->companyId
            )
            ->when(
                filled($this->branch_id),
                fn ($query) => $query->where(
                    'branch_id',
                    $this->branch_id
                )
            )
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function filterDepartments()
    {
        return Department::query()
            ->where(
                'company_id',
                $this->companyId
            )
            ->when(
                filled($this->filterBranch),
                fn ($query) => $query->where(
                    'branch_id',
                    $this->filterBranch
                )
            )
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function positions()
    {
        return Position::query()
            ->with([
                'branch:id,name',
                'department:id,name',
            ])
            ->where(
                'company_id',
                $this->companyId
            )
            ->when(
                filled($this->filterBranch),
                fn ($query) => $query->where(
                    'branch_id',
                    $this->filterBranch
                )
            )
            ->when(
                filled($this->filterDepartment),
                fn ($query) => $query->where(
                    'department_id',
                    $this->filterDepartment
                )
            )
            ->when(
                filled($this->search),
                function ($query): void {
                    $search = trim($this->search);

                    $query->where(
                        function ($query) use ($search): void {
                            $query
                                ->where(
                                    'title',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'description',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }

    public function updatedBranchId(): void
    {
        unset($this->departments);

        $this->syncDepartment();
    }

    public function updatedFilterBranch(): void
    {
        $this->filterDepartment = '';

        unset($this->filterDepartments);
        unset($this->positions);
    }

    public function updatedFilterDepartment(): void
    {
        unset($this->positions);
    }

    public function updatedSearch(): void
    {
        unset($this->positions);
    }

    public function save(): void
    {
        $validated = $this->validate();

        $validated['title'] = trim(
            $validated['title']
        );

        $validated['code'] = strtoupper(
            trim($validated['code'])
        );

        $validated['description'] =
            filled($validated['description'])
                ? trim((string) $validated['description'])
                : null;

        $validated['minimum_salary'] =
            filled($validated['minimum_salary'])
                ? $validated['minimum_salary']
                : null;

        $validated['maximum_salary'] =
            filled($validated['maximum_salary'])
                ? $validated['maximum_salary']
                : null;

        $position = $this->positionId !== null
            ? $this->findPosition($this->positionId)
            : new Position();

        $position->fill([
            ...$validated,
            'company_id' => $this->companyId,
        ]);

        $position->save();

        Flux::toast(
            variant: 'success',
            text: $this->positionId !== null
                ? 'បានកែប្រែមុខតំណែងដោយជោគជ័យ។'
                : 'បានបង្កើតមុខតំណែងថ្មីដោយជោគជ័យ។'
        );

        unset($this->positions);

        $this->resetForm();
    }

    public function edit(int $positionId): void
    {
        $position = $this->findPosition(
            $positionId
        );

        $this->positionId = $position->id;

        $this->branch_id =
            (string) $position->branch_id;

        $this->department_id =
            (string) $position->department_id;

        $this->title = $position->title;

        $this->code = $position->code;

        $this->description =
            (string) $position->description;

        $this->minimum_salary =
            $position->minimum_salary !== null
                ? (string) $position->minimum_salary
                : '';

        $this->maximum_salary =
            $position->maximum_salary !== null
                ? (string) $position->maximum_salary
                : '';

        $this->is_manager_position =
            (bool) $position->is_manager_position;

        $this->is_active =
            (bool) $position->is_active;

        $this->sort_order =
            (string) $position->sort_order;

        unset($this->departments);

        $this->resetValidation();
    }

    public function toggleStatus(
        int $positionId
    ): void {
        $position = $this->findPosition(
            $positionId
        );

        $newStatus = ! $position->is_active;

        $position->update([
            'is_active' => $newStatus,
        ]);

        if ($this->positionId === $position->id) {
            $this->is_active = $newStatus;
        }

        unset($this->positions);

        Flux::toast(
            variant: 'success',
            text: $newStatus
                ? 'បានបើកដំណើរការមុខតំណែង។'
                : 'បានបិទដំណើរការមុខតំណែង។'
        );
    }

    public function delete(
        int $positionId
    ): void {
        $position = $this->findPosition(
            $positionId
        );

        try {
            $position->delete();
        } catch (QueryException) {
            Flux::toast(
                variant: 'danger',
                text: 'មិនអាចលុបមុខតំណែងនេះបានទេ ព្រោះមានបុគ្គលិកកំពុងប្រើប្រាស់វា។'
            );

            return;
        }

        if ($this->positionId === $positionId) {
            $this->resetForm();
        }

        unset($this->positions);

        Flux::toast(
            variant: 'success',
            text: 'បានលុបមុខតំណែងដោយជោគជ័យ។'
        );
    }

    public function resetForm(): void
    {
        $currentBranchId = $this->branch_id;

        $this->reset([
            'positionId',
            'department_id',
            'title',
            'code',
            'description',
            'minimum_salary',
            'maximum_salary',
            'is_manager_position',
            'is_active',
            'sort_order',
        ]);

        $this->branch_id = $currentBranchId;

        $this->is_manager_position = false;

        $this->is_active = true;

        $this->sort_order = '0';

        unset($this->departments);

        $this->syncDepartment();

        $this->resetValidation();
    }

    private function findPosition(
        int $positionId
    ): Position {
        return Position::query()
            ->where(
                'company_id',
                $this->companyId
            )
            ->findOrFail($positionId);
    }

    private function setDefaultBranchAndDepartment(): void
    {
        $branch = Branch::query()
            ->where(
                'company_id',
                $this->companyId
            )
            ->orderByDesc('is_head_office')
            ->orderBy('name')
            ->first();

        $this->branch_id = $branch
            ? (string) $branch->id
            : '';

        $this->syncDepartment();
    }

    private function syncDepartment(): void
    {
        if (! filled($this->branch_id)) {
            $this->department_id = '';

            return;
        }

        $departmentExists = Department::query()
            ->where(
                'company_id',
                $this->companyId
            )
            ->where(
                'branch_id',
                $this->branch_id
            )
            ->where(
                'id',
                $this->department_id
            )
            ->exists();

        if ($departmentExists) {
            return;
        }

        $department = Department::query()
            ->where(
                'company_id',
                $this->companyId
            )
            ->where(
                'branch_id',
                $this->branch_id
            )
            ->orderBy('name')
            ->first();

        $this->department_id = $department
            ? (string) $department->id
            : '';
    }
};

?>

<div class="w-full space-y-6 p-4 sm:p-6">

    {{-- Page heading --}}
    <div
        class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
    >
        <div>
            <h1
                class="text-2xl font-semibold text-zinc-900 dark:text-white"
            >
                គ្រប់គ្រងមុខតំណែង
            </h1>

            <p
                class="mt-1 text-zinc-600 dark:text-zinc-300"
            >
                បង្កើត ស្វែងរក កែប្រែ និងគ្រប់គ្រងមុខតំណែងតាមសាខា និងផ្នែក។
            </p>
        </div>

        <flux:button
            type="button"
            variant="ghost"
            icon="plus"
            :href="route('positions.create')"
            wire:navigate
        >
            បង្កើតមុខតំណែងថ្មី
        </flux:button>
    </div>

    @if ($this->branches->isEmpty())

        {{-- No branch warning --}}
        <div
            class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-amber-800 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-200"
        >
            <h2 class="font-semibold">
                មិនទាន់មានសាខា
            </h2>

            <p class="mt-1 text-sm">
                សូមបង្កើតសាខាជាមុនសិន មុនពេលបង្កើតមុខតំណែង។
            </p>

            <flux:button
                class="mt-4"
                variant="primary"
                :href="route('branches.create')"
                wire:navigate
            >
                បង្កើតសាខា
            </flux:button>
        </div>
    @else

        {{-- Main content --}}
        <div
            @class([
                'grid items-start gap-6',
                'xl:grid-cols-[380px_minmax(0,1fr)]' =>
                    $positionId !== null,
            ])
        >
            @if ($positionId !== null)

                {{-- Edit form --}}
                <form
                    wire:submit="save"
                    class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
                >
                    <div>
                        <h2
                            class="text-lg font-semibold text-zinc-900 dark:text-white"
                        >
                            កែប្រែមុខតំណែង
                        </h2>

                        <p
                            class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
                        >
                            កែប្រែព័ត៌មានមុខតំណែងដែលបានជ្រើសរើស។
                        </p>
                    </div>

                    <flux:select
                        wire:model.live="branch_id"
                        label="សាខា"
                        required
                    >
                        <flux:select.option value="">
                            ជ្រើសរើសសាខា
                        </flux:select.option>

                        @foreach ($this->branches as $branch)
                            <flux:select.option
                                value="{{ $branch->id }}"
                            >
                                {{ $branch->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select
                        wire:model="department_id"
                        label="ផ្នែក"
                        required
                    >
                        <flux:select.option value="">
                            ជ្រើសរើសផ្នែក
                        </flux:select.option>

                        @foreach ($this->departments as $department)
                            <flux:select.option
                                value="{{ $department->id }}"
                            >
                                {{ $department->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input
                        wire:model="title"
                        label="ឈ្មោះមុខតំណែង"
                        placeholder="ឧទាហរណ៍៖ អ្នកគ្រប់គ្រងផ្នែក"
                        required
                    />

                    <flux:input
                        wire:model="code"
                        label="លេខកូដមុខតំណែង"
                        placeholder="ឧទាហរណ៍៖ POS001"
                        required
                    />

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input
                            wire:model="minimum_salary"
                            type="number"
                            min="0"
                            step="0.01"
                            label="ប្រាក់ខែអប្បបរមា"
                            placeholder="0.00"
                        />

                        <flux:input
                            wire:model="maximum_salary"
                            type="number"
                            min="0"
                            step="0.01"
                            label="ប្រាក់ខែអតិបរមា"
                            placeholder="0.00"
                        />
                    </div>

                    <flux:input
                        wire:model="sort_order"
                        type="number"
                        min="0"
                        label="លំដាប់បង្ហាញ"
                        placeholder="0"
                        required
                    />

                    <flux:textarea
                        wire:model="description"
                        label="ការពិពណ៌នា"
                        rows="3"
                        placeholder="ពិពណ៌នាអំពីតួនាទី និងការទទួលខុសត្រូវ..."
                    />

                    <div class="space-y-4">
                        <flux:checkbox
                            wire:model="is_manager_position"
                            label="ជាមុខតំណែងគ្រប់គ្រង"
                        />

                        <flux:checkbox
                            wire:model="is_active"
                            label="មុខតំណែងកំពុងដំណើរការ"
                        />
                    </div>

                    <div
                        class="flex flex-wrap gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700"
                    >
                        <flux:button
                            type="submit"
                            variant="primary"
                            icon="check"
                            wire:loading.attr="disabled"
                            wire:target="save"
                        >
                            <span
                                wire:loading.remove
                                wire:target="save"
                            >
                                រក្សាទុក
                            </span>

                            <span
                                wire:loading
                                wire:target="save"
                            >
                                កំពុងរក្សាទុក...
                            </span>
                        </flux:button>

                        <flux:button
                            type="button"
                            variant="ghost"
                            icon="x-mark"
                            wire:click="resetForm"
                        >
                            បោះបង់
                        </flux:button>
                    </div>
                </form>
            @endif

            {{-- Position list --}}
            <div class="min-w-0">
                <div
                    class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
                >
                    {{-- List header --}}
                    <div
                        class="border-b border-zinc-200 p-5 dark:border-zinc-700"
                    >
                        <div
                            class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between"
                        >
                            <div>
                                <h2
                                    class="text-lg font-semibold text-zinc-900 dark:text-white"
                                >
                                    បញ្ជីមុខតំណែង
                                </h2>

                                <p
                                    class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
                                >
                                    ចំនួនមុខតំណែង៖
                                    {{ $this->positions->count() }}
                                </p>
                            </div>

                            <div
                                class="grid w-full gap-3 sm:grid-cols-2 xl:w-auto xl:grid-cols-3"
                            >
                                <div class="w-full xl:w-64">
                                    <flux:input
                                        wire:model.live.debounce.300ms="search"
                                        icon="magnifying-glass"
                                        placeholder="ស្វែងរកមុខតំណែង..."
                                        clearable
                                    />
                                </div>

                                <div class="w-full xl:w-56">
                                    <flux:select
                                        wire:model.live="filterBranch"
                                    >
                                        <flux:select.option value="">
                                            សាខាទាំងអស់
                                        </flux:select.option>

                                        @foreach ($this->branches as $branch)
                                            <flux:select.option
                                                value="{{ $branch->id }}"
                                            >
                                                {{ $branch->name }}
                                            </flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </div>

                                <div class="w-full xl:w-56">
                                    <flux:select
                                        wire:model.live="filterDepartment"
                                    >
                                        <flux:select.option value="">
                                            ផ្នែកទាំងអស់
                                        </flux:select.option>

                                        @foreach (
                                            $this->filterDepartments
                                            as $department
                                        )
                                            <flux:select.option
                                                value="{{ $department->id }}"
                                            >
                                                {{ $department->name }}
                                            </flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Desktop table --}}
                    <div
                        class="hidden overflow-x-auto md:block"
                    >
                        <table
                            class="w-full min-w-[1050px] text-left text-sm"
                        >
                            <thead
                                class="bg-zinc-50 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                            >
                                <tr>
                                    <th class="px-5 py-4 font-medium">
                                        មុខតំណែង
                                    </th>

                                    <th class="px-5 py-4 font-medium">
                                        សាខា / ផ្នែក
                                    </th>

                                    <th class="px-5 py-4 font-medium">
                                        ជួរប្រាក់ខែ
                                    </th>

                                    <th class="px-5 py-4 font-medium">
                                        ប្រភេទ
                                    </th>

                                    <th class="px-5 py-4 font-medium">
                                        ស្ថានភាព
                                    </th>

                                    <th
                                        class="px-5 py-4 text-right font-medium"
                                    >
                                        សកម្មភាព
                                    </th>
                                </tr>
                            </thead>

                            <tbody
                                class="divide-y divide-zinc-200 dark:divide-zinc-700"
                            >
                                @forelse (
                                    $this->positions
                                    as $position
                                )
                                    <tr
                                        wire:key="position-table-{{ $position->id }}"
                                        class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                                    >
                                        <td
                                            class="px-5 py-4 align-top"
                                        >
                                            <div
                                                class="font-medium text-zinc-900 dark:text-white"
                                            >
                                                {{ $position->title }}
                                            </div>

                                            <div
                                                class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                            >
                                                {{ $position->code }}
                                                ·
                                                លំដាប់
                                                {{ $position->sort_order }}
                                            </div>

                                            @if ($position->description)
                                                <div
                                                    class="mt-2 max-w-md text-xs text-zinc-500 dark:text-zinc-400"
                                                >
                                                    {{
                                                        str(
                                                            $position->description
                                                        )->limit(90)
                                                    }}
                                                </div>
                                            @endif
                                        </td>

                                        <td
                                            class="px-5 py-4 align-top"
                                        >
                                            <div
                                                class="text-zinc-800 dark:text-zinc-200"
                                            >
                                                {{
                                                    $position->branch?->name
                                                        ?? 'មិនមានសាខា'
                                                }}
                                            </div>

                                            <div
                                                class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                            >
                                                {{
                                                    $position->department?->name
                                                        ?? 'មិនមានផ្នែក'
                                                }}
                                            </div>
                                        </td>

                                        <td
                                            class="px-5 py-4 align-top text-zinc-700 dark:text-zinc-300"
                                        >
                                            @if (
                                                $position->minimum_salary !== null ||
                                                $position->maximum_salary !== null
                                            )
                                                <div class="whitespace-nowrap">
                                                    ${{ number_format(
                                                        (float) (
                                                            $position->minimum_salary
                                                            ?? 0
                                                        ),
                                                        2
                                                    ) }}

                                                    –

                                                    ${{ number_format(
                                                        (float) (
                                                            $position->maximum_salary
                                                            ?? 0
                                                        ),
                                                        2
                                                    ) }}
                                                </div>
                                            @else
                                                <span class="text-zinc-400">
                                                    មិនទាន់កំណត់
                                                </span>
                                            @endif
                                        </td>

                                        <td
                                            class="px-5 py-4 align-top"
                                        >
                                            @if (
                                                $position->is_manager_position
                                            )
                                                <flux:badge
                                                    size="sm"
                                                    color="blue"
                                                >
                                                    គ្រប់គ្រង
                                                </flux:badge>
                                            @else
                                                <flux:badge
                                                    size="sm"
                                                    color="zinc"
                                                >
                                                    ទូទៅ
                                                </flux:badge>
                                            @endif
                                        </td>

                                        <td
                                            class="px-5 py-4 align-top"
                                        >
                                            @if ($position->is_active)
                                                <flux:badge
                                                    size="sm"
                                                    color="green"
                                                >
                                                    កំពុងដំណើរការ
                                                </flux:badge>
                                            @else
                                                <flux:badge
                                                    size="sm"
                                                    color="zinc"
                                                >
                                                    បានបិទ
                                                </flux:badge>
                                            @endif
                                        </td>

                                        <td
                                            class="px-5 py-4 align-top"
                                        >
                                            <div
                                                class="flex justify-end gap-2"
                                            >
                                                {{-- Edit --}}
                                                <flux:button
                                                    type="button"
                                                    size="sm"
                                                    variant="ghost"
                                                    icon="pencil-square"
                                                    square
                                                    wire:click="edit({{ $position->id }})"
                                                    title="កែប្រែ"
                                                    aria-label="កែប្រែ"
                                                />

                                                {{-- Status --}}
                                                <flux:button
                                                    type="button"
                                                    size="sm"
                                                    variant="ghost"
                                                    :icon="$position->is_active
                                                        ? 'pause-circle'
                                                        : 'play-circle'"
                                                    square
                                                    wire:click="toggleStatus({{ $position->id }})"
                                                    :title="$position->is_active
                                                        ? 'បិទដំណើរការ'
                                                        : 'បើកដំណើរការ'"
                                                    :aria-label="$position->is_active
                                                        ? 'បិទដំណើរការ'
                                                        : 'បើកដំណើរការ'"
                                                />

                                                {{-- Delete --}}
                                                <flux:button
                                                    type="button"
                                                    size="sm"
                                                    variant="danger"
                                                    icon="trash"
                                                    square
                                                    wire:click="delete({{ $position->id }})"
                                                    wire:confirm="តើអ្នកពិតជាចង់លុបមុខតំណែងនេះមែនទេ?"
                                                    title="លុប"
                                                    aria-label="លុប"
                                                />
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td
                                            colspan="6"
                                            class="px-5 py-12 text-center"
                                        >
                                            <div
                                                class="font-medium text-zinc-700 dark:text-zinc-200"
                                            >
                                                មិនមានមុខតំណែង
                                            </div>

                                            <div
                                                class="mt-2 text-sm text-zinc-500 dark:text-zinc-400"
                                            >
                                                សូមបង្កើតមុខតំណែងថ្មី
                                                ឬប្តូរពាក្យស្វែងរក។
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobile cards --}}
                    <div
                        class="divide-y divide-zinc-200 dark:divide-zinc-700 md:hidden"
                    >
                        @forelse (
                            $this->positions
                            as $position
                        )
                            <div
                                wire:key="position-mobile-{{ $position->id }}"
                                class="space-y-4 p-5"
                            >
                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <div class="min-w-0">
                                        <div
                                            class="truncate font-medium text-zinc-900 dark:text-white"
                                        >
                                            {{ $position->title }}
                                        </div>

                                        <div
                                            class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                        >
                                            {{ $position->code }}
                                        </div>
                                    </div>

                                    @if ($position->is_active)
                                        <flux:badge
                                            size="sm"
                                            color="green"
                                        >
                                            ដំណើរការ
                                        </flux:badge>
                                    @else
                                        <flux:badge
                                            size="sm"
                                            color="zinc"
                                        >
                                            បានបិទ
                                        </flux:badge>
                                    @endif
                                </div>

                                <dl class="grid gap-3 text-sm">
                                    <div>
                                        <dt
                                            class="text-xs text-zinc-500 dark:text-zinc-400"
                                        >
                                            សាខា / ផ្នែក
                                        </dt>

                                        <dd
                                            class="mt-1 text-zinc-800 dark:text-zinc-200"
                                        >
                                            {{
                                                $position->branch?->name
                                                    ?? 'មិនមានសាខា'
                                            }}

                                            ·

                                            {{
                                                $position->department?->name
                                                    ?? 'មិនមានផ្នែក'
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-xs text-zinc-500 dark:text-zinc-400"
                                        >
                                            ជួរប្រាក់ខែ
                                        </dt>

                                        <dd
                                            class="mt-1 text-zinc-800 dark:text-zinc-200"
                                        >
                                            @if (
                                                $position->minimum_salary !== null ||
                                                $position->maximum_salary !== null
                                            )
                                                ${{ number_format(
                                                    (float) (
                                                        $position->minimum_salary
                                                        ?? 0
                                                    ),
                                                    2
                                                ) }}

                                                –

                                                ${{ number_format(
                                                    (float) (
                                                        $position->maximum_salary
                                                        ?? 0
                                                    ),
                                                    2
                                                ) }}
                                            @else
                                                មិនទាន់កំណត់
                                            @endif
                                        </dd>
                                    </div>
                                </dl>

                                @if (
                                    $position->is_manager_position
                                )
                                    <flux:badge
                                        size="sm"
                                        color="blue"
                                    >
                                        មុខតំណែងគ្រប់គ្រង
                                    </flux:badge>
                                @endif

                                <div
                                    class="flex justify-end gap-2 border-t border-zinc-100 pt-4 dark:border-zinc-800"
                                >
                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="ghost"
                                        icon="pencil-square"
                                        square
                                        wire:click="edit({{ $position->id }})"
                                        title="កែប្រែ"
                                        aria-label="កែប្រែ"
                                    />

                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="ghost"
                                        :icon="$position->is_active
                                            ? 'pause-circle'
                                            : 'play-circle'"
                                        square
                                        wire:click="toggleStatus({{ $position->id }})"
                                        :title="$position->is_active
                                            ? 'បិទដំណើរការ'
                                            : 'បើកដំណើរការ'"
                                        :aria-label="$position->is_active
                                            ? 'បិទដំណើរការ'
                                            : 'បើកដំណើរការ'"
                                    />

                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="danger"
                                        icon="trash"
                                        square
                                        wire:click="delete({{ $position->id }})"
                                        wire:confirm="តើអ្នកពិតជាចង់លុបមុខតំណែងនេះមែនទេ?"
                                        title="លុប"
                                        aria-label="លុប"
                                    />
                                </div>
                            </div>
                        @empty
                            <div
                                class="px-5 py-12 text-center"
                            >
                                <div
                                    class="font-medium text-zinc-700 dark:text-zinc-200"
                                >
                                    មិនមានមុខតំណែង
                                </div>

                                <div
                                    class="mt-2 text-sm text-zinc-500 dark:text-zinc-400"
                                >
                                    សូមបង្កើតមុខតំណែងថ្មី
                                    ឬប្តូរពាក្យស្វែងរក។
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>