<?php

use App\Models\Branch;
use App\Models\Company;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('គ្រប់គ្រងសាខា')] class extends Component
{
    public int $companyId = 0;

    public ?int $branchId = null;

    public string $search = '';

    public string $name = '';

    public string $code = '';

    public string $manager_name = '';

    public string $phone = '';

    public string $email = '';

    public string $city = '';

    public string $address = '';

    public bool $is_head_office = false;

    public bool $is_active = true;

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

        return null;
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

                Rule::unique('branches', 'code')
                    ->where(
                        fn ($query) => $query->where(
                            'company_id',
                            $this->companyId
                        )
                    )
                    ->ignore($this->branchId),
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

            'city' => [
                'nullable',
                'string',
                'max:100',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'is_head_office' => [
                'boolean',
            ],

            'is_active' => [
                'boolean',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'សូមបញ្ចូលឈ្មោះសាខា។',

            'name.max' => 'ឈ្មោះសាខាមិនអាចលើសពី ២៥៥ តួអក្សរ។',

            'code.required' => 'សូមបញ្ចូលលេខកូដសាខា។',

            'code.unique' => 'លេខកូដសាខានេះត្រូវបានប្រើរួចហើយ។',

            'code.max' => 'លេខកូដសាខាមិនអាចលើសពី ៥០ តួអក្សរ។',

            'email.email' => 'ទម្រង់អ៊ីមែលមិនត្រឹមត្រូវ។',
        ];
    }

    #[Computed]
    public function branches()
    {
        return Branch::query()
            ->where('company_id', $this->companyId)
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
                                ->orWhere(
                                    'city',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->orderByDesc('is_head_office')
            ->orderBy('name')
            ->get();
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->is_head_office) {
            Branch::query()
                ->where('company_id', $this->companyId)
                ->when(
                    $this->branchId !== null,
                    fn ($query) => $query->where(
                        'id',
                        '!=',
                        $this->branchId
                    )
                )
                ->update([
                    'is_head_office' => false,
                ]);
        }

        if ($this->branchId !== null) {
            $branch = $this->findBranch($this->branchId);

            $branch->update($validated);

            Flux::toast(
                variant: 'success',
                text: 'បានកែប្រែព័ត៌មានសាខាដោយជោគជ័យ។'
            );
        } else {
            Branch::query()->create([
                ...$validated,
                'company_id' => $this->companyId,
            ]);

            Flux::toast(
                variant: 'success',
                text: 'បានបង្កើតសាខាថ្មីដោយជោគជ័យ។'
            );
        }

        unset($this->branches);

        $this->resetForm();
    }

    public function edit(int $branchId): void
    {
        $branch = $this->findBranch($branchId);

        $this->branchId = $branch->id;

        $this->name = $branch->name;

        $this->code = $branch->code;

        $this->manager_name = (string) $branch->manager_name;

        $this->phone = (string) $branch->phone;

        $this->email = (string) $branch->email;

        $this->city = (string) $branch->city;

        $this->address = (string) $branch->address;

        $this->is_head_office = (bool) $branch->is_head_office;

        $this->is_active = (bool) $branch->is_active;

        $this->resetValidation();
    }

    public function toggleStatus(int $branchId): void
    {
        $branch = $this->findBranch($branchId);

        $newStatus = ! $branch->is_active;

        $branch->update([
            'is_active' => $newStatus,
        ]);

        unset($this->branches);

        Flux::toast(
            variant: 'success',
            text: $newStatus
                ? 'បានបើកដំណើរការសាខា។'
                : 'បានបិទដំណើរការសាខា។'
        );
    }

    public function delete(int $branchId): void
    {
        $branch = $this->findBranch($branchId);

        if ($branch->is_head_office) {
            Flux::toast(
                variant: 'danger',
                text: 'មិនអាចលុបការិយាល័យកណ្ដាលបានទេ។'
            );

            return;
        }

        $branch->delete();

        if ($this->branchId === $branchId) {
            $this->resetForm();
        }

        unset($this->branches);

        Flux::toast(
            variant: 'success',
            text: 'បានលុបសាខាដោយជោគជ័យ។'
        );
    }

    public function resetForm(): void
    {
        $this->reset([
            'branchId',
            'name',
            'code',
            'manager_name',
            'phone',
            'email',
            'city',
            'address',
            'is_head_office',
            'is_active',
        ]);

        $this->is_head_office = false;

        $this->is_active = true;

        $this->resetValidation();
    }

    private function findBranch(int $branchId): Branch
    {
        return Branch::query()
            ->where('company_id', $this->companyId)
            ->findOrFail($branchId);
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
                គ្រប់គ្រងសាខា
            </h1>

            <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                បង្កើត ស្វែងរក កែប្រែ និងគ្រប់គ្រងសាខារបស់ក្រុមហ៊ុន។
            </p>
        </div>

        <flux:button
            type="button"
            variant="ghost"
            icon="plus"
            :href="route('branches.create')"
            wire:navigate
        >
            បង្កើតសាខាថ្មី
        </flux:button>
    </div>

    {{-- Main content --}}
    <div
        @class([
            'grid items-start gap-6',
            'xl:grid-cols-[380px_minmax(0,1fr)]' => $branchId !== null,
        ])
    >
        @if ($branchId !== null)

            {{-- Edit form --}}
            <form
                wire:submit="save"
                class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
            >
                <div>
                    <h2
                        class="text-lg font-semibold text-zinc-900 dark:text-white"
                    >
                        កែប្រែព័ត៌មានសាខា
                    </h2>

                    <p
                        class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
                    >
                        កែប្រែព័ត៌មានសំខាន់ៗរបស់សាខានេះ។
                    </p>
                </div>

                <flux:input
                    wire:model="name"
                    label="ឈ្មោះសាខា"
                    placeholder="ឧទាហរណ៍៖ សាខាភ្នំពេញ"
                    required
                />

                <flux:input
                    wire:model="code"
                    label="លេខកូដសាខា"
                    placeholder="ឧទាហរណ៍៖ PP001"
                    required
                />

                <flux:input
                    wire:model="manager_name"
                    label="ឈ្មោះអ្នកគ្រប់គ្រង"
                    placeholder="ឈ្មោះអ្នកគ្រប់គ្រងសាខា"
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
                    placeholder="branch@example.com"
                />

                <flux:input
                    wire:model="city"
                    label="ទីក្រុង ឬខេត្ត"
                    placeholder="ភ្នំពេញ"
                />

                <flux:textarea
                    wire:model="address"
                    label="អាសយដ្ឋាន"
                    rows="3"
                    placeholder="អាសយដ្ឋានរបស់សាខា"
                />

                <div class="space-y-4">
                    <flux:checkbox
                        wire:model="is_head_office"
                        label="កំណត់ជាការិយាល័យកណ្ដាល"
                    />

                    <flux:checkbox
                        wire:model="is_active"
                        label="សាខាកំពុងដំណើរការ"
                    />
                </div>

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

        {{-- Branch list --}}
        <div class="min-w-0">
            <div
                class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
            >
                {{-- List heading --}}
                <div
                    class="border-b border-zinc-200 p-5 dark:border-zinc-700"
                >
                    <div
                        class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h2
                                class="text-lg font-semibold text-zinc-900 dark:text-white"
                            >
                                បញ្ជីសាខា
                            </h2>

                            <p
                                class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
                            >
                                ចំនួនសាខា៖
                                {{ $this->branches->count() }}
                            </p>
                        </div>

                        <div class="w-full sm:w-72">
                            <flux:input
                                wire:model.live.debounce.300ms="search"
                                icon="magnifying-glass"
                                placeholder="ស្វែងរកសាខា..."
                                clearable
                            />
                        </div>
                    </div>
                </div>

                {{-- Desktop table --}}
                <div class="hidden overflow-x-auto md:block">
                    <table
                        class="w-full min-w-[900px] text-left text-sm"
                    >
                        <thead
                            class="bg-zinc-50 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                        >
                            <tr>
                                <th class="px-5 py-4 font-medium">
                                    សាខា
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    ទំនាក់ទំនង
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    អ្នកគ្រប់គ្រង
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
                            @forelse ($this->branches as $branch)
                                <tr
                                    wire:key="branch-table-{{ $branch->id }}"
                                    class="transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                                >
                                    <td class="px-5 py-4 align-top">
                                        <div
                                            class="font-medium text-zinc-900 dark:text-white"
                                        >
                                            {{ $branch->name }}
                                        </div>

                                        <div
                                            class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                        >
                                            {{ $branch->code }}

                                            @if ($branch->city)
                                                · {{ $branch->city }}
                                            @endif
                                        </div>

                                        @if ($branch->is_head_office)
                                            <div class="mt-2">
                                                <flux:badge
                                                    size="sm"
                                                    color="blue"
                                                >
                                                    ការិយាល័យកណ្ដាល
                                                </flux:badge>
                                            </div>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4 align-top">
                                        @if ($branch->phone)
                                            <div
                                                class="text-zinc-800 dark:text-zinc-200"
                                            >
                                                {{ $branch->phone }}
                                            </div>
                                        @endif

                                        @if ($branch->email)
                                            <div
                                                class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                            >
                                                {{ $branch->email }}
                                            </div>
                                        @endif

                                        @if (
                                            ! $branch->phone &&
                                            ! $branch->email
                                        )
                                            <span class="text-zinc-400">
                                                មិនមាន
                                            </span>
                                        @endif
                                    </td>

                                    <td
                                        class="px-5 py-4 align-top text-zinc-700 dark:text-zinc-300"
                                    >
                                        {{
                                            $branch->manager_name
                                                ?: 'មិនទាន់កំណត់'
                                        }}
                                    </td>

                                    <td class="px-5 py-4 align-top">
                                        @if ($branch->is_active)
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

                                    <td class="px-5 py-4 align-top">
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
                                                wire:click="edit({{ $branch->id }})"
                                                title="កែប្រែ"
                                                aria-label="កែប្រែ"
                                            />

                                            {{-- Activate/deactivate --}}
                                            <flux:button
                                                type="button"
                                                size="sm"
                                                variant="ghost"
                                                :icon="$branch->is_active
                                                    ? 'pause-circle'
                                                    : 'play-circle'"
                                                square
                                                wire:click="toggleStatus({{ $branch->id }})"
                                                :title="$branch->is_active
                                                    ? 'បិទដំណើរការ'
                                                    : 'បើកដំណើរការ'"
                                                :aria-label="$branch->is_active
                                                    ? 'បិទដំណើរការ'
                                                    : 'បើកដំណើរការ'"
                                            />

                                            {{-- Delete --}}
                                            @if (! $branch->is_head_office)
                                                <flux:button
                                                    type="button"
                                                    size="sm"
                                                    variant="danger"
                                                    icon="trash"
                                                    square
                                                    wire:click="delete({{ $branch->id }})"
                                                    wire:confirm="តើអ្នកពិតជាចង់លុបសាខានេះមែនទេ?"
                                                    title="លុប"
                                                    aria-label="លុប"
                                                />
                                            @endif
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
                                            មិនមានសាខា
                                        </div>

                                        <div
                                            class="mt-2 text-sm text-zinc-500 dark:text-zinc-400"
                                        >
                                            សូមបង្កើតសាខាថ្មី
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
                    @forelse ($this->branches as $branch)
                        <div
                            wire:key="branch-mobile-{{ $branch->id }}"
                            class="space-y-4 p-5"
                        >
                            <div
                                class="flex items-start justify-between gap-4"
                            >
                                <div class="min-w-0">
                                    <div
                                        class="truncate font-medium text-zinc-900 dark:text-white"
                                    >
                                        {{ $branch->name }}
                                    </div>

                                    <div
                                        class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                    >
                                        {{ $branch->code }}

                                        @if ($branch->city)
                                            · {{ $branch->city }}
                                        @endif
                                    </div>
                                </div>

                                @if ($branch->is_active)
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

                            @if ($branch->is_head_office)
                                <flux:badge
                                    size="sm"
                                    color="blue"
                                >
                                    ការិយាល័យកណ្ដាល
                                </flux:badge>
                            @endif

                            <dl
                                class="grid grid-cols-1 gap-3 text-sm"
                            >
                                <div>
                                    <dt
                                        class="text-xs text-zinc-500 dark:text-zinc-400"
                                    >
                                        អ្នកគ្រប់គ្រង
                                    </dt>

                                    <dd
                                        class="mt-1 text-zinc-800 dark:text-zinc-200"
                                    >
                                        {{
                                            $branch->manager_name
                                                ?: 'មិនទាន់កំណត់'
                                        }}
                                    </dd>
                                </div>

                                <div>
                                    <dt
                                        class="text-xs text-zinc-500 dark:text-zinc-400"
                                    >
                                        ទំនាក់ទំនង
                                    </dt>

                                    <dd
                                        class="mt-1 text-zinc-800 dark:text-zinc-200"
                                    >
                                        {{
                                            $branch->phone
                                                ?: 'មិនមានលេខទូរស័ព្ទ'
                                        }}
                                    </dd>

                                    @if ($branch->email)
                                        <dd
                                            class="mt-1 break-all text-xs text-zinc-500 dark:text-zinc-400"
                                        >
                                            {{ $branch->email }}
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
                                    wire:click="edit({{ $branch->id }})"
                                    title="កែប្រែ"
                                    aria-label="កែប្រែ"
                                />

                                <flux:button
                                    type="button"
                                    size="sm"
                                    variant="ghost"
                                    :icon="$branch->is_active
                                        ? 'pause-circle'
                                        : 'play-circle'"
                                    square
                                    wire:click="toggleStatus({{ $branch->id }})"
                                    :title="$branch->is_active
                                        ? 'បិទដំណើរការ'
                                        : 'បើកដំណើរការ'"
                                    :aria-label="$branch->is_active
                                        ? 'បិទដំណើរការ'
                                        : 'បើកដំណើរការ'"
                                />

                                @if (! $branch->is_head_office)
                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="danger"
                                        icon="trash"
                                        square
                                        wire:click="delete({{ $branch->id }})"
                                        wire:confirm="តើអ្នកពិតជាចង់លុបសាខានេះមែនទេ?"
                                        title="លុប"
                                        aria-label="លុប"
                                    />
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-12 text-center">
                            <div
                                class="font-medium text-zinc-700 dark:text-zinc-200"
                            >
                                មិនមានសាខា
                            </div>

                            <div
                                class="mt-2 text-sm text-zinc-500 dark:text-zinc-400"
                            >
                                សូមបង្កើតសាខាថ្មី
                                ឬប្តូរពាក្យស្វែងរក។
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>