<?php

use App\Models\Company;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('ព័ត៌មានក្រុមហ៊ុន')] class extends Component
{
    public string $name = '';
    public string $legal_name = '';

    public string $email = '';
    public string $phone = '';
    public string $website = '';

    public string $registration_number = '';
    public string $tax_id = '';

    public string $address = '';
    public string $city = '';
    public string $country = 'Cambodia';

    public string $currency = 'USD';
    public string $timezone = 'Asia/Phnom_Penh';
    public string $date_format = 'd/m/Y';

    public function mount(): void
    {
        $company = Company::query()->first();

        if (! $company) {
            return;
        }

        $this->name = $company->name;
        $this->legal_name = (string) $company->legal_name;

        $this->email = (string) $company->email;
        $this->phone = (string) $company->phone;
        $this->website = (string) $company->website;

        $this->registration_number =
            (string) $company->registration_number;

        $this->tax_id = (string) $company->tax_id;

        $this->address = (string) $company->address;
        $this->city = (string) $company->city;
        $this->country = $company->country;

        $this->currency = $company->currency;
        $this->timezone = $company->timezone;
        $this->date_format = $company->date_format;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'legal_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:50',
            ],
            'website' => [
                'nullable',
                'url',
                'max:255',
            ],
            'registration_number' => [
                'nullable',
                'string',
                'max:100',
            ],
            'tax_id' => [
                'nullable',
                'string',
                'max:100',
            ],
            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'city' => [
                'nullable',
                'string',
                'max:100',
            ],
            'country' => [
                'required',
                'string',
                'max:100',
            ],
            'currency' => [
                'required',
                'string',
                'size:3',
            ],
            'timezone' => [
                'required',
                'string',
                'max:100',
            ],
            'date_format' => [
                'required',
                'string',
                'max:20',
            ],
        ]);

        $company = Company::query()->first() ?? new Company();

        $company->fill($validated);
        $company->save();

        Flux::toast(
            variant: 'success',
            text: 'បានរក្សាទុកព័ត៌មានក្រុមហ៊ុនដោយជោគជ័យ។',
        );
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
                ព័ត៌មានក្រុមហ៊ុន
            </h1>

            <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                គ្រប់គ្រងព័ត៌មានទូទៅ ទំនាក់ទំនង អាសយដ្ឋាន និងការកំណត់តំបន់។
            </p>
        </div>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div
            class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6"
        >
            <div>
                <h2
                    class="text-lg font-semibold text-zinc-900 dark:text-white"
                >
                    ព័ត៌មានទូទៅ
                </h2>

                <p class="mt-1 text-sm text-zinc-500">
                    ព័ត៌មានផ្លូវការ និងលេខសម្គាល់របស់ក្រុមហ៊ុន។
                </p>
            </div>

            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <flux:input
                    wire:model="name"
                    label="ឈ្មោះក្រុមហ៊ុន"
                    placeholder="ឈ្មោះក្រុមហ៊ុន"
                    required
                />

                <flux:input
                    wire:model="legal_name"
                    label="ឈ្មោះផ្លូវការ"
                    placeholder="ឈ្មោះផ្លូវការរបស់ក្រុមហ៊ុន"
                />

                <flux:input
                    wire:model="registration_number"
                    label="លេខចុះបញ្ជី"
                    placeholder="លេខចុះបញ្ជីអាជីវកម្ម"
                />

                <flux:input
                    wire:model="tax_id"
                    label="លេខសម្គាល់ពន្ធ"
                    placeholder="លេខសម្គាល់ពន្ធ"
                />
            </div>
        </div>

        <div
            class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6"
        >
            <div>
                <h2
                    class="text-lg font-semibold text-zinc-900 dark:text-white"
                >
                    ព័ត៌មានទំនាក់ទំនង
                </h2>

                <p class="mt-1 text-sm text-zinc-500">
                    មធ្យោបាយទំនាក់ទំនងសំខាន់ៗរបស់ក្រុមហ៊ុន។
                </p>
            </div>

            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <flux:input
                    wire:model="email"
                    label="អ៊ីមែល"
                    type="email"
                    placeholder="info@example.com"
                />

                <flux:input
                    wire:model="phone"
                    label="លេខទូរស័ព្ទ"
                    placeholder="+855..."
                />

                <div class="md:col-span-2">
                    <flux:input
                        wire:model="website"
                        label="គេហទំព័រ"
                        type="url"
                        placeholder="https://example.com"
                    />
                </div>
            </div>
        </div>

        <div
            class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6"
        >
            <div>
                <h2
                    class="text-lg font-semibold text-zinc-900 dark:text-white"
                >
                    អាសយដ្ឋាន
                </h2>

                <p class="mt-1 text-sm text-zinc-500">
                    ទីតាំងការិយាល័យចម្បងរបស់ក្រុមហ៊ុន។
                </p>
            </div>

            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <flux:textarea
                        wire:model="address"
                        label="អាសយដ្ឋាន"
                        rows="3"
                    />
                </div>

                <flux:input
                    wire:model="city"
                    label="រាជធានី/ខេត្ត"
                />

                <flux:input
                    wire:model="country"
                    label="ប្រទេស"
                    required
                />
            </div>
        </div>

        <div
            class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6"
        >
            <div>
                <h2
                    class="text-lg font-semibold text-zinc-900 dark:text-white"
                >
                    ការកំណត់តំបន់
                </h2>

                <p class="mt-1 text-sm text-zinc-500">
                    រូបិយប័ណ្ណ តំបន់ពេលវេលា និងទម្រង់កាលបរិច្ឆេទ។
                </p>
            </div>

            <div class="mt-6 grid gap-5 md:grid-cols-3">
                <flux:select
                    wire:model="currency"
                    label="រូបិយប័ណ្ណ"
                >
                    <flux:select.option value="USD">
                        USD — ដុល្លារ
                    </flux:select.option>

                    <flux:select.option value="KHR">
                        KHR — រៀល
                    </flux:select.option>
                </flux:select>

                <flux:select
                    wire:model="timezone"
                    label="តំបន់ពេលវេលា"
                >
                    <flux:select.option value="Asia/Phnom_Penh">
                        Asia/Phnom_Penh
                    </flux:select.option>

                    <flux:select.option value="Asia/Bangkok">
                        Asia/Bangkok
                    </flux:select.option>

                    <flux:select.option value="Asia/Singapore">
                        Asia/Singapore
                    </flux:select.option>

                    <flux:select.option value="UTC">
                        UTC
                    </flux:select.option>
                </flux:select>

                <flux:select
                    wire:model="date_format"
                    label="ទម្រង់កាលបរិច្ឆេទ"
                >
                    <flux:select.option value="d/m/Y">
                        ថ្ងៃ/ខែ/ឆ្នាំ
                    </flux:select.option>

                    <flux:select.option value="m/d/Y">
                        ខែ/ថ្ងៃ/ឆ្នាំ
                    </flux:select.option>

                    <flux:select.option value="Y-m-d">
                        ឆ្នាំ-ខែ-ថ្ងៃ
                    </flux:select.option>
                </flux:select>
            </div>
        </div>

        <div
            class="flex flex-wrap justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700"
        >
            <flux:button
                type="submit"
                variant="primary"
                wire:loading.attr="disabled"
                wire:target="save"
            >
                <span wire:loading.remove wire:target="save">
                    រក្សាទុកការកំណត់
                </span>

                <span wire:loading wire:target="save">
                    កំពុងរក្សាទុក...
                </span>
            </flux:button>
        </div>
    </form>
</div>
