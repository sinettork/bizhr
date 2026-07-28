<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('បង្កើតផ្នែក')] class extends Component
{
    public int $companyId = 0;

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

        $this->setDefaultBranch();
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
                    ),
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

            'manager_name.max' =>
                'ឈ្មោះប្រធានផ្នែកមិនអាចលើសពី ២៥៥ តួអក្សរ។',

            'phone.max' =>
                'លេខទូរស័ព្ទមិនអាចលើសពី ៥០ តួអក្សរ។',

            'email.email' =>
                'ទម្រង់អ៊ីមែលមិនត្រឹមត្រូវ។',

            'email.max' =>
                'អ៊ីមែលមិនអាចលើសពី ២៥៥ តួអក្សរ។',

            'description.max' =>
                'សេចក្ដីពិពណ៌នាមិនអាចលើសពី ២០០០ តួអក្សរ។',
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
            ->where('is_active', true)
            ->orderByDesc('is_head_office')
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

        Department::query()->create([
            ...$validated,
            'company_id' => $this->companyId,
        ]);

        Flux::toast(
            variant: 'success',
            text: 'បានបង្កើតផ្នែកថ្មីដោយជោគជ័យ។'
        );

        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset([
            'branch_id',
            'name',
            'code',
            'manager_name',
            'phone',
            'email',
            'description',
            'is_active',
        ]);

        $this->is_active = true;

        $this->setDefaultBranch();

        $this->resetValidation();
    }

    private function setDefaultBranch(): void
    {
        $firstBranch = Branch::query()
            ->where(
                'company_id',
                $this->companyId
            )
            ->where('is_active', true)
            ->orderByDesc('is_head_office')
            ->orderBy('name')
            ->first();

        $this->branch_id = $firstBranch
            ? (string) $firstBranch->id
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
                បង្កើតផ្នែក
            </h1>

            <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                ជ្រើសរើសសាខា និងបំពេញព័ត៌មានផ្នែកថ្មី។
            </p>
        </div>

        <flux:button
            type="button"
            variant="ghost"
            icon="arrow-left"
            :href="route('departments.index')"
            wire:navigate
        >
            ត្រឡប់ទៅបញ្ជីផ្នែក
        </flux:button>
    </div>

    @if ($this->branches->isEmpty())

        {{-- No active branches --}}
        <div
            class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-amber-800 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-200"
        >
            <h2 class="font-semibold">
                មិនទាន់មានសាខាដែលកំពុងដំណើរការ
            </h2>

            <p class="mt-1 text-sm">
                សូមបង្កើត ឬបើកដំណើរការសាខាជាមុនសិន។
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

        {{-- Create form --}}
        <form
            wire:submit="save"
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6"
        >
            {{-- Form heading --}}
            <div class="mb-6">
                <h2
                    class="text-lg font-semibold text-zinc-900 dark:text-white"
                >
                    ព័ត៌មានផ្នែកថ្មី
                </h2>

                <p
                    class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
                >
                    បំពេញព័ត៌មានសំខាន់ៗ ទំនាក់ទំនង
                    និងស្ថានភាពផ្នែក។
                </p>
            </div>

            <div class="space-y-7">

                {{-- Basic information --}}
                <section>
                    <h3
                        class="mb-4 font-medium text-zinc-900 dark:text-white"
                    >
                        ព័ត៌មានមូលដ្ឋាន
                    </h3>

                    <div
                        class="grid gap-4 md:grid-cols-2 xl:grid-cols-4"
                    >
                        <flux:select
                            wire:model="branch_id"
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
                    </div>
                </section>

                {{-- Contact information --}}
                <section>
                    <h3
                        class="mb-4 font-medium text-zinc-900 dark:text-white"
                    >
                        ទំនាក់ទំនង និងព័ត៌មានបន្ថែម
                    </h3>

                    <div
                        class="grid gap-4 md:grid-cols-2 xl:grid-cols-4"
                    >
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

                        <div class="md:col-span-2 xl:col-span-2">
                            <flux:textarea
                                wire:model="description"
                                label="សេចក្ដីពិពណ៌នា"
                                rows="2"
                                placeholder="ពិពណ៌នាអំពីតួនាទីរបស់ផ្នែក..."
                            />
                        </div>

                        <div
                            class="flex items-end pb-2 md:col-span-2 xl:col-span-4"
                        >
                            <flux:checkbox
                                wire:model="is_active"
                                label="ផ្នែកកំពុងដំណើរការ"
                            />
                        </div>
                    </div>
                </section>
            </div>

            {{-- Form actions --}}
            <div
                class="mt-7 flex flex-wrap justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700"
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
                        បង្កើតផ្នែក
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
                    icon="arrow-path"
                    wire:click="resetForm"
                    wire:loading.attr="disabled"
                    wire:target="resetForm"
                >
                    សម្អាត
                </flux:button>

                <flux:button
                    type="button"
                    variant="ghost"
                    :href="route('departments.index')"
                    wire:navigate
                >
                    បោះបង់
                </flux:button>
            </div>
        </form>
    @endif
</div>