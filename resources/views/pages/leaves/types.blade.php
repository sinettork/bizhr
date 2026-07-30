<?php

use App\Models\Company;
use App\Models\LeaveType;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('ប្រភេទការឈប់សម្រាក')] class extends Component
{
    use WithPagination;

    public int $companyId = 0;

    public ?int $leaveTypeId = null;

    public bool $showForm = false;

    public string $search = '';

    public string $filterStatus = '';

    public string $name = '';

    public string $code = '';

    public string $days_per_year = '0';

    public bool $is_paid = true;

    public bool $requires_attachment = false;

    public bool $carry_forward_allowed = false;

    public string $maximum_carry_forward_days = '0';

    public bool $is_active = true;

    public function mount(): void
    {
        abort_unless(
            auth()->user()?->can('leave.manage'),
            403
        );

        $company = Company::query()->firstOrFail();

        $this->companyId = $company->id;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();

        unset($this->leaveTypes);
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();

        unset($this->leaveTypes);
    }

    public function updatedCarryForwardAllowed(
        bool $allowed
    ): void {
        if (! $allowed) {
            $this->maximum_carry_forward_days = '0';
        }

        $this->resetValidation(
            'maximum_carry_forward_days'
        );
    }

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'required',
                'string',
                'max:50',

                Rule::unique('leave_types', 'code')
                    ->where(
                        fn ($query) => $query->where(
                            'company_id',
                            $this->companyId
                        )
                    )
                    ->ignore($this->leaveTypeId),
            ],

            'days_per_year' => [
                'required',
                'numeric',
                'min:0',
                'max:365',
            ],

            'is_paid' => [
                'boolean',
            ],

            'requires_attachment' => [
                'boolean',
            ],

            'carry_forward_allowed' => [
                'boolean',
            ],

            'maximum_carry_forward_days' => [
                Rule::requiredIf(
                    fn () => $this->carry_forward_allowed
                ),
                'numeric',
                'min:0',
                'max:365',
            ],

            'is_active' => [
                'boolean',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' =>
                'សូមបញ្ចូលឈ្មោះប្រភេទការឈប់សម្រាក។',

            'name.max' =>
                'ឈ្មោះប្រភេទការឈប់សម្រាកមិនអាចលើសពី ២៥៥ តួអក្សរ។',

            'code.required' =>
                'សូមបញ្ចូលលេខកូដ។',

            'code.unique' =>
                'លេខកូដនេះត្រូវបានប្រើរួចហើយ។',

            'code.max' =>
                'លេខកូដមិនអាចលើសពី ៥០ តួអក្សរ។',

            'days_per_year.required' =>
                'សូមបញ្ចូលចំនួនថ្ងៃប្រចាំឆ្នាំ។',

            'days_per_year.numeric' =>
                'ចំនួនថ្ងៃប្រចាំឆ្នាំត្រូវតែជាលេខ។',

            'days_per_year.min' =>
                'ចំនួនថ្ងៃមិនអាចតិចជាងសូន្យ។',

            'days_per_year.max' =>
                'ចំនួនថ្ងៃមិនអាចលើសពី ៣៦៥ ថ្ងៃ។',

            'maximum_carry_forward_days.required' =>
                'សូមបញ្ចូលចំនួនថ្ងៃអតិបរមាដែលអាចយោងទៅឆ្នាំក្រោយ។',

            'maximum_carry_forward_days.numeric' =>
                'ចំនួនថ្ងៃយោងត្រូវតែជាលេខ។',

            'maximum_carry_forward_days.min' =>
                'ចំនួនថ្ងៃយោងមិនអាចតិចជាងសូន្យ។',

            'maximum_carry_forward_days.max' =>
                'ចំនួនថ្ងៃយោងមិនអាចលើសពី ៣៦៥ ថ្ងៃ។',
        ];
    }

    #[Computed]
    public function leaveTypes()
    {
        return LeaveType::query()
            ->withCount([
                'balances',
                'requests',
            ])
            ->where(
                'company_id',
                $this->companyId
            )
            ->when(
                filled($this->search),
                function ($query): void {
                    $search = trim($this->search);

                    $query->where(
                        fn ($query) => $query
                            ->where(
                                'name',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'code',
                                'like',
                                "%{$search}%"
                            )
                    );
                }
            )
            ->when(
                $this->filterStatus !== '',
                fn ($query) => $query->where(
                    'is_active',
                    $this->filterStatus === '1'
                )
            )
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(15);
    }

    #[Computed]
    public function statistics(): array
    {
        $query = LeaveType::query()
            ->where(
                'company_id',
                $this->companyId
            );

        return [
            'total' => (clone $query)->count(),

            'active' => (clone $query)
                ->where('is_active', true)
                ->count(),

            'paid' => (clone $query)
                ->where('is_paid', true)
                ->count(),

            'carry_forward' => (clone $query)
                ->where(
                    'carry_forward_allowed',
                    true
                )
                ->count(),
        ];
    }

    public function openCreateForm(): void
    {
        $this->resetForm();

        $this->showForm = true;
    }

    public function edit(int $leaveTypeId): void
    {
        $leaveType = $this->findLeaveType(
            $leaveTypeId
        );

        $this->leaveTypeId = $leaveType->id;
        $this->name = $leaveType->name;
        $this->code = $leaveType->code;
        $this->days_per_year =
            (string) $leaveType->days_per_year;
        $this->is_paid =
            (bool) $leaveType->is_paid;
        $this->requires_attachment =
            (bool) $leaveType->requires_attachment;
        $this->carry_forward_allowed =
            (bool) $leaveType->carry_forward_allowed;
        $this->maximum_carry_forward_days =
            (string) $leaveType
                ->maximum_carry_forward_days;
        $this->is_active =
            (bool) $leaveType->is_active;

        $this->resetValidation();

        $this->showForm = true;
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->can('leave.manage'),
            403
        );

        $validated = $this->validate();

        $validated['name'] = trim(
            $validated['name']
        );

        $validated['code'] = strtoupper(
            trim($validated['code'])
        );

        $validated['days_per_year'] =
            (float) $validated['days_per_year'];

        $validated['is_paid'] =
            (bool) $validated['is_paid'];

        $validated['requires_attachment'] =
            (bool) $validated['requires_attachment'];

        $validated['carry_forward_allowed'] =
            (bool) $validated[
                'carry_forward_allowed'
            ];

        $validated['maximum_carry_forward_days'] =
            $validated['carry_forward_allowed']
                ? (float) (
                    $validated[
                        'maximum_carry_forward_days'
                    ] ?? 0
                )
                : 0;

        $validated['is_active'] =
            (bool) $validated['is_active'];

        if ($this->leaveTypeId !== null) {
            $leaveType = $this->findLeaveType(
                $this->leaveTypeId
            );

            $leaveType->update($validated);

            $message =
                'បានកែប្រែប្រភេទការឈប់សម្រាកដោយជោគជ័យ។';
        } else {
            LeaveType::query()->create([
                ...$validated,
                'company_id' => $this->companyId,
            ]);

            $message =
                'បានបង្កើតប្រភេទការឈប់សម្រាកថ្មីដោយជោគជ័យ។';
        }

        unset(
            $this->leaveTypes,
            $this->statistics
        );

        $this->resetPage();
        $this->cancelForm();

        Flux::toast(
            variant: 'success',
            text: $message
        );
    }

    public function toggleStatus(
        int $leaveTypeId
    ): void {
        $leaveType = $this->findLeaveType(
            $leaveTypeId
        );

        $leaveType->update([
            'is_active' => ! $leaveType->is_active,
        ]);

        unset(
            $this->leaveTypes,
            $this->statistics
        );

        Flux::toast(
            variant: 'success',
            text: $leaveType->fresh()->is_active
                ? 'បានបើកដំណើរការប្រភេទការឈប់សម្រាក។'
                : 'បានបិទដំណើរការប្រភេទការឈប់សម្រាក។'
        );
    }

    public function delete(int $leaveTypeId): void
    {
        $leaveType = $this->findLeaveType(
            $leaveTypeId
        );

        if (
            $leaveType->requests()->exists()
            || $leaveType->balances()->exists()
        ) {
            Flux::toast(
                variant: 'danger',
                text: 'មិនអាចលុបប្រភេទនេះបានទេ ព្រោះមានប្រវត្តិប្រើប្រាស់រួចហើយ។'
            );

            return;
        }

        $leaveType->delete();

        if ($this->leaveTypeId === $leaveTypeId) {
            $this->cancelForm();
        }

        unset(
            $this->leaveTypes,
            $this->statistics
        );

        $this->resetPage();

        Flux::toast(
            variant: 'success',
            text: 'បានលុបប្រភេទការឈប់សម្រាកដោយជោគជ័យ។'
        );
    }

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'filterStatus',
        ]);

        unset($this->leaveTypes);

        $this->resetPage();
    }

    public function cancelForm(): void
    {
        $this->resetForm();

        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->reset([
            'leaveTypeId',
            'name',
            'code',
            'days_per_year',
            'is_paid',
            'requires_attachment',
            'carry_forward_allowed',
            'maximum_carry_forward_days',
            'is_active',
        ]);

        $this->days_per_year = '0';
        $this->maximum_carry_forward_days = '0';
        $this->is_paid = true;
        $this->requires_attachment = false;
        $this->carry_forward_allowed = false;
        $this->is_active = true;

        $this->resetValidation();
    }

    private function findLeaveType(
        int $leaveTypeId
    ): LeaveType {
        return LeaveType::query()
            ->where(
                'company_id',
                $this->companyId
            )
            ->findOrFail($leaveTypeId);
    }
};

?>

<div class="w-full space-y-5 p-4 sm:p-6">
    {{-- Page heading --}}
    <div
        class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
    >
        <div>
            <h1
                class="text-2xl font-semibold text-zinc-900 dark:text-white"
            >
                ប្រភេទការឈប់សម្រាក
            </h1>

            <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                កំណត់ប្រភេទច្បាប់ ចំនួនថ្ងៃ និងលក្ខខណ្ឌប្រើប្រាស់។
            </p>
        </div>

        <flux:button
            type="button"
            variant="primary"
            icon="plus"
            wire:click="openCreateForm"
        >
            បន្ថែមប្រភេទច្បាប់
        </flux:button>
    </div>

    {{-- Statistics --}}
    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div
            class="rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                ប្រភេទសរុប
            </p>

            <p
                class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-white"
            >
                {{ number_format($this->statistics['total']) }}
            </p>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                កំពុងប្រើប្រាស់
            </p>

            <p class="mt-1 text-2xl font-semibold text-emerald-600">
                {{ number_format($this->statistics['active']) }}
            </p>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                មានប្រាក់ឈ្នួល
            </p>

            <p class="mt-1 text-2xl font-semibold text-blue-600">
                {{ number_format($this->statistics['paid']) }}
            </p>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                អាចយោងទៅឆ្នាំក្រោយ
            </p>

            <p class="mt-1 text-2xl font-semibold text-amber-600">
                {{
                    number_format(
                        $this->statistics['carry_forward']
                    )
                }}
            </p>
        </div>
    </div>

    {{-- Create or edit form --}}
    @if ($showForm)
        <form
            wire:submit="save"
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6"
        >
            <div
                class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
            >
                <div>
                    <h2
                        class="text-lg font-semibold text-zinc-900 dark:text-white"
                    >
                        {{
                            $leaveTypeId !== null
                                ? 'កែប្រែប្រភេទច្បាប់'
                                : 'បន្ថែមប្រភេទច្បាប់ថ្មី'
                        }}
                    </h2>

                    <p
                        class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
                    >
                        បំពេញព័ត៌មាន និងលក្ខខណ្ឌសំខាន់ៗ។
                    </p>
                </div>

                <flux:button
                    type="button"
                    variant="ghost"
                    icon="x-mark"
                    wire:click="cancelForm"
                >
                    បិទទម្រង់
                </flux:button>
            </div>

            <div
                class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4"
            >
                <flux:input
                    wire:model="name"
                    label="ឈ្មោះប្រភេទច្បាប់"
                    placeholder="ឧទាហរណ៍៖ ច្បាប់ប្រចាំឆ្នាំ"
                    required
                />

                <flux:input
                    wire:model="code"
                    label="លេខកូដ"
                    placeholder="ANNUAL"
                    required
                />

                <flux:input
                    wire:model="days_per_year"
                    type="number"
                    min="0"
                    max="365"
                    step="0.5"
                    label="ចំនួនថ្ងៃប្រចាំឆ្នាំ"
                    required
                />

                <flux:input
                    wire:model="maximum_carry_forward_days"
                    type="number"
                    min="0"
                    max="365"
                    step="0.5"
                    label="ថ្ងៃយោងអតិបរមា"
                    :disabled="! $carry_forward_allowed"
                />
            </div>

            <div
                class="mt-5 grid gap-4 border-t border-zinc-200 pt-5 sm:grid-cols-2 xl:grid-cols-4 dark:border-zinc-700"
            >
                <flux:checkbox
                    wire:model="is_paid"
                    label="មានប្រាក់ឈ្នួល"
                />

                <flux:checkbox
                    wire:model="requires_attachment"
                    label="តម្រូវឯកសារភ្ជាប់"
                />

                <flux:checkbox
                    wire:model.live="carry_forward_allowed"
                    label="អាចយោងទៅឆ្នាំក្រោយ"
                />

                <flux:checkbox
                    wire:model="is_active"
                    label="កំពុងដំណើរការ"
                />
            </div>

            <div
                class="mt-5 flex flex-col-reverse gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700 sm:flex-row sm:justify-end"
            >
                <flux:button
                    type="button"
                    variant="ghost"
                    wire:click="cancelForm"
                    class="w-full sm:w-auto"
                >
                    បោះបង់
                </flux:button>

                <flux:button
                    type="submit"
                    variant="primary"
                    icon="check"
                    wire:loading.attr="disabled"
                    wire:target="save"
                    class="w-full sm:w-auto"
                >
                    <span
                        wire:loading.remove
                        wire:target="save"
                    >
                        {{
                            $leaveTypeId !== null
                                ? 'រក្សាទុកការកែប្រែ'
                                : 'បង្កើតប្រភេទច្បាប់'
                        }}
                    </span>

                    <span wire:loading wire:target="save">
                        កំពុងរក្សាទុក...
                    </span>
                </flux:button>
            </div>
        </form>
    @endif

    {{-- List --}}
    <div
        class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
    >
        <div
            class="border-b border-zinc-200 p-4 dark:border-zinc-700 sm:p-5"
        >
            <div
                class="grid gap-3 md:grid-cols-[minmax(0,2fr)_minmax(180px,1fr)_auto] md:items-end"
            >
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    label="ស្វែងរក"
                    icon="magnifying-glass"
                    placeholder="ឈ្មោះ ឬលេខកូដ..."
                    clearable
                />

                <flux:select
                    wire:model.live="filterStatus"
                    label="ស្ថានភាព"
                >
                    <flux:select.option value="">
                        ស្ថានភាពទាំងអស់
                    </flux:select.option>

                    <flux:select.option value="1">
                        កំពុងដំណើរការ
                    </flux:select.option>

                    <flux:select.option value="0">
                        បានបិទ
                    </flux:select.option>
                </flux:select>

                <flux:button
                    type="button"
                    variant="ghost"
                    icon="arrow-path"
                    wire:click="clearFilters"
                    class="w-full md:w-auto"
                >
                    សម្អាត
                </flux:button>
            </div>
        </div>

        <div
            class="flex items-center justify-between border-b border-zinc-200 px-5 py-3 dark:border-zinc-700"
        >
            <div>
                <h2
                    class="font-semibold text-zinc-900 dark:text-white"
                >
                    បញ្ជីប្រភេទច្បាប់
                </h2>

                <p
                    class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                >
                    រកឃើញ
                    {{ number_format($this->leaveTypes->total()) }}
                    ប្រភេទ
                </p>
            </div>
        </div>

        {{-- Desktop table --}}
        <div class="hidden overflow-x-auto md:block">
            <table
                class="w-full min-w-[1000px] text-left text-sm"
            >
                <thead
                    class="bg-zinc-50 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                >
                    <tr>
                        <th class="px-5 py-3.5 font-medium">
                            ប្រភេទច្បាប់
                        </th>

                        <th class="px-5 py-3.5 font-medium">
                            ចំនួនថ្ងៃ
                        </th>

                        <th class="px-5 py-3.5 font-medium">
                            លក្ខខណ្ឌ
                        </th>

                        <th class="px-5 py-3.5 font-medium">
                            ការប្រើប្រាស់
                        </th>

                        <th class="px-5 py-3.5 font-medium">
                            ស្ថានភាព
                        </th>

                        <th
                            class="px-5 py-3.5 text-right font-medium"
                        >
                            សកម្មភាព
                        </th>
                    </tr>
                </thead>

                <tbody
                    class="divide-y divide-zinc-200 dark:divide-zinc-700"
                >
                    @forelse ($this->leaveTypes as $leaveType)
                        <tr
                            wire:key="leave-type-table-{{ $leaveType->id }}"
                            class="align-top transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        >
                            <td class="px-5 py-4">
                                <div
                                    class="font-medium text-zinc-900 dark:text-white"
                                >
                                    {{ $leaveType->name }}
                                </div>

                                <div
                                    class="mt-1 font-mono text-xs text-zinc-500 dark:text-zinc-400"
                                >
                                    {{ $leaveType->code }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div
                                    class="font-medium text-zinc-900 dark:text-white"
                                >
                                    {{
                                        number_format(
                                            (float) $leaveType
                                                ->days_per_year,
                                            1
                                        )
                                    }}
                                    ថ្ងៃ/ឆ្នាំ
                                </div>

                                @if (
                                    $leaveType
                                        ->carry_forward_allowed
                                )
                                    <div
                                        class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                    >
                                        យោងបានអតិបរមា
                                        {{
                                            number_format(
                                                (float) $leaveType
                                                    ->maximum_carry_forward_days,
                                                1
                                            )
                                        }}
                                        ថ្ងៃ
                                    </div>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    <flux:badge
                                        size="sm"
                                        :color="$leaveType->is_paid
                                            ? 'green'
                                            : 'zinc'"
                                    >
                                        {{
                                            $leaveType->is_paid
                                                ? 'មានប្រាក់ឈ្នួល'
                                                : 'គ្មានប្រាក់ឈ្នួល'
                                        }}
                                    </flux:badge>

                                    @if (
                                        $leaveType
                                            ->requires_attachment
                                    )
                                        <flux:badge
                                            size="sm"
                                            color="blue"
                                        >
                                            តម្រូវឯកសារ
                                        </flux:badge>
                                    @endif

                                    @if (
                                        $leaveType
                                            ->carry_forward_allowed
                                    )
                                        <flux:badge
                                            size="sm"
                                            color="amber"
                                        >
                                            យោងទៅឆ្នាំក្រោយ
                                        </flux:badge>
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div
                                    class="text-zinc-800 dark:text-zinc-200"
                                >
                                    {{
                                        number_format(
                                            $leaveType
                                                ->requests_count
                                        )
                                    }}
                                    សំណើ
                                </div>

                                <div
                                    class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                >
                                    {{
                                        number_format(
                                            $leaveType
                                                ->balances_count
                                        )
                                    }}
                                    សមតុល្យ
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <flux:badge
                                    size="sm"
                                    :color="$leaveType->is_active
                                        ? 'green'
                                        : 'zinc'"
                                >
                                    {{
                                        $leaveType->is_active
                                            ? 'កំពុងដំណើរការ'
                                            : 'បានបិទ'
                                    }}
                                </flux:badge>
                            </td>

                            <td class="px-5 py-4">
                                <div
                                    class="flex justify-end gap-2"
                                >
                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="ghost"
                                        icon="pencil-square"
                                        square
                                        wire:click="edit({{ $leaveType->id }})"
                                        title="កែប្រែ"
                                        aria-label="កែប្រែ"
                                    />

                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="ghost"
                                        :icon="$leaveType->is_active
                                            ? 'pause-circle'
                                            : 'play-circle'"
                                        square
                                        wire:click="toggleStatus({{ $leaveType->id }})"
                                        :title="$leaveType->is_active
                                            ? 'បិទដំណើរការ'
                                            : 'បើកដំណើរការ'"
                                        :aria-label="$leaveType->is_active
                                            ? 'បិទដំណើរការ'
                                            : 'បើកដំណើរការ'"
                                    />

                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="danger"
                                        icon="trash"
                                        square
                                        wire:click="delete({{ $leaveType->id }})"
                                        wire:confirm="តើអ្នកពិតជាចង់លុបប្រភេទច្បាប់នេះមែនទេ?"
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
                                class="px-5 py-14 text-center"
                            >
                                <div
                                    class="font-medium text-zinc-700 dark:text-zinc-200"
                                >
                                    មិនទាន់មានប្រភេទការឈប់សម្រាក
                                </div>

                                <p
                                    class="mt-2 text-sm text-zinc-500 dark:text-zinc-400"
                                >
                                    បន្ថែមប្រភេទច្បាប់ថ្មី
                                    ឬប្តូរលក្ខខណ្ឌស្វែងរក។
                                </p>
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
            @forelse ($this->leaveTypes as $leaveType)
                <div
                    wire:key="leave-type-card-{{ $leaveType->id }}"
                    class="space-y-4 p-5"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div class="min-w-0">
                            <div
                                class="truncate font-medium text-zinc-900 dark:text-white"
                            >
                                {{ $leaveType->name }}
                            </div>

                            <div
                                class="mt-1 font-mono text-xs text-zinc-500 dark:text-zinc-400"
                            >
                                {{ $leaveType->code }}
                            </div>
                        </div>

                        <flux:badge
                            size="sm"
                            :color="$leaveType->is_active
                                ? 'green'
                                : 'zinc'"
                        >
                            {{
                                $leaveType->is_active
                                    ? 'ដំណើរការ'
                                    : 'បានបិទ'
                            }}
                        </flux:badge>
                    </div>

                    <div class="grid grid-cols-2 gap-3 text-sm">
                        <div>
                            <div
                                class="text-xs text-zinc-500 dark:text-zinc-400"
                            >
                                ចំនួនថ្ងៃ
                            </div>

                            <div
                                class="mt-1 text-zinc-800 dark:text-zinc-200"
                            >
                                {{
                                    number_format(
                                        (float) $leaveType
                                            ->days_per_year,
                                        1
                                    )
                                }}
                                ថ្ងៃ/ឆ្នាំ
                            </div>
                        </div>

                        <div>
                            <div
                                class="text-xs text-zinc-500 dark:text-zinc-400"
                            >
                                ការប្រើប្រាស់
                            </div>

                            <div
                                class="mt-1 text-zinc-800 dark:text-zinc-200"
                            >
                                {{
                                    number_format(
                                        $leaveType
                                            ->requests_count
                                    )
                                }}
                                សំណើ
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-1.5">
                        <flux:badge
                            size="sm"
                            :color="$leaveType->is_paid
                                ? 'green'
                                : 'zinc'"
                        >
                            {{
                                $leaveType->is_paid
                                    ? 'មានប្រាក់ឈ្នួល'
                                    : 'គ្មានប្រាក់ឈ្នួល'
                            }}
                        </flux:badge>

                        @if ($leaveType->requires_attachment)
                            <flux:badge size="sm" color="blue">
                                តម្រូវឯកសារ
                            </flux:badge>
                        @endif

                        @if ($leaveType->carry_forward_allowed)
                            <flux:badge size="sm" color="amber">
                                យោងទៅឆ្នាំក្រោយ
                            </flux:badge>
                        @endif
                    </div>

                    <div
                        class="flex justify-end gap-2 border-t border-zinc-100 pt-4 dark:border-zinc-800"
                    >
                        <flux:button
                            type="button"
                            size="sm"
                            variant="ghost"
                            icon="pencil-square"
                            square
                            wire:click="edit({{ $leaveType->id }})"
                            title="កែប្រែ"
                            aria-label="កែប្រែ"
                        />

                        <flux:button
                            type="button"
                            size="sm"
                            variant="ghost"
                            :icon="$leaveType->is_active
                                ? 'pause-circle'
                                : 'play-circle'"
                            square
                            wire:click="toggleStatus({{ $leaveType->id }})"
                            :title="$leaveType->is_active
                                ? 'បិទដំណើរការ'
                                : 'បើកដំណើរការ'"
                            :aria-label="$leaveType->is_active
                                ? 'បិទដំណើរការ'
                                : 'បើកដំណើរការ'"
                        />

                        <flux:button
                            type="button"
                            size="sm"
                            variant="danger"
                            icon="trash"
                            square
                            wire:click="delete({{ $leaveType->id }})"
                            wire:confirm="តើអ្នកពិតជាចង់លុបប្រភេទច្បាប់នេះមែនទេ?"
                            title="លុប"
                            aria-label="លុប"
                        />
                    </div>
                </div>
            @empty
                <div class="px-5 py-14 text-center">
                    <div
                        class="font-medium text-zinc-700 dark:text-zinc-200"
                    >
                        មិនទាន់មានប្រភេទការឈប់សម្រាក
                    </div>

                    <p
                        class="mt-2 text-sm text-zinc-500 dark:text-zinc-400"
                    >
                        បន្ថែមប្រភេទច្បាប់ថ្មី
                        ឬប្តូរលក្ខខណ្ឌស្វែងរក។
                    </p>
                </div>
            @endforelse
        </div>

        @if ($this->leaveTypes->hasPages())
            <div
                class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-700"
            >
                {{ $this->leaveTypes->links() }}
            </div>
        @endif
    </div>
</div>