<?php

use App\Models\Branch;
use App\Models\Company;
use Flux\Flux;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('បង្កើតសាខា')] class extends Component
{
    public int $companyId = 0;

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

            'manager_name.max' => 'ឈ្មោះអ្នកគ្រប់គ្រងមិនអាចលើសពី ២៥៥ តួអក្សរ។',

            'phone.max' => 'លេខទូរស័ព្ទមិនអាចលើសពី ៥០ តួអក្សរ។',

            'email.email' => 'ទម្រង់អ៊ីមែលមិនត្រឹមត្រូវ។',

            'email.max' => 'អ៊ីមែលមិនអាចលើសពី ២៥៥ តួអក្សរ។',

            'city.max' => 'ឈ្មោះរាជធានី ឬខេត្តមិនអាចលើសពី ១០០ តួអក្សរ។',

            'address.max' => 'អាសយដ្ឋានមិនអាចលើសពី ១០០០ តួអក្សរ។',
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $validated['name'] = trim($validated['name']);

        $validated['code'] = strtoupper(
            trim($validated['code'])
        );

        foreach ([
            'manager_name',
            'phone',
            'email',
            'city',
            'address',
        ] as $field) {
            $validated[$field] = filled($validated[$field])
                ? trim((string) $validated[$field])
                : null;
        }

        DB::transaction(function () use ($validated): void {
            if ($validated['is_head_office']) {
                Branch::query()
                    ->where('company_id', $this->companyId)
                    ->update([
                        'is_head_office' => false,
                    ]);
            }

            Branch::query()->create([
                ...$validated,
                'company_id' => $this->companyId,
            ]);
        });

        Flux::toast(
            variant: 'success',
            text: 'បានបង្កើតសាខាថ្មីដោយជោគជ័យ។'
        );

        $this->redirectRoute(
            'branches.index',
            navigate: true
        );
    }

    public function resetForm(): void
    {
        $this->reset([
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
};

?>

<div class="w-full p-4 sm:p-6">

    <form
        wire:submit="save"
        class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900"
    >
        {{-- Form heading --}}
        <div
            class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <h1
                    class="text-xl font-semibold text-zinc-900 dark:text-white"
                >
                    បង្កើតសាខាថ្មី
                </h1>

                <p
                    class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
                >
                    បំពេញព័ត៌មានសាខា ទំនាក់ទំនង និងការកំណត់សំខាន់ៗ។
                </p>
            </div>

            <flux:button
                type="button"
                variant="ghost"
                icon="arrow-left"
                :href="route('branches.index')"
                wire:navigate
            >
                ត្រឡប់
            </flux:button>
        </div>

        <div class="space-y-6">

            {{-- Basic information --}}
            <div>
                <h2
                    class="mb-4 text-base font-semibold text-zinc-900 dark:text-white"
                >
                    ព័ត៌មានមូលដ្ឋាន
                </h2>

                <div
                    class="grid grid-cols-1 gap-x-4 gap-y-4 md:grid-cols-2 xl:grid-cols-4"
                >
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
                        wire:model="city"
                        label="រាជធានី ឬខេត្ត"
                        placeholder="ឧទាហរណ៍៖ ភ្នំពេញ"
                    />

                    <flux:input
                        wire:model="manager_name"
                        label="ឈ្មោះអ្នកគ្រប់គ្រង"
                        placeholder="ឈ្មោះអ្នកគ្រប់គ្រងសាខា"
                    />
                </div>
            </div>

            {{-- Contact information --}}
            <div>
                <h2
                    class="mb-4 text-base font-semibold text-zinc-900 dark:text-white"
                >
                    ព័ត៌មានទំនាក់ទំនង
                </h2>

                <div
                    class="grid grid-cols-1 gap-x-4 gap-y-4 md:grid-cols-2 xl:grid-cols-4"
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
                        placeholder="branch@example.com"
                    />

                    <div class="md:col-span-2">
                        <flux:textarea
                            wire:model="address"
                            label="អាសយដ្ឋាន"
                            rows="2"
                            placeholder="ផ្ទះលេខ ផ្លូវ សង្កាត់ ខណ្ឌ រាជធានី ឬខេត្ត"
                        />
                    </div>
                </div>
            </div>

            {{-- Branch settings --}}
            <div>
                <h2
                    class="mb-4 text-base font-semibold text-zinc-900 dark:text-white"
                >
                    ការកំណត់សាខា
                </h2>

                <div
                    class="grid grid-cols-1 gap-x-6 gap-y-4 md:grid-cols-2 xl:grid-cols-4"
                >
                    <div class="xl:col-span-2">
                        <flux:checkbox
                            wire:model="is_head_office"
                            label="កំណត់ជាការិយាល័យកណ្ដាល"
                            description="សាខានេះនឹងក្លាយជាការិយាល័យកណ្ដាលរបស់ក្រុមហ៊ុន។"
                        />
                    </div>

                    <div class="xl:col-span-2">
                        <flux:checkbox
                            wire:model="is_active"
                            label="បើកដំណើរការសាខា"
                            description="អនុញ្ញាតឱ្យសាខានេះប្រើប្រាស់នៅក្នុងប្រព័ន្ធ។"
                        />
                    </div>
                </div>
            </div>
        </div>

        {{-- Form actions --}}
        <div
            class="mt-6 flex flex-col-reverse gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-end"
        >
            <flux:button
                type="button"
                variant="ghost"
                :href="route('branches.index')"
                wire:navigate
                class="w-full sm:w-auto"
            >
                បោះបង់
            </flux:button>

            <flux:button
                type="button"
                variant="ghost"
                icon="arrow-path"
                wire:click="resetForm"
                wire:loading.attr="disabled"
                wire:target="resetForm"
                class="w-full sm:w-auto"
            >
                សម្អាត
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
                    បង្កើតសាខា
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
</div>