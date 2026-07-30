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

    public string $companyCurrency = 'USD';

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

        $this->companyCurrency =
            $company->currency ?: 'USD';

        $this->setDefaultStructure();

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | React when branch changes
    |--------------------------------------------------------------------------
    */

    public function updated(
        string $property,
        mixed $value
    ): void {
        if ($property !== 'branch_id') {
            return;
        }

        $this->department_id = '';

        unset($this->departments);

        $firstDepartment = Department::query()
            ->where(
                'company_id',
                $this->companyId
            )
            ->where(
                'branch_id',
                $value
            )
            ->where(
                'is_active',
                true
            )
            ->orderBy('name')
            ->first();

        if ($firstDepartment) {
            $this->department_id =
                (string) $firstDepartment->id;
        }

        $this->resetValidation([
            'branch_id',
            'department_id',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    protected function rules(): array
    {
        return [
            'branch_id' => [
                'required',
                'integer',

                Rule::exists(
                    'branches',
                    'id'
                )->where(
                    fn ($query) => $query
                        ->where(
                            'company_id',
                            $this->companyId
                        )
                        ->where(
                            'is_active',
                            true
                        )
                ),
            ],

            'department_id' => [
                'required',
                'integer',

                Rule::exists(
                    'departments',
                    'id'
                )->where(
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
                            'is_active',
                            true
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

                Rule::unique(
                    'positions',
                    'code'
                )->where(
                    fn ($query) => $query->where(
                        'company_id',
                        $this->companyId
                    )
                ),
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
                'min:0',
                'max:999999',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'branch_id.required' =>
                'សូមជ្រើសរើសសាខា។',

            'branch_id.exists' =>
                'សាខាដែលបានជ្រើសរើសមិនត្រឹមត្រូវ ឬត្រូវបានបិទ។',

            'department_id.required' =>
                'សូមជ្រើសរើសផ្នែក។',

            'department_id.exists' =>
                'ផ្នែកដែលបានជ្រើសរើសមិនស្ថិតនៅក្នុងសាខានេះទេ។',

            'title.required' =>
                'សូមបញ្ចូលឈ្មោះមុខតំណែង។',

            'title.max' =>
                'ឈ្មោះមុខតំណែងមិនអាចលើសពី ២៥៥ តួអក្សរ។',

            'code.required' =>
                'សូមបញ្ចូលលេខកូដមុខតំណែង។',

            'code.unique' =>
                'លេខកូដមុខតំណែងនេះត្រូវបានប្រើរួចហើយ។',

            'code.max' =>
                'លេខកូដមុខតំណែងមិនអាចលើសពី ៥០ តួអក្សរ។',

            'description.max' =>
                'ការពិពណ៌នាមិនអាចលើសពី ២,០០០ តួអក្សរ។',

            'minimum_salary.numeric' =>
                'ប្រាក់ខែអប្បបរមាត្រូវតែជាលេខ។',

            'minimum_salary.min' =>
                'ប្រាក់ខែអប្បបរមាមិនអាចតិចជាងសូន្យ។',

            'maximum_salary.numeric' =>
                'ប្រាក់ខែអតិបរមាត្រូវតែជាលេខ។',

            'maximum_salary.min' =>
                'ប្រាក់ខែអតិបរមាមិនអាចតិចជាងសូន្យ។',

            'sort_order.required' =>
                'សូមបញ្ចូលលំដាប់បង្ហាញ។',

            'sort_order.integer' =>
                'លំដាប់បង្ហាញត្រូវតែជាចំនួនគត់។',

            'sort_order.min' =>
                'លំដាប់បង្ហាញមិនអាចតិចជាងសូន្យ។',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Form options
    |--------------------------------------------------------------------------
    */

    #[Computed]
    public function branches()
    {
        return Branch::query()
            ->where(
                'company_id',
                $this->companyId
            )
            ->where(
                'is_active',
                true
            )
            ->orderByDesc(
                'is_head_office'
            )
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function departments()
    {
        if (! $this->branch_id) {
            return collect();
        }

        return Department::query()
            ->where(
                'company_id',
                $this->companyId
            )
            ->where(
                'branch_id',
                $this->branch_id
            )
            ->where(
                'is_active',
                true
            )
            ->orderBy('name')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Save position
    |--------------------------------------------------------------------------
    */

    public function save(): mixed
    {
        $this->title =
            trim($this->title);

        $this->code =
            mb_strtoupper(
                trim($this->code)
            );

        $validated = $this->validate();

        $minimumSalary =
            filled(
                $validated['minimum_salary']
            )
                ? (float) $validated['minimum_salary']
                : null;

        $maximumSalary =
            filled(
                $validated['maximum_salary']
            )
                ? (float) $validated['maximum_salary']
                : null;

        if (
            $minimumSalary !== null
            &&
            $maximumSalary !== null
            &&
            $maximumSalary < $minimumSalary
        ) {
            $this->addError(
                'maximum_salary',
                'ប្រាក់ខែអតិបរមាត្រូវតែធំជាង ឬស្មើប្រាក់ខែអប្បបរមា។'
            );

            return null;
        }

        Position::query()->create([
            'company_id' =>
                $this->companyId,

            'branch_id' =>
                (int) $validated['branch_id'],

            'department_id' =>
                (int) $validated['department_id'],

            'title' =>
                trim($validated['title']),

            'code' =>
                mb_strtoupper(
                    trim($validated['code'])
                ),

            'description' =>
                filled(
                    $validated['description']
                )
                    ? trim(
                        $validated['description']
                    )
                    : null,

            'minimum_salary' =>
                $minimumSalary,

            'maximum_salary' =>
                $maximumSalary,

            'is_manager_position' =>
                (bool) $validated[
                    'is_manager_position'
                ],

            'is_active' =>
                (bool) $validated[
                    'is_active'
                ],

            'sort_order' =>
                (int) $validated[
                    'sort_order'
                ],
        ]);

        Flux::toast(
            variant: 'success',
            text: 'បានបង្កើតមុខតំណែងថ្មីដោយជោគជ័យ។'
        );

        session()->flash(
            'status',
            'បានបង្កើតមុខតំណែងថ្មីដោយជោគជ័យ។'
        );

        return $this->redirectRoute(
            'positions.index',
            navigate: true
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Reset form
    |--------------------------------------------------------------------------
    */

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

        $this->is_manager_position = false;

        $this->is_active = true;

        $this->sort_order = '0';

        $this->setDefaultStructure();

        unset($this->departments);

        $this->resetValidation();
    }

    /*
    |--------------------------------------------------------------------------
    | Default branch and department
    |--------------------------------------------------------------------------
    */

    private function setDefaultStructure(): void
    {
        $firstBranch = Branch::query()
            ->where(
                'company_id',
                $this->companyId
            )
            ->where(
                'is_active',
                true
            )
            ->orderByDesc(
                'is_head_office'
            )
            ->orderBy('name')
            ->first();

        if (! $firstBranch) {
            return;
        }

        $this->branch_id =
            (string) $firstBranch->id;

        $firstDepartment =
            Department::query()
                ->where(
                    'company_id',
                    $this->companyId
                )
                ->where(
                    'branch_id',
                    $firstBranch->id
                )
                ->where(
                    'is_active',
                    true
                )
                ->orderBy('name')
                ->first();

        if ($firstDepartment) {
            $this->department_id =
                (string) $firstDepartment->id;
        }
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
                បង្កើតមុខតំណែងថ្មី
            </h1>

            <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                បញ្ចូលព័ត៌មានមុខតំណែង ផ្នែក ប្រាក់ខែ
                និងស្ថានភាពប្រើប្រាស់។
            </p>
        </div>

        <flux:button
            type="button"
            variant="ghost"
            icon="arrow-left"
            :href="route('positions.index')"
            wire:navigate
        >
            ត្រឡប់ទៅបញ្ជី
        </flux:button>
    </div>

    {{-- No active branches --}}
    @if ($this->branches->isEmpty())
        <div
            class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-amber-800 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200"
        >
            <h2 class="font-semibold">
                មិនទាន់មានសាខាសកម្ម
            </h2>

            <p class="mt-1 text-sm">
                សូមបង្កើត ឬបើកដំណើរការសាខាជាមុនសិន។
            </p>
        </div>
    @else
        {{-- Create form --}}
        <form
            wire:submit="save"
            class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
        >
            <div
                class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-700"
            >
                <h2
                    class="text-lg font-semibold text-zinc-900 dark:text-white"
                >
                    ព័ត៌មានមុខតំណែង
                </h2>

                <p
                    class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
                >
                    វាលដែលមានសញ្ញា * ត្រូវតែបំពេញ។
                </p>
            </div>

            <div class="space-y-5 p-5">
                <div
                    class="grid grid-cols-1 gap-x-4 gap-y-4 md:grid-cols-2 xl:grid-cols-4"
                >
                    {{-- Branch --}}
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

                                @if ($branch->is_head_office)
                                    — ការិយាល័យកណ្ដាល
                                @endif
                            </flux:select.option>
                        @endforeach
                    </flux:select>

                    {{-- Department --}}
                    <flux:select
                        wire:model="department_id"
                        label="ផ្នែក"
                        required
                        :disabled="$this->departments->isEmpty()"
                    >
                        <flux:select.option value="">
                            {{
                                $this->departments->isEmpty()
                                    ? 'មិនមានផ្នែកក្នុងសាខានេះ'
                                    : 'ជ្រើសរើសផ្នែក'
                            }}
                        </flux:select.option>

                        @foreach (
                            $this->departments
                            as $department
                        )
                            <flux:select.option
                                value="{{ $department->id }}"
                            >
                                {{ $department->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>

                    {{-- Position title --}}
                    <flux:input
                        wire:model.blur="title"
                        label="ឈ្មោះមុខតំណែង"
                        placeholder="ឧទាហរណ៍៖ អ្នកគ្រប់គ្រងផ្នែកលក់"
                        required
                    />

                    {{-- Position code --}}
                    <flux:input
                        wire:model.blur="code"
                        label="លេខកូដមុខតំណែង"
                        placeholder="ឧទាហរណ៍៖ SALES-MGR"
                        required
                    />

                    {{-- Minimum salary --}}
                    <flux:input
                        wire:model.blur="minimum_salary"
                        type="number"
                        min="0"
                        step="0.01"
                        label="ប្រាក់ខែអប្បបរមា"
                        placeholder="0.00"
                    />

                    {{-- Maximum salary --}}
                    <flux:input
                        wire:model.blur="maximum_salary"
                        type="number"
                        min="0"
                        step="0.01"
                        label="ប្រាក់ខែអតិបរមា"
                        placeholder="0.00"
                    />

                    {{-- Sort order --}}
                    <flux:input
                        wire:model.blur="sort_order"
                        type="number"
                        min="0"
                        step="1"
                        label="លំដាប់បង្ហាញ"
                        placeholder="0"
                        required
                    />

                    {{-- Currency --}}
                    <div>
                        <label
                            class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                        >
                            រូបិយប័ណ្ណ
                        </label>

                        <div
                            class="flex min-h-10 items-center rounded-lg border border-zinc-300 bg-zinc-50 px-3 text-sm font-medium text-zinc-700 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200"
                        >
                            {{ $companyCurrency }}
                        </div>
                    </div>

                    {{-- Description --}}
                    <div
                        class="md:col-span-2 xl:col-span-4"
                    >
                        <flux:textarea
                            wire:model.blur="description"
                            label="ការពិពណ៌នា"
                            rows="3"
                            placeholder="ពិពណ៌នាអំពីភារកិច្ច ឬតម្រូវការសំខាន់ៗរបស់មុខតំណែង..."
                        />
                    </div>
                </div>

                {{-- Position settings --}}
                <div
                    class="grid gap-3 rounded-xl border border-zinc-200 bg-zinc-50 p-4 md:grid-cols-2 dark:border-zinc-700 dark:bg-zinc-800/50"
                >
                    <flux:checkbox
                        wire:model="is_manager_position"
                        label="នេះជាមុខតំណែងអ្នកគ្រប់គ្រង"
                        description="ប្រើសម្រាប់កំណត់មុខតំណែងដែលមានសិទ្ធិគ្រប់គ្រងក្រុម។"
                    />

                    <flux:checkbox
                        wire:model="is_active"
                        label="បើកដំណើរការមុខតំណែង"
                        description="មុខតំណែងសកម្មអាចជ្រើសរើសនៅពេលបង្កើតបុគ្គលិក។"
                    />
                </div>

                {{-- Department warning --}}
                @if ($this->departments->isEmpty())
                    <div
                        class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200"
                    >
                        សាខាដែលបានជ្រើសរើសមិនទាន់មានផ្នែកសកម្មទេ។
                        សូមបង្កើតផ្នែកជាមុនសិន។
                    </div>
                @endif
            </div>

            {{-- Form actions --}}
            <div
                class="flex flex-col-reverse gap-3 border-t border-zinc-200 bg-zinc-50 px-5 py-4 dark:border-zinc-700 dark:bg-zinc-800/40 sm:flex-row sm:justify-end"
            >
                <flux:button
                    type="button"
                    variant="ghost"
                    icon="arrow-path"
                    wire:click="resetForm"
                    class="w-full sm:w-auto"
                >
                    សម្អាត
                </flux:button>

                <flux:button
                    type="button"
                    variant="ghost"
                    :href="route('positions.index')"
                    wire:navigate
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
                    :disabled="$this->departments->isEmpty()"
                    class="w-full sm:w-auto"
                >
                    <span
                        wire:loading.remove
                        wire:target="save"
                    >
                        បង្កើតមុខតំណែង
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
    @endif
</div>