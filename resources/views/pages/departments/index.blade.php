<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('គ្រប់គ្រងផ្នែក')] class extends Component
{
    public int $companyId = 0;

    public ?int $departmentId = null;

    public string $search = '';

    public string $filterBranch = '';

    public string $branch_id = '';

    public string $name = '';

    public string $code = '';

    public string $manager_name = '';

    public string $phone = '';

    public string $email = '';

    public string $description = '';

    public bool $is_active = true;

    public function mount()
    {
        $company = Company::query()->first();

        if (! $company) {
            return $this->redirectRoute(
                'company.settings',
                navigate: true
            );
        }

        $this->companyId = $company->id;

        $firstBranch = Branch::query()
            ->where('company_id', $this->companyId)
            ->orderByDesc('is_head_office')
            ->orderBy('name')
            ->first();

        if ($firstBranch) {
            $this->branch_id = (string) $firstBranch->id;
        }
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

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'code' => [
                'required',
                'string',
                'max:50',

                Rule::unique('departments', 'code')
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
                    )
                    ->ignore($this->departmentId),
            ],

            'manager_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'is_active' => [
                'boolean',
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

            'name.required' =>
                'សូមបញ្ចូលឈ្មោះផ្នែក។',

            'name.max' =>
                'ឈ្មោះផ្នែកមិនអាចលើសពី ២៥៥ តួអក្សរ។',

            'code.required' =>
                'សូមបញ្ចូលលេខកូដផ្នែក។',

            'code.unique' =>
                'លេខកូដផ្នែកនេះត្រូវបានប្រើរួចហើយនៅក្នុងសាខានេះ។',

            'code.max' =>
                'លេខកូដផ្នែកមិនអាចលើសពី ៥០ តួអក្សរ។',

            'email.email' =>
                'ទម្រង់អ៊ីមែលមិនត្រឹមត្រូវ។',

            'description.max' =>
                'សេចក្ដីពិពណ៌នាមិនអាចលើសពី ២០០០ តួអក្សរ។',
        ];
    }

    #[Computed]
    public function branches()
    {
        return Branch::query()
            ->where('company_id', $this->companyId)
            ->orderByDesc('is_head_office')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function departments()
    {
        return Department::query()
            ->with('branch')
            ->where('company_id', $this->companyId)
            ->when(
                filled($this->filterBranch),
                fn ($query) => $query->where(
                    'branch_id',
                    $this->filterBranch
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
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'manager_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'phone',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'email',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhereHas(
                                    'branch',
                                    fn ($branchQuery) =>
                                        $branchQuery->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )
                                );
                        }
                    );
                }
            )
            ->orderBy('branch_id')
            ->orderBy('name')
            ->get();
    }

    public function save(): void
    {
        $validated = $this->validate();

        $validated['name'] = trim(
            $validated['name']
        );

        $validated['code'] = strtoupper(
            trim($validated['code'])
        );

        foreach ([
            'manager_name',
            'phone',
            'email',
            'description',
        ] as $field) {
            $validated[$field] = filled(
                $validated[$field]
            )
                ? trim((string) $validated[$field])
                : null;
        }

        if ($this->departmentId !== null) {
            $department = $this->findDepartment(
                $this->departmentId
            );

            $department->update($validated);

            Flux::toast(
                variant: 'success',
                text: 'បានកែប្រែព័ត៌មានផ្នែកដោយជោគជ័យ។'
            );
        } else {
            Department::query()->create([
                ...$validated,
                'company_id' => $this->companyId,
            ]);

            Flux::toast(
                variant: 'success',
                text: 'បានបង្កើតផ្នែកថ្មីដោយជោគជ័យ។'
            );
        }

        unset($this->departments);

        $this->resetForm();
    }

    public function edit(int $departmentId): void
    {
        $department = $this->findDepartment(
            $departmentId
        );

        $this->departmentId = $department->id;

        $this->branch_id = (string) $department->branch_id;

        $this->name = $department->name;

        $this->code = $department->code;

        $this->manager_name = (string) $department->manager_name;

        $this->phone = (string) $department->phone;

        $this->email = (string) $department->email;

        $this->description = (string) $department->description;

        $this->is_active = (bool) $department->is_active;

        $this->resetValidation();
    }

    public function toggleStatus(
        int $departmentId
    ): void {
        $department = $this->findDepartment(
            $departmentId
        );

        $newStatus = ! $department->is_active;

        $department->update([
            'is_active' => $newStatus,
        ]);

        unset($this->departments);

        Flux::toast(
            variant: 'success',
            text: $newStatus
                ? 'បានបើកដំណើរការផ្នែក។'
                : 'បានបិទដំណើរការផ្នែក។'
        );
    }

    public function delete(
        int $departmentId
    ): void {
        $department = $this->findDepartment(
            $departmentId
        );

        $department->delete();

        if ($this->departmentId === $departmentId) {
            $this->resetForm();
        }

        unset($this->departments);

        Flux::toast(
            variant: 'success',
            text: 'បានលុបផ្នែកដោយជោគជ័យ។'
        );
    }

    public function resetForm(): void
    {
        $firstBranch = Branch::query()
            ->where('company_id', $this->companyId)
            ->orderByDesc('is_head_office')
            ->orderBy('name')
            ->first();

        $this->reset([
            'departmentId',
            'branch_id',
            'name',
            'code',
            'manager_name',
            'phone',
            'email',
            'description',
            'is_active',
        ]);

        $this->branch_id = $firstBranch
            ? (string) $firstBranch->id
            : '';

        $this->is_active = true;

        $this->resetValidation();
    }

    private function findDepartment(
        int $departmentId
    ): Department {
        return Department::query()
            ->where(
                'company_id',
                $this->companyId
            )
            ->findOrFail($departmentId);
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
                គ្រប់គ្រងផ្នែក
            </h1>

            <p
                class="mt-1 text-zinc-600 dark:text-zinc-300"
            >
                បង្កើត ស្វែងរក កែប្រែ និងគ្រប់គ្រងផ្នែកតាមសាខា។
            </p>
        </div>

        <flux:button
            type="button"
            variant="primary"
            icon="plus"
            :href="route('departments.create')"
            wire:navigate
        >
            បង្កើតផ្នែកថ្មី
        </flux:button>
    </div>

    @if ($this->branches->isEmpty())

        {{-- No branches warning --}}
        <div
            class="rounded-xl border border-amber-200 bg-amber-50 p-6 text-amber-800 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-200"
        >
            <h2 class="font-semibold">
                មិនទាន់មានសាខា
            </h2>

            <p class="mt-2 text-sm">
                សូមបង្កើតសាខាជាមុនសិន មុនពេលបង្កើតផ្នែក។
            </p>

            <flux:button
                class="mt-4"
                variant="primary"
                :href="route('branches.index')"
                wire:navigate
            >
                ទៅកាន់ការគ្រប់គ្រងសាខា
            </flux:button>
        </div>
    @else

        {{-- Main content --}}
        <div
            @class([
                'grid items-start gap-6',
                'xl:grid-cols-[380px_minmax(0,1fr)]' =>
                    $departmentId !== null,
            ])
        >
            @if ($departmentId !== null)

                {{-- Edit form --}}
                <form
                    wire:submit="save"
                    class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
                >
                    <div>
                        <h2
                            class="text-lg font-semibold text-zinc-900 dark:text-white"
                        >
                            កែប្រែព័ត៌មានផ្នែក
                        </h2>

                        <p
                            class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
                        >
                            ជ្រើសរើសសាខា និងកែប្រែព័ត៌មានផ្នែក។
                        </p>
                    </div>

                    <flux:select
                        wire:model="branch_id"
                        label="សាខា"
                        required
                    >
                        <flux:select.option value="">
                            សូមជ្រើសរើសសាខា
                        </flux:select.option>

                        @foreach ($this->branches as $branch)
                            <flux:select.option
                                value="{{ $branch->id }}"
                            >
                                {{ $branch->name }}

                                @if ($branch->is_head_office)
                                    — ការិយាល័យកណ្ដាល
                                @endif
                            </flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input
                        wire:model="name"
                        label="ឈ្មោះផ្នែក"
                        placeholder="ឧទាហរណ៍៖ ផ្នែករដ្ឋបាល"
                        required
                    />

                    <flux:input
                        wire:model="code"
                        label="លេខកូដផ្នែក"
                        placeholder="ឧទាហរណ៍៖ ADM001"
                        required
                    />

                    <flux:input
                        wire:model="manager_name"
                        label="ឈ្មោះប្រធានផ្នែក"
                        placeholder="ឈ្មោះប្រធានផ្នែក"
                    />

                    <flux:input
                        wire:model="phone"
                        label="លេខទូរស័ព្ទ"
                        placeholder="+855..."
                    />

                    <flux:input
                        wire:model="email"
                        type="email"
                        label="អ៊ីមែល"
                        placeholder="department@example.com"
                    />

                    <flux:textarea
                        wire:model="description"
                        label="សេចក្ដីពិពណ៌នា"
                        rows="3"
                        placeholder="ពិពណ៌នាអំពីតួនាទីរបស់ផ្នែក..."
                    />

                    <flux:checkbox
                        wire:model="is_active"
                        label="ផ្នែកកំពុងដំណើរការ"
                    />

                    <div class="flex flex-wrap gap-3">
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
                                រក្សាទុកការកែប្រែ
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

            {{-- Department list --}}
            <div class="min-w-0">
                <div
                    class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
                >
                    {{-- List header --}}
                    <div
                        class="border-b border-zinc-200 p-5 dark:border-zinc-700"
                    >
                        <div
                            class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
                        >
                            <div>
                                <h2
                                    class="text-lg font-semibold text-zinc-900 dark:text-white"
                                >
                                    បញ្ជីផ្នែក
                                </h2>

                                <p
                                    class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
                                >
                                    ចំនួនផ្នែក៖
                                    {{ $this->departments->count() }}
                                </p>
                            </div>

                            <div
                                class="grid w-full gap-3 sm:grid-cols-2 lg:w-auto"
                            >
                                <div class="w-full lg:w-56">
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

                                <div class="w-full lg:w-72">
                                    <flux:input
                                        wire:model.live.debounce.300ms="search"
                                        icon="magnifying-glass"
                                        placeholder="ស្វែងរកផ្នែក..."
                                        clearable
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Desktop table --}}
                    <div
                        class="hidden overflow-x-auto md:block"
                    >
                        <table
                            class="w-full min-w-[900px] text-left text-sm"
                        >
                            <thead
                                class="bg-zinc-50 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                            >
                                <tr>
                                    <th
                                        class="px-5 py-4 font-medium"
                                    >
                                        ផ្នែក
                                    </th>

                                    <th
                                        class="px-5 py-4 font-medium"
                                    >
                                        សាខា
                                    </th>

                                    <th
                                        class="px-5 py-4 font-medium"
                                    >
                                        ប្រធានផ្នែក
                                    </th>

                                    <th
                                        class="px-5 py-4 font-medium"
                                    >
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
                                    $this->departments
                                    as $department
                                )
                                    <tr
                                        wire:key="department-table-{{ $department->id }}"
                                        class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                                    >
                                        <td
                                            class="px-5 py-4 align-top"
                                        >
                                            <div
                                                class="font-medium text-zinc-900 dark:text-white"
                                            >
                                                {{ $department->name }}
                                            </div>

                                            <div
                                                class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                            >
                                                {{ $department->code }}
                                            </div>

                                            @if ($department->email)
                                                <div
                                                    class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                                >
                                                    {{ $department->email }}
                                                </div>
                                            @endif
                                        </td>

                                        <td
                                            class="px-5 py-4 align-top text-zinc-700 dark:text-zinc-300"
                                        >
                                            {{
                                                $department->branch?->name
                                                    ?? 'មិនមានសាខា'
                                            }}
                                        </td>

                                        <td
                                            class="px-5 py-4 align-top text-zinc-700 dark:text-zinc-300"
                                        >
                                            <div>
                                                {{
                                                    $department->manager_name
                                                        ?: 'មិនទាន់កំណត់'
                                                }}
                                            </div>

                                            @if ($department->phone)
                                                <div
                                                    class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                                >
                                                    {{ $department->phone }}
                                                </div>
                                            @endif
                                        </td>

                                        <td
                                            class="px-5 py-4 align-top"
                                        >
                                            @if ($department->is_active)
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
                                                    wire:click="edit({{ $department->id }})"
                                                    title="កែប្រែ"
                                                    aria-label="កែប្រែ"
                                                />

                                                {{-- Status --}}
                                                <flux:button
                                                    type="button"
                                                    size="sm"
                                                    variant="ghost"
                                                    :icon="$department->is_active
                                                        ? 'pause-circle'
                                                        : 'play-circle'"
                                                    square
                                                    wire:click="toggleStatus({{ $department->id }})"
                                                    :title="$department->is_active
                                                        ? 'បិទដំណើរការ'
                                                        : 'បើកដំណើរការ'"
                                                    :aria-label="$department->is_active
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
                                                    wire:click="delete({{ $department->id }})"
                                                    wire:confirm="តើអ្នកពិតជាចង់លុបផ្នែកនេះមែនទេ?"
                                                    title="លុប"
                                                    aria-label="លុប"
                                                />
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td
                                            colspan="5"
                                            class="px-5 py-12 text-center"
                                        >
                                            <div
                                                class="font-medium text-zinc-700 dark:text-zinc-200"
                                            >
                                                មិនមានផ្នែក
                                            </div>

                                            <div
                                                class="mt-2 text-sm text-zinc-500 dark:text-zinc-400"
                                            >
                                                សូមបង្កើតផ្នែកថ្មី
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
                            $this->departments
                            as $department
                        )
                            <div
                                wire:key="department-mobile-{{ $department->id }}"
                                class="space-y-4 p-5"
                            >
                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <div class="min-w-0">
                                        <div
                                            class="truncate font-medium text-zinc-900 dark:text-white"
                                        >
                                            {{ $department->name }}
                                        </div>

                                        <div
                                            class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                        >
                                            {{ $department->code }}
                                        </div>
                                    </div>

                                    @if ($department->is_active)
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
                                            សាខា
                                        </dt>

                                        <dd
                                            class="mt-1 text-zinc-800 dark:text-zinc-200"
                                        >
                                            {{
                                                $department->branch?->name
                                                    ?? 'មិនមានសាខា'
                                            }}
                                        </dd>
                                    </div>

                                    <div>
                                        <dt
                                            class="text-xs text-zinc-500 dark:text-zinc-400"
                                        >
                                            ប្រធានផ្នែក
                                        </dt>

                                        <dd
                                            class="mt-1 text-zinc-800 dark:text-zinc-200"
                                        >
                                            {{
                                                $department->manager_name
                                                    ?: 'មិនទាន់កំណត់'
                                            }}
                                        </dd>

                                        @if ($department->phone)
                                            <dd
                                                class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                            >
                                                {{ $department->phone }}
                                            </dd>
                                        @endif
                                    </div>
                                </dl>

                                <div
                                    class="flex justify-end gap-2 border-t border-zinc-100 pt-4 dark:border-zinc-800"
                                >
                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="ghost"
                                        icon="pencil-square"
                                        square
                                        wire:click="edit({{ $department->id }})"
                                        title="កែប្រែ"
                                        aria-label="កែប្រែ"
                                    />

                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="ghost"
                                        :icon="$department->is_active
                                            ? 'pause-circle'
                                            : 'play-circle'"
                                        square
                                        wire:click="toggleStatus({{ $department->id }})"
                                        :title="$department->is_active
                                            ? 'បិទដំណើរការ'
                                            : 'បើកដំណើរការ'"
                                        :aria-label="$department->is_active
                                            ? 'បិទដំណើរការ'
                                            : 'បើកដំណើរការ'"
                                    />

                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="danger"
                                        icon="trash"
                                        square
                                        wire:click="delete({{ $department->id }})"
                                        wire:confirm="តើអ្នកពិតជាចង់លុបផ្នែកនេះមែនទេ?"
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
                                    មិនមានផ្នែក
                                </div>

                                <div
                                    class="mt-2 text-sm text-zinc-500 dark:text-zinc-400"
                                >
                                    សូមបង្កើតផ្នែកថ្មី
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