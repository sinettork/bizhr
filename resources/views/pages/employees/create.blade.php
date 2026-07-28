<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmploymentType;
use App\Models\Position;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('បង្កើតបុគ្គលិក')] class extends Component
{
    use WithFileUploads;

    public int $companyId = 0;
    public string $companyCurrency = 'USD';

    public string $branch_id = '';
    public string $department_id = '';
    public string $position_id = '';
    public string $employment_type_id = '';

    public string $employee_code = '';
    public string $first_name = '';
    public string $last_name = '';
    public string $full_name_km = '';
    public string $full_name_en = '';
    public string $gender = '';
    public string $date_of_birth = '';
    public string $phone = '';
    public string $email = '';
    public string $address = '';
    public string $city = '';
    public string $hire_date = '';
    public string $probation_end_date = '';
    public string $contract_start_date = '';
    public string $contract_end_date = '';
    public string $base_salary = '';
    public string $salary_currency = 'USD';
    public string $employment_status = 'Draft';
    public bool $is_active = true;
    public $profile_photo_upload = null;

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
        $this->companyCurrency = $company->currency ?: 'USD';
        $this->salary_currency = $this->companyCurrency;
        $this->hire_date = now()->format('Y-m-d');
        $this->setDefaultStructure();
    }

    public function updated($property, $value): void
    {
        if ($property === 'branch_id') {
            $this->department_id = '';
            $this->position_id = '';
            $this->resetValidation(['department_id', 'position_id']);
        }

        if ($property === 'department_id') {
            $this->position_id = '';
            $this->resetValidation('position_id');
        }
    }

    protected function rules(): array
    {
        return [
            'branch_id' => [
                'required',
                'integer',
                Rule::exists('branches', 'id')
                    ->where(fn ($query) => $query->where(
                        'company_id',
                        $this->companyId
                    )),
            ],
            'department_id' => [
                'required',
                'integer',
                Rule::exists('departments', 'id')
                    ->where(fn ($query) => $query->where(
                        'company_id',
                        $this->companyId
                    ))
                    ->where(fn ($query) => $query->where(
                        'branch_id',
                        $this->branch_id
                    )),
            ],
            'position_id' => [
                'nullable',
                Rule::exists('positions', 'id')
                    ->where(fn ($query) => $query->where(
                        'company_id',
                        $this->companyId
                    ))
                    ->where(fn ($query) => $query->where(
                        'branch_id',
                        $this->branch_id
                    ))
                    ->where(fn ($query) => $query->where(
                        'department_id',
                        $this->department_id
                    )),
            ],
            'employment_type_id' => [
                'nullable',
                Rule::exists('employment_types', 'id')
                    ->where(fn ($query) => $query->where(
                        'company_id',
                        $this->companyId
                    )),
            ],
            'employee_code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('employees', 'employee_code'),
            ],
            'first_name' => [
                'required',
                'string',
                'max:100',
            ],
            'last_name' => [
                'required',
                'string',
                'max:100',
            ],
            'full_name_km' => [
                'nullable',
                'string',
                'max:255',
            ],
            'full_name_en' => [
                'nullable',
                'string',
                'max:255',
            ],
            'gender' => [
                'nullable',
                Rule::in(['M', 'F', 'Other']),
            ],
            'date_of_birth' => [
                'nullable',
                'date',
                'before:today',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:30',
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
            ],
            'address' => [
                'nullable',
                'string',
                'max:500',
            ],
            'city' => [
                'nullable',
                'string',
                'max:100',
            ],
            'hire_date' => [
                'required',
                'date',
            ],
            'probation_end_date' => [
                'nullable',
                'date',
                'after_or_equal:hire_date',
            ],
            'contract_start_date' => [
                'nullable',
                'date',
            ],
            'contract_end_date' => [
                'nullable',
                'date',
                'after_or_equal:contract_start_date',
            ],
            'base_salary' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'salary_currency' => [
                'required',
                Rule::in(['USD', 'KHR']),
            ],
            'employment_status' => [
                'required',
                Rule::in([
                    'Draft',
                    'Active',
                    'On probation',
                    'On leave',
                    'Suspended',
                    'Resigned',
                    'Terminated',
                    'Retired',
                ]),
            ],
            'is_active' => [
                'boolean',
            ],
            'profile_photo_upload' => [
                'nullable',
                'image',
                'max:2048',
            ],
        ];
    }

    #[Computed]
    public function branches()
    {
        return Branch::query()
            ->where('company_id', $this->companyId)
            ->where('is_active', true)
            ->orderByDesc('is_head_office')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function availableDepartments()
    {
        if (! $this->branch_id) {
            return collect();
        }

        return Department::query()
            ->where('company_id', $this->companyId)
            ->where('branch_id', $this->branch_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function availablePositions()
    {
        if (! $this->branch_id || ! $this->department_id) {
            return collect();
        }

        return Position::query()
            ->where('company_id', $this->companyId)
            ->where('branch_id', $this->branch_id)
            ->where('department_id', $this->department_id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();
    }

    #[Computed]
    public function employmentTypes()
    {
        return EmploymentType::query()
            ->where('company_id', $this->companyId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }

    public function save(): void
    {
        $validated = $this->validate();

        $validated['employee_code'] = strtoupper(trim($validated['employee_code']));
        $validated['first_name'] = trim($validated['first_name']);
        $validated['last_name'] = trim($validated['last_name']);

        foreach ([
            'full_name_km',
            'full_name_en',
            'date_of_birth',
            'phone',
            'email',
            'address',
            'city',
            'probation_end_date',
            'contract_start_date',
            'contract_end_date',
        ] as $field) {
            $validated[$field] = filled($validated[$field])
                ? trim((string) $validated[$field])
                : null;
        }

        $validated['position_id'] = filled($validated['position_id'])
            ? (int) $validated['position_id']
            : null;

        $validated['employment_type_id'] = filled($validated['employment_type_id'])
            ? (int) $validated['employment_type_id']
            : null;

        $validated['gender'] = filled($validated['gender'])
            ? trim($validated['gender'])
            : null;

        $validated['base_salary'] = filled($validated['base_salary'])
            ? $validated['base_salary']
            : null;

        $photoPath = null;

        if ($this->profile_photo_upload) {
            $photoPath = $this->profile_photo_upload->store(
                'employees/profile-photos',
                'public'
            );
        }

        $validated['profile_photo'] = $photoPath;

        Employee::query()->create([
            ...$validated,
            'company_id' => $this->companyId,
        ]);

        Flux::toast(
            variant: 'success',
            text: 'បានបង្កើតបុគ្គលិកថ្មីដោយជោគជ័យ។'
        );

        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset([
            'branch_id',
            'department_id',
            'position_id',
            'employment_type_id',
            'employee_code',
            'first_name',
            'last_name',
            'full_name_km',
            'full_name_en',
            'gender',
            'date_of_birth',
            'phone',
            'email',
            'address',
            'city',
            'hire_date',
            'probation_end_date',
            'contract_start_date',
            'contract_end_date',
            'base_salary',
            'salary_currency',
            'employment_status',
            'is_active',
            'profile_photo_upload',
        ]);

        $this->salary_currency = $this->companyCurrency;
        $this->employment_status = 'Draft';
        $this->is_active = true;
        $this->hire_date = now()->format('Y-m-d');
        $this->currentProfilePhoto = null;
        $this->setDefaultStructure();
        $this->resetValidation();
    }

    private function setDefaultStructure(): void
    {
        $firstBranch = Branch::query()
            ->where('company_id', $this->companyId)
            ->where('is_active', true)
            ->orderByDesc('is_head_office')
            ->orderBy('name')
            ->first();

        if (! $firstBranch) {
            return;
        }

        $this->branch_id = (string) $firstBranch->id;

        $firstDepartment = Department::query()
            ->where('company_id', $this->companyId)
            ->where('branch_id', $firstBranch->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->first();

        if ($firstDepartment) {
            $this->department_id = (string) $firstDepartment->id;
        }
    }
};
?>

<div class="w-full space-y-6 p-4 sm:p-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">
                បង្កើតបុគ្គលិក
            </h1>

            <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                បំពេញព័ត៌មានបុគ្គលិកថ្មី។
            </p>
        </div>

        <flux:button
            type="button"
            variant="ghost"
            :href="route('employees.index')"
            wire:navigate
        >
            ត្រលប់ទៅបញ្ជីបុគ្គលិក
        </flux:button>
    </div>

    <form
        wire:submit="save"
        class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6"
    >
        <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                    បង្កើតបុគ្គលិកថ្មី
                </h2>
                <p class="mt-1 text-sm text-zinc-500">
                    បំពេញព័ត៌មានសំខាន់ៗ រួមទាំងសាខា ផ្នែក និងមុខតំណែង។
                </p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[180px_1fr]">
            <div>
                <div class="flex aspect-square items-center justify-center overflow-hidden rounded-xl border border-dashed border-zinc-300 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800">
                    <div class="text-center text-zinc-400">
                        <div class="text-4xl">👤</div>
                        <p class="mt-2 text-sm">មិនមានរូបភាព</p>
                    </div>
                </div>

                <label for="profile_photo_upload" class="mt-4 block text-sm font-medium text-zinc-700 dark:text-zinc-200">
                    រូបថតបុគ្គលិក
                </label>

                <input
                    id="profile_photo_upload"
                    type="file"
                    accept="image/*"
                    wire:model="profile_photo_upload"
                    class="mt-2 block w-full text-sm text-zinc-600 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-3 file:py-2 file:text-sm file:font-medium dark:text-zinc-300 dark:file:bg-zinc-800"
                />

                <p wire:loading wire:target="profile_photo_upload" class="mt-2 text-sm text-zinc-500">
                    កំពុងផ្ទុករូបភាព...
                </p>

                @error('profile_photo_upload')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-7">
                <section>
                    <h3 class="mb-4 font-medium text-zinc-900 dark:text-white">ព័ត៌មានមូលដ្ឋាន</h3>
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <flux:input wire:model="employee_code" label="លេខកូដបុគ្គលិក" placeholder="EMP-0001" required />
                        <flux:input wire:model="first_name" label="នាមខ្លួន" required />
                        <flux:input wire:model="last_name" label="នាមត្រកូល" required />
                        <flux:input wire:model="full_name_km" label="ឈ្មោះពេញជាភាសាខ្មែរ" />
                        <flux:input wire:model="full_name_en" label="ឈ្មោះពេញជាភាសាអង់គ្លេស" />
                        <flux:select wire:model="gender" label="ភេទ">
                            <flux:select.option value="">មិនបានកំណត់</flux:select.option>
                            <flux:select.option value="M">ប្រុស</flux:select.option>
                            <flux:select.option value="F">ស្រី</flux:select.option>
                            <flux:select.option value="Other">ផ្សេងៗ</flux:select.option>
                        </flux:select>
                        <flux:input wire:model="date_of_birth" type="date" label="ថ្ងៃខែឆ្នាំកំណើត" />
                        <flux:input wire:model="phone" label="លេខទូរស័ព្ទ" />
                        <flux:input wire:model="email" type="email" label="អ៊ីមែល" />
                        <flux:input wire:model="city" label="រាជធានី/ខេត្ត" />
                        <div class="md:col-span-2 xl:col-span-4">
                            <flux:textarea wire:model="address" label="អាសយដ្ឋាន" rows="2" />
                        </div>
                    </div>
                </section>

                <section>
                    <h3 class="mb-4 font-medium text-zinc-900 dark:text-white">រចនាសម្ព័ន្ធការងារ</h3>
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <flux:select wire:model.live="branch_id" label="សាខា" required>
                            <flux:select.option value="">ជ្រើសរើសសាខា</flux:select.option>
                            @foreach ($this->branches as $branch)
                                <flux:select.option value="{{ $branch->id }}">{{ $branch->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:select wire:model.live="department_id" label="ផ្នែក" required>
                            <flux:select.option value="">ជ្រើសរើសផ្នែក</flux:select.option>
                            @foreach ($this->availableDepartments as $department)
                                <flux:select.option value="{{ $department->id }}">{{ $department->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:select wire:model="position_id" label="មុខតំណែង">
                            <flux:select.option value="">មិនទាន់កំណត់</flux:select.option>
                            @foreach ($this->availablePositions as $position)
                                <flux:select.option value="{{ $position->id }}">{{ $position->title }}</flux:select.option>
                            @endforeach
                        </flux:select>
                        <flux:select wire:model="employment_type_id" label="ប្រភេទការងារ">
                            <flux:select.option value="">មិនទាន់កំណត់</flux:select.option>
                            @foreach ($this->employmentTypes as $employmentType)
                                <flux:select.option value="{{ $employmentType->id }}">{{ $employmentType->name }}</flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>
                </section>

                <section>
                    <h3 class="mb-4 font-medium text-zinc-900 dark:text-white">កាលបរិច្ឆេទ និងប្រាក់ខែ</h3>
                    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <flux:input wire:model="hire_date" type="date" label="ថ្ងៃចូលធ្វើការ" required />
                        <flux:input wire:model="probation_end_date" type="date" label="ថ្ងៃបញ្ចប់សាកល្បង" />
                        <flux:input wire:model="contract_start_date" type="date" label="ថ្ងៃចាប់ផ្ដើមកិច្ចសន្យា" />
                        <flux:input wire:model="contract_end_date" type="date" label="ថ្ងៃបញ្ចប់កិច្ចសន្យា" />
                        <flux:input wire:model="base_salary" type="number" min="0" step="0.01" label="ប្រាក់ខែគោល" />
                        <flux:select wire:model="salary_currency" label="រូបិយប័ណ្ណ">
                            <flux:select.option value="USD">USD</flux:select.option>
                            <flux:select.option value="KHR">KHR</flux:select.option>
                        </flux:select>
                        <flux:select wire:model="employment_status" label="ស្ថានភាពការងារ" required>
                            <flux:select.option value="Draft">ព្រាង</flux:select.option>
                            <flux:select.option value="Active">កំពុងបម្រើការងារ</flux:select.option>
                            <flux:select.option value="On probation">កំពុងសាកល្បង</flux:select.option>
                            <flux:select.option value="On leave">កំពុងឈប់សម្រាក</flux:select.option>
                            <flux:select.option value="Suspended">ផ្អាកការងារ</flux:select.option>
                            <flux:select.option value="Resigned">លាឈប់</flux:select.option>
                            <flux:select.option value="Terminated">បញ្ចប់កិច្ចសន្យា</flux:select.option>
                            <flux:select.option value="Retired">ចូលនិវត្តន៍</flux:select.option>
                        </flux:select>
                        <div class="flex items-end pb-2">
                            <flux:checkbox wire:model="is_active" label="បុគ្គលិកកំពុងដំណើរការ" />
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <div class="mt-7 flex flex-wrap justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700">
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="save">
                <span wire:loading.remove wire:target="save">បង្កើតបុគ្គលិក</span>
                <span wire:loading wire:target="save">កំពុងរក្សាទុក...</span>
            </flux:button>
            <flux:button type="button" variant="ghost" :href="route('employees.index')" wire:navigate>បោះបង់</flux:button>
        </div>
    </form>
</div>
