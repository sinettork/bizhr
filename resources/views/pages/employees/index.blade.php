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
use Livewire\WithPagination;

new #[Title('បញ្ជីបុគ្គលិក')] class extends Component
{
    use WithFileUploads;
    use WithPagination;

    public int $companyId = 0;

    public string $companyCurrency = 'USD';

    public ?int $employeeId = null;

    public bool $showForm = false;
    public bool $showImport = false;
    public $importFile = null;
    public array $importResults = [];
    public bool $isImportValid = false;

    /*
    |--------------------------------------------------------------------------
    | Search and filters
    |--------------------------------------------------------------------------
    */

    public string $search = '';
    public string $filterBranch = '';
    public string $filterDepartment = '';
    public string $filterStatus = '';
    public string $filterActive = '';

    /*
    |--------------------------------------------------------------------------
    | Employee form
    |--------------------------------------------------------------------------
    */

    public string $branch_id = '';
    public string $department_id = '';
    public string $position_id = '';
    public string $employment_type_id = '';

    public string $employee_code = '';
    public string $first_name = '';
    public string $last_name = '';
    public string $full_name_km = '';
    public string $full_name_en = '';

    /*
     * Database accepts only:
     * M, F, Other
     */
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

    public ?string $currentProfilePhoto = null;

    public function processImport(\App\Services\EmployeeImportService $service): void
    {
        $this->validate([
            'importFile' => 'required|mimes:csv,txt|max:1024',
        ]);

        $path = $this->importFile->getRealPath();
        $file = fopen($path, 'r');
        $header = fgetcsv($file);
        
        // Basic mapping (assuming fixed order for simplicity)
        // employee_code, first_name, last_name, full_name_km, branch_id, department_id, hire_date, status, salary, currency
        $rows = [];
        while (($data = fgetcsv($file)) !== false) {
            if (count($data) < 6) continue;
            $rows[] = [
                'employee_code' => $data[0],
                'first_name' => $data[1],
                'last_name' => $data[2],
                'full_name_km' => $data[3],
                'branch_id' => (int) $data[4],
                'department_id' => (int) $data[5],
                'hire_date' => $data[6],
                'employment_status' => $data[7] ?: 'Active',
                'base_salary' => $data[8] ?: 0,
                'salary_currency' => $data[9] ?: 'USD',
            ];
        }
        fclose($file);

        $this->importResults = $service->validate($rows, $this->companyId);
        $this->isImportValid = collect($this->importResults)->every(fn($r) => $r['is_valid']);
    }

    public function confirmImport(\App\Services\EmployeeImportService $service): void
    {
        if (!$this->isImportValid) return;

        $validRows = collect($this->importResults)->map(fn($r) => $r['data'])->toArray();
        $count = $service->commit($validRows, $this->companyId);

        $this->reset(['showImport', 'importFile', 'importResults', 'isImportValid']);
        session()->flash('success', "បាននាំចូលបុគ្គលិកចំនួន {$count} នាក់ដោយជោគជ័យ។");
        unset($this->employees);
    }

    public function downloadTemplate()
    {
        return response()->streamDownload(function () {
            $output = fopen('php://output', 'w');
            fputcsv($output, [
                'employee_code', 'first_name', 'last_name', 'full_name_km', 
                'branch_id', 'department_id', 'hire_date', 'status', 'salary', 'currency'
            ]);
            fputcsv($output, [
                'EMP001', 'Sok', 'Dara', 'សុខ ដារ៉ា', '1', '1', '2024-01-01', 'Active', '500', 'USD'
            ]);
            fclose($output);
        }, 'employee_import_template.csv');
    }

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

        $this->companyCurrency =
            $company->currency ?: 'USD';

        $this->salary_currency =
            $this->companyCurrency;

        $this->hire_date = now()->format('Y-m-d');

        $this->setDefaultStructure();
    }

    /*
    |--------------------------------------------------------------------------
    | React to form and filter changes
    |--------------------------------------------------------------------------
    */

    public function updated(
        string $property,
        mixed $value
    ): void {
        if ($property === 'branch_id') {
            $this->department_id = '';
            $this->position_id = '';

            $firstDepartment = Department::query()
                ->where('company_id', $this->companyId)
                ->where('branch_id', $value)
                ->where('is_active', true)
                ->orderBy('name')
                ->first();

            if ($firstDepartment) {
                $this->department_id =
                    (string) $firstDepartment->id;
            }

            unset($this->availableDepartments);
            unset($this->availablePositions);

            $this->resetValidation([
                'department_id',
                'position_id',
            ]);
        }

        if ($property === 'department_id') {
            $this->position_id = '';

            unset($this->availablePositions);

            $this->resetValidation('position_id');
        }

        if ($property === 'filterBranch') {
            $this->filterDepartment = '';

            unset($this->filterDepartments);
        }

        if (
            in_array(
                $property,
                [
                    'search',
                    'filterBranch',
                    'filterDepartment',
                    'filterStatus',
                    'filterActive',
                ],
                true
            )
        ) {
            $this->resetPage();

            unset($this->employees);
        }
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

                Rule::exists('branches', 'id')
                    ->where(
                        fn ($query) => $query->where(
                            'company_id',
                            $this->companyId
                        )
                    ),
            ],

            'department_id' => [
                'required',
                'integer',

                Rule::exists('departments', 'id')
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

            'position_id' => [
                'nullable',

                Rule::exists('positions', 'id')
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
                            ->where(
                                'department_id',
                                $this->department_id
                            )
                    ),
            ],

            'employment_type_id' => [
                'nullable',

                Rule::exists('employment_types', 'id')
                    ->where(
                        fn ($query) => $query->where(
                            'company_id',
                            $this->companyId
                        )
                    ),
            ],

            'employee_code' => [
                'required',
                'string',
                'max:50',

                Rule::unique(
                    'employees',
                    'employee_code'
                )->ignore($this->employeeId),
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

            /*
             * Must exactly match SQLite CHECK constraint:
             * gender IN ('M', 'F', 'Other')
             */
            'gender' => [
                'nullable',
                Rule::in([
                    'M',
                    'F',
                    'Other',
                ]),
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
                Rule::in([
                    'USD',
                    'KHR',
                ]),
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

    protected function messages(): array
    {
        return [
            'branch_id.required' =>
                'សូមជ្រើសរើសសាខា។',

            'branch_id.exists' =>
                'សាខាដែលបានជ្រើសរើសមិនត្រឹមត្រូវ។',

            'department_id.required' =>
                'សូមជ្រើសរើសផ្នែក។',

            'department_id.exists' =>
                'ផ្នែកដែលបានជ្រើសរើសមិនត្រឹមត្រូវ។',

            'position_id.exists' =>
                'មុខតំណែងដែលបានជ្រើសរើសមិនត្រឹមត្រូវ។',

            'employment_type_id.exists' =>
                'ប្រភេទការងារដែលបានជ្រើសរើសមិនត្រឹមត្រូវ។',

            'employee_code.required' =>
                'សូមបញ្ចូលលេខកូដបុគ្គលិក។',

            'employee_code.unique' =>
                'លេខកូដបុគ្គលិកនេះត្រូវបានប្រើរួចហើយ។',

            'employee_code.max' =>
                'លេខកូដបុគ្គលិកមិនអាចលើសពី ៥០ តួអក្សរ។',

            'first_name.required' =>
                'សូមបញ្ចូលនាមខ្លួន។',

            'last_name.required' =>
                'សូមបញ្ចូលនាមត្រកូល។',

            'gender.in' =>
                'ភេទដែលបានជ្រើសរើសមិនត្រឹមត្រូវ។',

            'date_of_birth.before' =>
                'ថ្ងៃខែឆ្នាំកំណើតត្រូវតែមុនថ្ងៃនេះ។',

            'email.email' =>
                'អាសយដ្ឋានអ៊ីមែលមិនត្រឹមត្រូវ។',

            'hire_date.required' =>
                'សូមបញ្ចូលថ្ងៃចូលធ្វើការ។',

            'hire_date.date' =>
                'ថ្ងៃចូលធ្វើការមិនត្រឹមត្រូវ។',

            'probation_end_date.after_or_equal' =>
                'ថ្ងៃបញ្ចប់សាកល្បងត្រូវតែក្រោយ ឬស្មើថ្ងៃចូលធ្វើការ។',

            'contract_end_date.after_or_equal' =>
                'ថ្ងៃបញ្ចប់កិច្ចសន្យាត្រូវតែក្រោយ ឬស្មើថ្ងៃចាប់ផ្ដើមកិច្ចសន្យា។',

            'base_salary.numeric' =>
                'ប្រាក់ខែគោលត្រូវតែជាលេខ។',

            'base_salary.min' =>
                'ប្រាក់ខែគោលមិនអាចតិចជាងសូន្យ។',

            'profile_photo_upload.image' =>
                'ឯកសារត្រូវតែជារូបភាព។',

            'profile_photo_upload.max' =>
                'រូបភាពមិនអាចធំជាង ២ MB។',
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
        if (
            ! $this->branch_id
            || ! $this->department_id
        ) {
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

    #[Computed]
    public function filterDepartments()
    {
        return Department::query()
            ->where('company_id', $this->companyId)
            ->when(
                filled($this->filterBranch),
                fn ($query) => $query->where(
                    'branch_id',
                    $this->filterBranch
                )
            )
            ->orderBy('name')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

    #[Computed]
    public function employeeStats(): array
    {
        $query = Employee::query()
            ->where('company_id', $this->companyId);

        return [
            'total' => (clone $query)->count(),

            'active' => (clone $query)
                ->where('is_active', true)
                ->count(),

            'probation' => (clone $query)
                ->where(
                    'employment_status',
                    'On probation'
                )
                ->count(),

            'inactive' => (clone $query)
                ->where('is_active', false)
                ->count(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Employee list
    |--------------------------------------------------------------------------
    */

    #[Computed]
    public function employees()
    {
        return Employee::query()
            ->with([
                'branch',
                'department',
                'position',
                'employmentType',
            ])
            ->where('company_id', $this->companyId)

            ->when(
                filled($this->filterBranch),
                fn ($query) => $query->where(
                    'branch_id',
                    $this->filterBranch
                )
            )

            ->when(
                filled($this->filterDepartment),
                fn ($query) => $query->where(
                    'department_id',
                    $this->filterDepartment
                )
            )

            ->when(
                filled($this->filterStatus),
                fn ($query) => $query->where(
                    'employment_status',
                    $this->filterStatus
                )
            )

            ->when(
                $this->filterActive !== '',
                fn ($query) => $query->where(
                    'is_active',
                    $this->filterActive === '1'
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
                                    'employee_code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'first_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'last_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'full_name_km',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'full_name_en',
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
                                )
                                ->orWhereHas(
                                    'department',
                                    fn ($departmentQuery) =>
                                        $departmentQuery->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )
                                )
                                ->orWhereHas(
                                    'position',
                                    fn ($positionQuery) =>
                                        $positionQuery->where(
                                            'title',
                                            'like',
                                            "%{$search}%"
                                        )
                                );
                        }
                    );
                }
            )

            ->orderByDesc('is_active')
            ->orderBy('employee_code')
            ->paginate(10);
    }

    /*
    |--------------------------------------------------------------------------
    | Form actions
    |--------------------------------------------------------------------------
    */

    public function openCreateForm(): void
    {
        $this->resetForm();

        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->resetForm();

        $this->showForm = false;
    }

    public function save(): void
    {
        /*
         * Do not convert gender to lowercase.
         * Database accepts M, F and Other exactly.
         */
        $validated = $this->validate();

        $validated['branch_id'] =
            (int) $validated['branch_id'];

        $validated['department_id'] =
            (int) $validated['department_id'];

        $validated['position_id'] =
            filled($validated['position_id'])
                ? (int) $validated['position_id']
                : null;

        $validated['employment_type_id'] =
            filled($validated['employment_type_id'])
                ? (int) $validated['employment_type_id']
                : null;

        $validated['employee_code'] =
            strtoupper(
                trim($validated['employee_code'])
            );

        $validated['first_name'] =
            trim($validated['first_name']);

        $validated['last_name'] =
            trim($validated['last_name']);

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
            $validated[$field] =
                filled($validated[$field])
                    ? trim((string) $validated[$field])
                    : null;
        }

        /*
         * Gender must remain M, F or Other.
         */
        $validated['gender'] =
            filled($validated['gender'])
                ? trim($validated['gender'])
                : null;

        $validated['base_salary'] =
            filled($validated['base_salary'])
                ? $validated['base_salary']
                : null;

        $photoPath = $this->currentProfilePhoto;

        if ($this->profile_photo_upload) {
            if (
                $this->employeeId
                && $this->currentProfilePhoto
            ) {
                Storage::disk('public')->delete(
                    $this->currentProfilePhoto
                );
            }

            $photoPath =
                $this->profile_photo_upload->store(
                    'employees/profile-photos',
                    'public'
                );
        }

        unset($validated['profile_photo_upload']);

        $validated['profile_photo'] = $photoPath;

        if ($this->employeeId !== null) {
            $employee = $this->findEmployee(
                $this->employeeId
            );

            $employee->update($validated);

            Flux::toast(
                variant: 'success',
                text: 'បានកែប្រែព័ត៌មានបុគ្គលិកដោយជោគជ័យ។'
            );
        } else {
            Employee::query()->create([
                ...$validated,
                'company_id' => $this->companyId,
            ]);

            Flux::toast(
                variant: 'success',
                text: 'បានបង្កើតបុគ្គលិកថ្មីដោយជោគជ័យ។'
            );
        }

        unset($this->employees);
        unset($this->employeeStats);

        $this->resetPage();
        $this->resetForm();

        $this->showForm = false;
    }

    public function edit(int $employeeId): void
    {
        $employee = $this->findEmployee($employeeId);

        $this->employeeId = $employee->id;

        $this->branch_id =
            (string) $employee->branch_id;

        $this->department_id =
            (string) $employee->department_id;

        $this->position_id =
            $employee->position_id
                ? (string) $employee->position_id
                : '';

        $this->employment_type_id =
            $employee->employment_type_id
                ? (string) $employee->employment_type_id
                : '';

        $this->employee_code =
            $employee->employee_code;

        $this->first_name =
            $employee->first_name;

        $this->last_name =
            $employee->last_name;

        $this->full_name_km =
            (string) $employee->full_name_km;

        $this->full_name_en =
            (string) $employee->full_name_en;

        /*
         * Keep exact database value:
         * M, F or Other
         */
        $this->gender =
            (string) ($employee->gender ?? '');

        $this->date_of_birth =
            $employee->date_of_birth
                ? $employee->date_of_birth->format('Y-m-d')
                : '';

        $this->phone =
            (string) $employee->phone;

        $this->email =
            (string) $employee->email;

        $this->address =
            (string) $employee->address;

        $this->city =
            (string) $employee->city;

        $this->hire_date =
            $employee->hire_date
                ? $employee->hire_date->format('Y-m-d')
                : '';

        $this->probation_end_date =
            $employee->probation_end_date
                ? $employee->probation_end_date->format('Y-m-d')
                : '';

        $this->contract_start_date =
            $employee->contract_start_date
                ? $employee->contract_start_date->format('Y-m-d')
                : '';

        $this->contract_end_date =
            $employee->contract_end_date
                ? $employee->contract_end_date->format('Y-m-d')
                : '';

        $this->base_salary =
            $employee->base_salary !== null
                ? (string) $employee->base_salary
                : '';

        $this->salary_currency =
            $employee->salary_currency ?: 'USD';

        $this->employment_status =
            $employee->employment_status;

        $this->is_active =
            $employee->is_active;

        $this->currentProfilePhoto =
            $employee->profile_photo;

        $this->profile_photo_upload = null;

        unset($this->availableDepartments);
        unset($this->availablePositions);

        $this->resetValidation();

        $this->showForm = true;
    }

    public function toggleStatus(int $employeeId): void
    {
        $employee = $this->findEmployee($employeeId);

        $newStatus = ! $employee->is_active;

        $employee->update([
            'is_active' => $newStatus,
        ]);

        unset($this->employees);
        unset($this->employeeStats);

        Flux::toast(
            variant: 'success',
            text: $newStatus
                ? 'បានបើកដំណើរការបុគ្គលិក។'
                : 'បានបិទដំណើរការបុគ្គលិក។'
        );
    }

    public function delete(int $employeeId): void
    {
        $employee = $this->findEmployee($employeeId);

        $employee->delete();

        if ($this->employeeId === $employeeId) {
            $this->resetForm();

            $this->showForm = false;
        }

        unset($this->employees);
        unset($this->employeeStats);

        $this->resetPage();

        Flux::toast(
            variant: 'success',
            text: 'បានលុបបុគ្គលិកទៅក្នុងធុងសំរាម។'
        );
    }

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'filterBranch',
            'filterDepartment',
            'filterStatus',
            'filterActive',
        ]);

        unset($this->employees);
        unset($this->filterDepartments);

        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset([
            'employeeId',
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
            'employment_status',
            'is_active',
            'profile_photo_upload',
            'currentProfilePhoto',
        ]);

        $this->salary_currency =
            $this->companyCurrency;

        $this->employment_status = 'Draft';
        $this->is_active = true;
        $this->hire_date = now()->format('Y-m-d');

        $this->setDefaultStructure();

        unset($this->availableDepartments);
        unset($this->availablePositions);

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

        $this->branch_id =
            (string) $firstBranch->id;

        $firstDepartment = Department::query()
            ->where('company_id', $this->companyId)
            ->where('branch_id', $firstBranch->id)
            ->where('is_active', true)
            ->orderBy('name')
            ->first();

        if ($firstDepartment) {
            $this->department_id =
                (string) $firstDepartment->id;
        }
    }

    private function findEmployee(
        int $employeeId
    ): Employee {
        return Employee::query()
            ->where('company_id', $this->companyId)
            ->findOrFail($employeeId);
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
                បញ្ជីបុគ្គលិក
            </h1>

            <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                គ្រប់គ្រងព័ត៌មានបុគ្គលិក សាខា ផ្នែក និងស្ថានភាពការងារ។
            </p>
        </div>

        <div class="flex gap-2">
            <flux:button
                type="button"
                variant="ghost"
                icon="arrow-up-tray"
                wire:click="$set('showImport', true)"
            >
                នាំចូល (CSV)
            </flux:button>

            <flux:button
                type="button"
                variant="primary"
                icon="plus"
                :href="route('employees.create')"
                wire:navigate
            >
                បន្ថែមបុគ្គលិក
            </flux:button>
        </div>
    </div>

    {{-- Import form --}}
    @if ($showImport)
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6">
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">នាំចូលបុគ្គលិកពី CSV</h2>
                    <p class="mt-1 text-sm text-zinc-500">ទាញយកគំរូ CSV រួចបំពេញព័ត៌មានបុគ្គលិកដើម្បីនាំចូលក្នុងប្រព័ន្ធ។</p>
                </div>
                <flux:button variant="ghost" wire:click="$set('showImport', false)">បិទ</flux:button>
            </div>

            <div class="space-y-4">
                <div class="flex items-center gap-4">
                    <flux:button variant="ghost" icon="arrow-down-tray" wire:click="downloadTemplate">ទាញយកគំរូ CSV</flux:button>
                    <div class="flex-1">
                        <flux:input type="file" wire:model="importFile" accept=".csv" />
                    </div>
                    <flux:button variant="primary" wire:click="processImport" wire:loading.attr="disabled">ត្រួតពិនិត្យឯកសារ</flux:button>
                </div>

                @if ($importResults)
                    <div class="mt-6 overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                            <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                                <tr class="text-left text-xs text-zinc-500">
                                    <th class="px-4 py-2">ជួរដេក</th>
                                    <th class="px-4 py-2">លេខកូដ</th>
                                    <th class="px-4 py-2">ឈ្មោះ</th>
                                    <th class="px-4 py-2">ស្ថានភាព</th>
                                    <th class="px-4 py-2">បញ្ហា</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                                @foreach ($importResults as $result)
                                    <tr class="text-sm">
                                        <td class="px-4 py-2">{{ $result['row'] }}</td>
                                        <td class="px-4 py-2">{{ $result['data']['employee_code'] }}</td>
                                        <td class="px-4 py-2">{{ $result['data']['first_name'] }} {{ $result['data']['last_name'] }}</td>
                                        <td class="px-4 py-2">
                                            @if ($result['is_valid'])
                                                <flux:badge variant="success">ត្រឹមត្រូវ</flux:badge>
                                            @else
                                                <flux:badge variant="danger">មិនត្រឹមត្រូវ</flux:badge>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 text-xs text-red-600">
                                            {{ implode(', ', $result['errors']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <flux:button variant="primary" :disabled="!$isImportValid" wire:click="confirmImport">បញ្ជាក់ការនាំចូល</flux:button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- Statistics --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500">
                បុគ្គលិកសរុប
            </p>

            <p
                class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white"
            >
                {{ number_format($this->employeeStats['total']) }}
            </p>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500">
                កំពុងបម្រើការងារ
            </p>

            <p
                class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white"
            >
                {{ number_format($this->employeeStats['active']) }}
            </p>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500">
                កំពុងសាកល្បងការងារ
            </p>

            <p
                class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white"
            >
                {{ number_format($this->employeeStats['probation']) }}
            </p>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500">
                បានបិទដំណើរការ
            </p>

            <p
                class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white"
            >
                {{ number_format($this->employeeStats['inactive']) }}
            </p>
        </div>
    </div>

    {{-- Create/Edit form --}}
    @if ($showForm)
        <form
            wire:submit="save"
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6"
        >
            <div
                class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2
                        class="text-lg font-semibold text-zinc-900 dark:text-white"
                    >
                        {{ $employeeId
                            ? 'កែប្រែព័ត៌មានបុគ្គលិក'
                            : 'បន្ថែមបុគ្គលិកថ្មី' }}
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500">
                        បំពេញព័ត៌មានសំខាន់ៗរបស់បុគ្គលិក។
                    </p>
                </div>

                <flux:button
                    type="button"
                    variant="ghost"
                    wire:click="cancelForm"
                >
                    បិទទម្រង់
                </flux:button>
            </div>

            <div class="grid gap-6 xl:grid-cols-[180px_1fr]">
                {{-- Photo --}}
                <div>
                    <div
                        class="flex aspect-square items-center justify-center overflow-hidden rounded-xl border border-dashed border-zinc-300 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800"
                    >
                        @if ($profile_photo_upload)
                            <img
                                src="{{ $profile_photo_upload->temporaryUrl() }}"
                                alt="រូបភាពបុគ្គលិក"
                                class="h-full w-full object-cover"
                            >
                        @elseif ($currentProfilePhoto)
                            <img
                                src="{{ asset(
                                    'storage/'
                                    . ltrim(
                                        $currentProfilePhoto,
                                        '/'
                                    )
                                ) }}"
                                alt="រូបភាពបុគ្គលិក"
                                class="h-full w-full object-cover"
                            >
                        @else
                            <div class="text-center text-zinc-400">
                                <div class="text-4xl">
                                    👤
                                </div>

                                <p class="mt-2 text-sm">
                                    មិនមានរូបភាព
                                </p>
                            </div>
                        @endif
                    </div>

                    <label
                        for="profile_photo_upload"
                        class="mt-4 block text-sm font-medium text-zinc-700 dark:text-zinc-200"
                    >
                        រូបថតបុគ្គលិក
                    </label>

                    <input
                        id="profile_photo_upload"
                        type="file"
                        accept="image/*"
                        wire:model="profile_photo_upload"
                        class="mt-2 block w-full text-sm text-zinc-600 file:mr-3 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-3 file:py-2 file:text-sm file:font-medium dark:text-zinc-300 dark:file:bg-zinc-800"
                    >

                    <p
                        wire:loading
                        wire:target="profile_photo_upload"
                        class="mt-2 text-sm text-zinc-500"
                    >
                        កំពុងផ្ទុករូបភាព...
                    </p>

                    @error('profile_photo_upload')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Form fields --}}
                <div class="space-y-7">
                    <section>
                        <h3
                            class="mb-4 font-medium text-zinc-900 dark:text-white"
                        >
                            ព័ត៌មានមូលដ្ឋាន
                        </h3>

                        <div
                            class="grid gap-4 md:grid-cols-2 xl:grid-cols-3"
                        >
                            <flux:input
                                wire:model="employee_code"
                                label="លេខកូដបុគ្គលិក"
                                placeholder="EMP-0001"
                                required
                            />

                            <flux:input
                                wire:model="first_name"
                                label="នាមខ្លួន"
                                required
                            />

                            <flux:input
                                wire:model="last_name"
                                label="នាមត្រកូល"
                                required
                            />

                            <flux:input
                                wire:model="full_name_km"
                                label="ឈ្មោះពេញជាភាសាខ្មែរ"
                            />

                            <flux:input
                                wire:model="full_name_en"
                                label="ឈ្មោះពេញជាភាសាអង់គ្លេស"
                            />

                            {{-- Exact values: M, F, Other --}}
                            <flux:select
                                wire:model="gender"
                                label="ភេទ"
                            >
                                <flux:select.option value="">
                                    មិនបានកំណត់
                                </flux:select.option>

                                <flux:select.option value="M">
                                    ប្រុស
                                </flux:select.option>

                                <flux:select.option value="F">
                                    ស្រី
                                </flux:select.option>

                                <flux:select.option value="Other">
                                    ផ្សេងៗ
                                </flux:select.option>
                            </flux:select>

                            <flux:input
                                wire:model="date_of_birth"
                                type="date"
                                label="ថ្ងៃខែឆ្នាំកំណើត"
                            />

                            <flux:input
                                wire:model="phone"
                                label="លេខទូរស័ព្ទ"
                            />

                            <flux:input
                                wire:model="email"
                                type="email"
                                label="អ៊ីមែល"
                            />

                            <flux:input
                                wire:model="city"
                                label="រាជធានី/ខេត្ត"
                            />

                            <div class="md:col-span-2">
                                <flux:textarea
                                    wire:model="address"
                                    label="អាសយដ្ឋាន"
                                    rows="2"
                                />
                            </div>
                        </div>
                    </section>

                    <section>
                        <h3
                            class="mb-4 font-medium text-zinc-900 dark:text-white"
                        >
                            រចនាសម្ព័ន្ធការងារ
                        </h3>

                        <div
                            class="grid gap-4 md:grid-cols-2 xl:grid-cols-4"
                        >
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
                                    </flux:select.option>
                                @endforeach
                            </flux:select>

                            <flux:select
                                wire:model.live="department_id"
                                label="ផ្នែក"
                                required
                            >
                                <flux:select.option value="">
                                    ជ្រើសរើសផ្នែក
                                </flux:select.option>

                                @foreach (
                                    $this->availableDepartments
                                    as $department
                                )
                                    <flux:select.option
                                        value="{{ $department->id }}"
                                    >
                                        {{ $department->name }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>

                            <flux:select
                                wire:model="position_id"
                                label="មុខតំណែង"
                            >
                                <flux:select.option value="">
                                    មិនទាន់កំណត់
                                </flux:select.option>

                                @foreach (
                                    $this->availablePositions
                                    as $position
                                )
                                    <flux:select.option
                                        value="{{ $position->id }}"
                                    >
                                        {{ $position->title }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>

                            <flux:select
                                wire:model="employment_type_id"
                                label="ប្រភេទការងារ"
                            >
                                <flux:select.option value="">
                                    មិនទាន់កំណត់
                                </flux:select.option>

                                @foreach (
                                    $this->employmentTypes
                                    as $employmentType
                                )
                                    <flux:select.option
                                        value="{{ $employmentType->id }}"
                                    >
                                        {{ $employmentType->name }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>
                    </section>

                    <section>
                        <h3
                            class="mb-4 font-medium text-zinc-900 dark:text-white"
                        >
                            កាលបរិច្ឆេទ និងប្រាក់ខែ
                        </h3>

                        <div
                            class="grid gap-4 md:grid-cols-2 xl:grid-cols-4"
                        >
                            <flux:input
                                wire:model="hire_date"
                                type="date"
                                label="ថ្ងៃចូលធ្វើការ"
                                required
                            />

                            <flux:input
                                wire:model="probation_end_date"
                                type="date"
                                label="ថ្ងៃបញ្ចប់សាកល្បង"
                            />

                            <flux:input
                                wire:model="contract_start_date"
                                type="date"
                                label="ថ្ងៃចាប់ផ្ដើមកិច្ចសន្យា"
                            />

                            <flux:input
                                wire:model="contract_end_date"
                                type="date"
                                label="ថ្ងៃបញ្ចប់កិច្ចសន្យា"
                            />

                            <flux:input
                                wire:model="base_salary"
                                type="number"
                                min="0"
                                step="0.01"
                                label="ប្រាក់ខែគោល"
                            />

                            <flux:select
                                wire:model="salary_currency"
                                label="រូបិយប័ណ្ណ"
                            >
                                <flux:select.option value="USD">
                                    USD
                                </flux:select.option>

                                <flux:select.option value="KHR">
                                    KHR
                                </flux:select.option>
                            </flux:select>

                            <flux:select
                                wire:model="employment_status"
                                label="ស្ថានភាពការងារ"
                                required
                            >
                                <flux:select.option value="Draft">
                                    ព្រាង
                                </flux:select.option>

                                <flux:select.option value="Active">
                                    កំពុងបម្រើការងារ
                                </flux:select.option>

                                <flux:select.option value="On probation">
                                    កំពុងសាកល្បង
                                </flux:select.option>

                                <flux:select.option value="On leave">
                                    កំពុងឈប់សម្រាក
                                </flux:select.option>

                                <flux:select.option value="Suspended">
                                    ផ្អាកការងារ
                                </flux:select.option>

                                <flux:select.option value="Resigned">
                                    លាឈប់
                                </flux:select.option>

                                <flux:select.option value="Terminated">
                                    បញ្ចប់កិច្ចសន្យា
                                </flux:select.option>

                                <flux:select.option value="Retired">
                                    ចូលនិវត្តន៍
                                </flux:select.option>
                            </flux:select>

                            <div class="flex items-end pb-2">
                                <flux:checkbox
                                    wire:model="is_active"
                                    label="បុគ្គលិកកំពុងដំណើរការ"
                                />
                            </div>
                        </div>
                    </section>
                </div>
            </div>

            <div
                class="mt-7 flex flex-wrap justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700"
            >
                <flux:button
                    type="button"
                    variant="ghost"
                    wire:click="cancelForm"
                >
                    បោះបង់
                </flux:button>

                <flux:button
                    type="submit"
                    variant="primary"
                    wire:loading.attr="disabled"
                    wire:target="save"
                >
                    <span wire:loading.remove wire:target="save">
                        {{ $employeeId
                            ? 'រក្សាទុកការកែប្រែ'
                            : 'បង្កើតបុគ្គលិក' }}
                    </span>

                    <span wire:loading wire:target="save">
                        កំពុងរក្សាទុក...
                    </span>
                </flux:button>
            </div>
        </form>
    @endif

    {{-- Employee list --}}
    <div
        class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
    >
        {{-- Filters --}}
        <div
            class="border-b border-zinc-200 p-5 dark:border-zinc-700"
        >
            <div
                class="grid gap-3 md:grid-cols-2 xl:grid-cols-6"
            >
                <div class="md:col-span-2">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        icon="magnifying-glass"
                        placeholder="ស្វែងរកឈ្មោះ កូដ ទូរស័ព្ទ..."
                        clearable
                    />
                </div>

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

                <flux:select
                    wire:model.live="filterDepartment"
                >
                    <flux:select.option value="">
                        ផ្នែកទាំងអស់
                    </flux:select.option>

                    @foreach (
                        $this->filterDepartments
                        as $department
                    )
                        <flux:select.option
                            value="{{ $department->id }}"
                        >
                            {{ $department->name }}
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select
                    wire:model.live="filterStatus"
                >
                    <flux:select.option value="">
                        ស្ថានភាពទាំងអស់
                    </flux:select.option>

                    <flux:select.option value="Draft">
                        ព្រាង
                    </flux:select.option>

                    <flux:select.option value="Active">
                        កំពុងបម្រើការងារ
                    </flux:select.option>

                    <flux:select.option value="On probation">
                        កំពុងសាកល្បង
                    </flux:select.option>

                    <flux:select.option value="On leave">
                        កំពុងឈប់សម្រាក
                    </flux:select.option>

                    <flux:select.option value="Suspended">
                        ផ្អាកការងារ
                    </flux:select.option>

                    <flux:select.option value="Resigned">
                        លាឈប់
                    </flux:select.option>

                    <flux:select.option value="Terminated">
                        បញ្ចប់កិច្ចសន្យា
                    </flux:select.option>

                    <flux:select.option value="Retired">
                        ចូលនិវត្តន៍
                    </flux:select.option>
                </flux:select>

                <div class="flex gap-2">
                    <div class="min-w-0 flex-1">
                        <flux:select
                            wire:model.live="filterActive"
                        >
                            <flux:select.option value="">
                                ទាំងអស់
                            </flux:select.option>

                            <flux:select.option value="1">
                                បើក
                            </flux:select.option>

                            <flux:select.option value="0">
                                បិទ
                            </flux:select.option>
                        </flux:select>
                    </div>

                    <flux:button
                        type="button"
                        variant="ghost"
                        icon="x-mark"
                        square
                        wire:click="clearFilters"
                        title="សម្អាតការស្វែងរក"
                    />
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-5 py-4 font-medium">
                            បុគ្គលិក
                        </th>

                        <th class="px-5 py-4 font-medium">
                            ភេទ
                        </th>

                        <th class="px-5 py-4 font-medium">
                            ទំនាក់ទំនង
                        </th>

                        <th class="px-5 py-4 font-medium">
                            សាខា និងផ្នែក
                        </th>

                        <th class="px-5 py-4 font-medium">
                            មុខតំណែង
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
                    @forelse ($this->employees as $employee)
                        @php
                            $employeeName =
                                $employee->full_name_km
                                ?: $employee->full_name_en
                                ?: trim(
                                    $employee->first_name
                                    . ' '
                                    . $employee->last_name
                                );

                            $initial = mb_substr(
                                $employeeName,
                                0,
                                1
                            );

                            $genderLabel = match (
                                $employee->gender
                            ) {
                                'M' => 'ប្រុស',
                                'F' => 'ស្រី',
                                'Other' => 'ផ្សេងៗ',
                                default => 'មិនបានកំណត់',
                            };

                            $statusLabel = match (
                                $employee->employment_status
                            ) {
                                'Active' =>
                                    'កំពុងបម្រើការងារ',

                                'On probation' =>
                                    'កំពុងសាកល្បង',

                                'On leave' =>
                                    'កំពុងឈប់សម្រាក',

                                'Suspended' =>
                                    'ផ្អាកការងារ',

                                'Resigned' =>
                                    'លាឈប់',

                                'Terminated' =>
                                    'បញ្ចប់កិច្ចសន្យា',

                                'Retired' =>
                                    'ចូលនិវត្តន៍',

                                default =>
                                    'ព្រាង',
                            };

                            $statusColor = match (
                                $employee->employment_status
                            ) {
                                'Active' => 'green',
                                'On probation' => 'blue',
                                'On leave' => 'amber',
                                'Suspended' => 'red',

                                'Resigned',
                                'Terminated',
                                'Retired' => 'zinc',

                                default => 'zinc',
                            };
                        @endphp

                        <tr
                            wire:key="employee-{{ $employee->id }}"
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        >
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    @if ($employee->profile_photo)
                                        <img
                                            src="{{ asset(
                                                'storage/'
                                                . ltrim(
                                                    $employee->profile_photo,
                                                    '/'
                                                )
                                            ) }}"
                                            alt="{{ $employeeName }}"
                                            class="h-11 w-11 rounded-full object-cover"
                                        >
                                    @else
                                        <div
                                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-zinc-100 font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                                        >
                                            {{ $initial }}
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <div
                                            class="truncate font-medium text-zinc-900 dark:text-white"
                                        >
                                            {{ $employeeName }}
                                        </div>

                                        <div
                                            class="mt-1 text-xs text-zinc-500"
                                        >
                                            {{ $employee->employee_code }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                {{ $genderLabel }}
                            </td>

                            <td class="px-5 py-4">
                                <div>
                                    {{ $employee->phone ?: '—' }}
                                </div>

                                <div
                                    class="mt-1 max-w-48 truncate text-xs text-zinc-500"
                                >
                                    {{ $employee->email
                                        ?: 'មិនមានអ៊ីមែល' }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div>
                                    {{ $employee->branch?->name
                                        ?? '—' }}
                                </div>

                                <div
                                    class="mt-1 text-xs text-zinc-500"
                                >
                                    {{ $employee->department?->name
                                        ?? '—' }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div>
                                    {{ $employee->position?->title
                                        ?? 'មិនទាន់កំណត់' }}
                                </div>

                                <div
                                    class="mt-1 text-xs text-zinc-500"
                                >
                                    {{ $employee->employmentType?->name
                                        ?? 'មិនទាន់កំណត់' }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div class="space-y-2">
                                    <flux:badge
                                        size="sm"
                                        :color="$statusColor"
                                    >
                                        {{ $statusLabel }}
                                    </flux:badge>

                                    <div>
                                        @if ($employee->is_active)
                                            <span
                                                class="text-xs text-green-600"
                                            >
                                                ● បើកដំណើរការ
                                            </span>
                                        @else
                                            <span
                                                class="text-xs text-zinc-500"
                                            >
                                                ● បានបិទ
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div
                                    class="flex justify-end gap-1"
                                >
                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="ghost"
                                        icon="eye"
                                        square
                                        :href="route(
                                            'employees.show',
                                            $employee
                                        )"
                                        wire:navigate
                                        title="មើលព័ត៌មាន"
                                    />

                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="ghost"
                                        icon="pencil-square"
                                        square
                                        wire:click="edit({{ $employee->id }})"
                                        title="កែប្រែ"
                                    />

                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="ghost"
                                        :icon="$employee->is_active
                                            ? 'pause-circle'
                                            : 'play-circle'"
                                        square
                                        wire:click="toggleStatus({{ $employee->id }})"
                                        title="{{ $employee->is_active
                                            ? 'បិទដំណើរការ'
                                            : 'បើកដំណើរការ' }}"
                                    />

                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="danger"
                                        icon="trash"
                                        square
                                        wire:click="delete({{ $employee->id }})"
                                        wire:confirm="តើអ្នកពិតជាចង់លុបបុគ្គលិកនេះមែនទេ?"
                                        title="លុប"
                                    />
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="7"
                                class="px-5 py-14 text-center"
                            >
                                <div
                                    class="font-medium text-zinc-700 dark:text-zinc-200"
                                >
                                    មិនទាន់មានបុគ្គលិក
                                </div>

                                <p
                                    class="mt-2 text-sm text-zinc-500"
                                >
                                    បន្ថែមបុគ្គលិកថ្មី
                                    ឬប្តូរលក្ខខណ្ឌស្វែងរក។
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->employees->hasPages())
            <div
                class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-700"
            >
                {{ $this->employees->links() }}
            </div>
        @endif
    </div>
</div>