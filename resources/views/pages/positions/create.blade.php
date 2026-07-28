<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Position;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('បង្កើតមុខតំណែង')] class extends Component
{
    public int $companyId = 0;

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

        $this->syncDepartment();
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
                    ->where(fn ($query) => $query->where(
                        'company_id',
                        $this->companyId
                    )),
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
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'branch_id.required' => 'សូមជ្រើសរើសសាខា។',
            'branch_id.exists' => 'សាខាដែលបានជ្រើសរើសមិនត្រឹមត្រូវ។',
            'department_id.required' => 'សូមជ្រើសរើសផ្នែក។',
            'department_id.exists' => 'ផ្នែកដែលបានជ្រើសរើសមិនត្រឹមត្រូវ។',
            'title.required' => 'សូមបញ្ចូលចំណងជើងមុខតំណែង។',
            'title.max' => 'ចំណងជើងមុខតំណែងអាចត្រូវបានត្រឹមតែ ២៥៥ តួអក្សរ។',
            'code.required' => 'សូមបញ្ចូលលេខកូដមុខតំណែង។',
            'code.unique' => 'លេខកូដ​មុខតំណែងនេះបានប្រើរួចហើយ។',
            'code.max' => 'លេខកូដមុខតំណែងអាចត្រូវបានត្រឹមតែ ៥០ តួអក្សរ។',
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
            ->where('company_id', $this->companyId)
            ->when(
                filled($this->branch_id),
                fn ($query) => $query->where('branch_id', $this->branch_id)
            )
            ->orderBy('name')
            ->get();
    }

    public function updatedBranchId(): void
    {
        $this->department_id = '';
    }

    public function save(): void
    {
        $validated = $this->validate();

        Position::query()->create([
            'company_id' => $this->companyId,
            'branch_id' => $validated['branch_id'],
            'department_id' => $validated['department_id'],
            'title' => $validated['title'],
            'code' => $validated['code'],
            'description' => $validated['description'],
            'minimum_salary' => $validated['minimum_salary'],
            'maximum_salary' => $validated['maximum_salary'],
            'is_manager_position' => $validated['is_manager_position'],
            'is_active' => $validated['is_active'],
            'sort_order' => $validated['sort_order'],
        ]);

        Flux::toast(
            variant: 'success',
            text: 'បានបង្កើតមុខតំណែងថ្មីដោយជោគជ័យ។'
        );

        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset([
            'branch_id',
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

        $this->is_active = true;
        $this->sort_order = '0';

        $this->syncDepartment();
        $this->resetValidation();
    }

    private function syncDepartment(): void
    {
        if (! filled($this->branch_id)) {
            $this->department_id = '';
            return;
        }

        $department = Department::query()
            ->where('company_id', $this->companyId)
            ->where('branch_id', $this->branch_id)
            ->orderBy('name')
            ->first();

        $this->department_id = $department
            ? (string) $department->id
            : '';
    }
};
?>

<div class="w-full space-y-6 p-4 sm:p-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">
                បង្កើតមុខតំណែង
            </h1>

            <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                ជ្រើសរើសសាខា និងផ្នែក សម្រាប់មុខតំណែងថ្មី។
            </p>
        </div>

        <flux:button
            type="button"
            variant="ghost"
            :href="route('positions.index')"
            wire:navigate
        >
            ត្រលប់ទៅបញ្ជីមុខតំណែង
        </flux:button>
    </div>

    <form
        wire:submit="save"
        class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
    >
        <flux:select
            wire:model.live="branch_id"
            label="សាខា"
            required
        >
            <flux:select.option value="">ជ្រើសរើសសាខា</flux:select.option>
            @foreach ($this->branches as $branch)
                <flux:select.option value="{{ $branch->id }}">
                    {{ $branch->name }}
                </flux:select.option>
            @endforeach
        </flux:select>

        <flux:select
            wire:model="department_id"
            label="ផ្នែក"
            required
        >
            <flux:select.option value="">ជ្រើសរើសផ្នែក</flux:select.option>
            @foreach ($this->departments as $department)
                <flux:select.option value="{{ $department->id }}">
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
            label="លេខកូដ"
            placeholder="ឧទាហរណ៍៖ POS001"
            required
        />

        <div class="grid gap-4 sm:grid-cols-2">
            <flux:input
                wire:model="minimum_salary"
                type="number"
                label="ប្រាក់ខែអប្បបរមា"
                placeholder="0"
            />

            <flux:input
                wire:model="maximum_salary"
                type="number"
                label="ប្រាក់ខែអតិបរមា"
                placeholder="0"
            />
        </div>

        <flux:textarea
            wire:model="description"
            label="ការពិពណ៌នា"
            rows="4"
            placeholder="ពិពណ៌នាមុខតំណែង"
        />

        <flux:input
            wire:model="sort_order"
            type="number"
            label="លំដាប់"
            placeholder="0"
        />

        <div class="space-y-4">
            <flux:checkbox
                wire:model="is_manager_position"
                label="មុខតំណែងបុគ្គលិកគ្រប់គ្រង"
            />

            <flux:checkbox
                wire:model="is_active"
                label="មុខតំណែងកំពុងដំណើរការ"
            />
        </div>

        <div class="flex flex-wrap gap-3">
            <flux:button
                type="submit"
                variant="primary"
                wire:loading.attr="disabled"
                wire:target="save"
            >
                <span wire:loading.remove wire:target="save">
                    បង្កើតមុខតំណែង
                </span>

                <span wire:loading wire:target="save">
                    កំពុងរក្សាទុក...
                </span>
            </flux:button>

            <flux:button
                type="button"
                variant="ghost"
                :href="route('positions.index')"
                wire:navigate
            >
                បោះបង់
            </flux:button>
        </div>
    </form>
</div>
