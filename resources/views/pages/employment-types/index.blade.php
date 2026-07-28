<?php

use App\Models\Company;
use App\Models\EmploymentType;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('គ្រប់គ្រងប្រភេទការងារ')] class extends Component
{
    public int $companyId = 0;

    public ?int $employmentTypeId = null;

    public string $search = '';

    public string $name = '';
    public string $code = '';
    public string $description = '';

    public bool $is_active = true;

    public int $sort_order = 0;

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

                Rule::unique('employment_types', 'code')
                    ->where(
                        fn ($query) => $query->where(
                            'company_id',
                            $this->companyId
                        )
                    )
                    ->ignore($this->employmentTypeId),
            ],

            'description' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'is_active' => [
                'boolean',
            ],

            'sort_order' => [
                'required',
                'integer',
                'min:0',
                'max:9999',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' =>
                'សូមបញ្ចូលឈ្មោះប្រភេទការងារ។',

            'name.string' =>
                'ឈ្មោះប្រភេទការងារត្រូវតែជាអក្សរ។',

            'name.max' =>
                'ឈ្មោះប្រភេទការងារមិនអាចលើសពី ២៥៥ តួអក្សរ។',

            'code.required' =>
                'សូមបញ្ចូលលេខកូដប្រភេទការងារ។',

            'code.string' =>
                'លេខកូដប្រភេទការងារត្រូវតែជាអក្សរ។',

            'code.max' =>
                'លេខកូដមិនអាចលើសពី ៥០ តួអក្សរ។',

            'code.unique' =>
                'លេខកូដនេះត្រូវបានប្រើរួចហើយ។',

            'description.string' =>
                'សេចក្ដីពិពណ៌នាត្រូវតែជាអក្សរ។',

            'description.max' =>
                'សេចក្ដីពិពណ៌នាមិនអាចលើសពី ៣០០០ តួអក្សរ។',

            'sort_order.required' =>
                'សូមបញ្ចូលលំដាប់បង្ហាញ។',

            'sort_order.integer' =>
                'លំដាប់បង្ហាញត្រូវតែជាលេខគត់។',

            'sort_order.min' =>
                'លំដាប់បង្ហាញមិនអាចតិចជាងសូន្យ។',

            'sort_order.max' =>
                'លំដាប់បង្ហាញមិនអាចលើសពី ៩៩៩៩។',
        ];
    }

    #[Computed]
    public function employmentTypes()
    {
        return EmploymentType::query()
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
                                    'description',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function updatedSearch(): void
    {
        unset($this->employmentTypes);
    }

    public function save(): void
    {
        $validated = $this->validate();

        $validated['name'] = trim($validated['name']);
        $validated['code'] = strtoupper(
            trim($validated['code'])
        );

        $validated['description'] =
            filled($validated['description'])
                ? trim($validated['description'])
                : null;

        if ($this->employmentTypeId !== null) {
            $employmentType = $this->findEmploymentType(
                $this->employmentTypeId
            );

            $employmentType->update($validated);

            Flux::toast(
                variant: 'success',
                text: 'បានកែប្រែប្រភេទការងារដោយជោគជ័យ។'
            );
        } else {
            EmploymentType::query()->create([
                ...$validated,
                'company_id' => $this->companyId,
            ]);

            Flux::toast(
                variant: 'success',
                text: 'បានបង្កើតប្រភេទការងារថ្មីដោយជោគជ័យ។'
            );
        }

        unset($this->employmentTypes);

        $this->resetForm();
    }

    public function edit(int $employmentTypeId): void
    {
        $employmentType = $this->findEmploymentType(
            $employmentTypeId
        );

        $this->employmentTypeId = $employmentType->id;
        $this->name = $employmentType->name;
        $this->code = $employmentType->code;

        $this->description =
            (string) $employmentType->description;

        $this->is_active = $employmentType->is_active;
        $this->sort_order = $employmentType->sort_order;

        $this->resetValidation();
    }

    public function toggleStatus(
        int $employmentTypeId
    ): void {
        $employmentType = $this->findEmploymentType(
            $employmentTypeId
        );

        $newStatus = ! $employmentType->is_active;

        $employmentType->update([
            'is_active' => $newStatus,
        ]);

        unset($this->employmentTypes);

        Flux::toast(
            variant: 'success',
            text: $newStatus
                ? 'បានបើកដំណើរការប្រភេទការងារ។'
                : 'បានបិទដំណើរការប្រភេទការងារ។'
        );
    }

    public function delete(int $employmentTypeId): void
    {
        $employmentType = $this->findEmploymentType(
            $employmentTypeId
        );

        $employmentType->delete();

        if (
            $this->employmentTypeId
            === $employmentTypeId
        ) {
            $this->resetForm();
        }

        unset($this->employmentTypes);

        Flux::toast(
            variant: 'success',
            text: 'បានលុបប្រភេទការងារដោយជោគជ័យ។'
        );
    }

    public function resetForm(): void
    {
        $this->reset([
            'employmentTypeId',
            'name',
            'code',
            'description',
            'is_active',
            'sort_order',
        ]);

        $this->is_active = true;
        $this->sort_order = 0;

        $this->resetValidation();
    }

    private function findEmploymentType(
        int $employmentTypeId
    ): EmploymentType {
        return EmploymentType::query()
            ->where('company_id', $this->companyId)
            ->findOrFail($employmentTypeId);
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
                គ្រប់គ្រងប្រភេទការងារ
            </h1>

            <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                បង្កើត និងគ្រប់គ្រងប្រភេទការងារសម្រាប់បុគ្គលិក។
            </p>
        </div>

        @if ($employmentTypeId)
            <flux:button
                type="button"
                variant="ghost"
                icon="plus"
                wire:click="resetForm"
            >
                បង្កើតប្រភេទថ្មី
            </flux:button>
        @endif
    </div>

    <div class="grid gap-6 xl:grid-cols-[380px_1fr]">

        {{-- Create/Edit form --}}
        <form
            wire:submit="save"
            class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <div>
                <h2
                    class="text-lg font-semibold text-zinc-900 dark:text-white"
                >
                    {{ $employmentTypeId
                        ? 'កែប្រែប្រភេទការងារ'
                        : 'បង្កើតប្រភេទការងារថ្មី' }}
                </h2>

                <p
                    class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
                >
                    បំពេញព័ត៌មានប្រភេទការងារខាងក្រោម។
                </p>
            </div>

            <flux:input
                wire:model="name"
                label="ឈ្មោះប្រភេទការងារ"
                placeholder="ឧទាហរណ៍៖ ពេញម៉ោង"
                required
            />

            <flux:input
                wire:model="code"
                label="លេខកូដ"
                placeholder="ឧទាហរណ៍៖ FULL-TIME"
                required
            />

            <flux:textarea
                wire:model="description"
                label="សេចក្ដីពិពណ៌នា"
                rows="4"
                placeholder="ពិពណ៌នាអំពីប្រភេទការងារ..."
            />

            <flux:input
                wire:model="sort_order"
                type="number"
                min="0"
                max="9999"
                label="លំដាប់បង្ហាញ"
                placeholder="0"
            />

            <flux:checkbox
                wire:model="is_active"
                label="ប្រភេទការងារកំពុងដំណើរការ"
            />

            <div class="flex flex-wrap gap-3">
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
                        {{ $employmentTypeId
                            ? 'រក្សាទុកការកែប្រែ'
                            : 'បង្កើតប្រភេទការងារ' }}
                    </span>

                    <span
                        wire:loading
                        wire:target="save"
                    >
                        កំពុងរក្សាទុក...
                    </span>
                </flux:button>

                @if ($employmentTypeId)
                    <flux:button
                        type="button"
                        variant="ghost"
                        wire:click="resetForm"
                    >
                        បោះបង់
                    </flux:button>
                @endif
            </div>
        </form>

        {{-- Employment type list --}}
        <div class="min-w-0">
            <div
                class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
            >
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
                                បញ្ជីប្រភេទការងារ
                            </h2>

                            <p class="mt-1 text-sm text-zinc-500">
                                ចំនួនសរុប៖
                                {{ $this->employmentTypes->count() }}
                            </p>
                        </div>

                        <div class="w-full sm:w-72">
                            <flux:input
                                wire:model.live.debounce.300ms="search"
                                icon="magnifying-glass"
                                placeholder="ស្វែងរក..."
                                clearable
                            />
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead
                            class="bg-zinc-50 dark:bg-zinc-800"
                        >
                            <tr>
                                <th class="px-5 py-4 font-medium">
                                    ប្រភេទការងារ
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    សេចក្ដីពិពណ៌នា
                                </th>

                                <th class="px-5 py-4 font-medium">
                                    លំដាប់
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
                                $this->employmentTypes
                                as $employmentType
                            )
                                <tr
                                    wire:key="employment-type-{{ $employmentType->id }}"
                                    class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                                >
                                    <td class="px-5 py-4">
                                        <div
                                            class="font-medium text-zinc-900 dark:text-white"
                                        >
                                            {{ $employmentType->name }}
                                        </div>

                                        <div
                                            class="mt-1 text-xs text-zinc-500"
                                        >
                                            {{ $employmentType->code }}
                                        </div>
                                    </td>

                                    <td class="max-w-xs px-5 py-4">
                                        @if ($employmentType->description)
                                            <p
                                                class="line-clamp-2 text-zinc-600 dark:text-zinc-300"
                                            >
                                                {{ $employmentType->description }}
                                            </p>
                                        @else
                                            <span class="text-zinc-400">
                                                មិនមានការពិពណ៌នា
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-5 py-4">
                                        {{ $employmentType->sort_order }}
                                    </td>

                                    <td class="px-5 py-4">
                                        @if ($employmentType->is_active)
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
                                                wire:click="edit({{ $employmentType->id }})"
                                                title="កែប្រែ"
                                                aria-label="កែប្រែ"
                                            />

                                            <flux:button
                                                type="button"
                                                size="sm"
                                                variant="ghost"
                                                :icon="$employmentType->is_active
                                                    ? 'pause-circle'
                                                    : 'play-circle'"
                                                square
                                                wire:click="toggleStatus({{ $employmentType->id }})"
                                                title="{{ $employmentType->is_active
                                                    ? 'បិទដំណើរការ'
                                                    : 'បើកដំណើរការ' }}"
                                                aria-label="{{ $employmentType->is_active
                                                    ? 'បិទដំណើរការ'
                                                    : 'បើកដំណើរការ' }}"
                                            />

                                            <flux:button
                                                type="button"
                                                size="sm"
                                                variant="danger"
                                                icon="trash"
                                                square
                                                wire:click="delete({{ $employmentType->id }})"
                                                wire:confirm="តើអ្នកពិតជាចង់លុបប្រភេទការងារនេះមែនទេ?"
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
                                            មិនមានប្រភេទការងារ
                                        </div>

                                        <div
                                            class="mt-2 text-sm text-zinc-500"
                                        >
                                            សូមបង្កើតប្រភេទការងារថ្មី
                                            ឬប្តូរពាក្យស្វែងរក។
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>