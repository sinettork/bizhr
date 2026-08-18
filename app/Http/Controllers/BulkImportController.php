<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmploymentType;
use App\Models\Position;
use App\Services\UploadedFileSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\SimpleExcel\SimpleExcelReader;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkImportController extends Controller
{
    private const MAX_ROWS = 2000;

    public function index(Request $request): View
    {
        $types = $this->availableTypes($request);
        $selectedType = $request->string('type')->toString();
        abort_if($selectedType && ! array_key_exists($selectedType, $types), 404);

        return view('imports.index', compact('types', 'selectedType'));
    }

    public function template(Request $request, string $type): StreamedResponse|BinaryFileResponse
    {
        $definition = $this->definition($type, $request);

        if ($request->string('format', 'xlsx')->lower()->toString() === 'csv') {
            return response()->streamDownload(function () use ($definition): void {
                $out = fopen('php://output', 'w');
                if ($out === false) {
                    throw new \RuntimeException('Unable to open the CSV output stream.');
                }

                try {
                    fputcsv($out, $definition['headers']);
                    fputcsv($out, $definition['example']);
                } finally {
                    fclose($out);
                }
            }, "bizhr-{$type}-template.csv", ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        $path = tempnam(sys_get_temp_dir(), 'bizhr-import-').'.xlsx';
        $writer = SimpleExcelWriter::create($path);
        $writer->nameCurrentSheet('Data')->addHeader($definition['headers'])->addRow($definition['example']);
        $writer->addNewSheetAndMakeItCurrent('Instructions')
            ->addHeader(['Step', 'Instruction'])
            ->addRow(['1', 'Enter data only in the Data sheet.'])
            ->addRow(['2', 'Do not rename, remove, or reorder the column headers.'])
            ->addRow(['3', 'Use yes/no for true/false fields and keep referenced codes consistent.'])
            ->addRow(['4', 'Upload the file, review the preview, then confirm the import.']);
        $writer->close();

        return response()->download(
            $path,
            "bizhr-{$type}-template.xlsx",
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend();
    }

    public function preview(Request $request, string $type, UploadedFileSecurityService $fileSecurity): RedirectResponse
    {
        $definition = $this->definition($type, $request);
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,csv,txt', 'max:5120']]);
        $file = $request->file('file');
        $fileSecurity->assertSafe($file, 'file');
        $extension = strtolower($file->getClientOriginalExtension());
        $reader = SimpleExcelReader::create($file->getRealPath(), $extension === 'xlsx' ? 'xlsx' : 'csv');
        if ($extension === 'xlsx' && $reader->hasSheet('Data')) {
            $reader->fromSheetName('Data');
        }
        $fileRows = $reader->noHeaderRow()->getRows();
        $firstRow = $fileRows->first();
        $headers = $firstRow ?? [];
        $headers = array_map(fn ($v) => Str::of((string) $v)->trim()->lower()->replace(' ', '_')->replace("\xEF\xBB\xBF", '')->value(), $headers);
        if ($headers !== $definition['headers']) {
            $reader->close();

            return back()->withErrors(['file' => 'Template columns do not match. Download the latest template and do not rename or reorder columns.']);
        }
        $rows = [];
        $line = 1;
        foreach ($fileRows->skip(1) as $values) {
            $line++;
            if ($line > self::MAX_ROWS + 1) {
                $reader->close();

                return back()->withErrors(['file' => 'A file can contain up to '.self::MAX_ROWS.' data rows.']);
            }
            $values = array_values($values);
            if (! array_filter($values, fn ($v) => filled(trim((string) $v)))) {
                continue;
            }
            $raw = array_combine($headers, array_map(fn ($index) => trim((string) ($values[$index] ?? '')), array_keys($headers)));
            $result = ($definition['prepare'])($raw);
            $rows[] = ['line' => $line, 'raw' => $raw, 'data' => $result['data'], 'errors' => $result['errors']];
        }
        $reader->close();
        if (! $rows) {
            return back()->withErrors(['file' => 'The file has no data rows.']);
        }
        $token = Str::random(48);
        $request->session()->put("bulk_imports.{$token}", ['type' => $type, 'rows' => $rows, 'created_at' => now()->timestamp]);

        return redirect()->route('imports.preview.show', ['type' => $type, 'token' => $token]);
    }

    public function showPreview(Request $request, string $type, string $token): View
    {
        $definition = $this->definition($type, $request);
        $saved = $request->session()->get("bulk_imports.{$token}");
        abort_unless(is_array($saved) && ($saved['type'] ?? null) === $type && is_array($saved['rows'] ?? null), 419, 'Import preview has expired. Upload the file again.');

        $rows = collect($saved['rows']);
        $perPage = 50;
        $page = max(1, $request->integer('page', 1));
        $paginator = new LengthAwarePaginator(
            $rows->forPage($page, $perPage)->values(),
            $rows->count(),
            $perPage,
            $page,
            ['path' => route('imports.preview.show', ['type' => $type, 'token' => $token])]
        );

        return view('imports.preview', [
            'definition' => $definition,
            'rows' => $paginator->items(),
            'paginator' => $paginator,
            'totalRows' => $rows->count(),
            'invalidCount' => $rows->filter(fn ($row) => ! empty($row['errors']))->count(),
            'token' => $token,
            'type' => $type,
        ]);
    }

    public function confirm(Request $request, string $type): RedirectResponse
    {
        $definition = $this->definition($type, $request);
        $data = $request->validate(['token' => ['required', 'string']]);
        $saved = $request->session()->pull("bulk_imports.{$data['token']}");
        abort_unless(is_array($saved) && ($saved['type'] ?? null) === $type && is_array($saved['rows'] ?? null), 419, 'Import preview has expired. Upload the file again.');
        $invalid = collect($saved['rows'])->filter(fn ($row) => ! empty($row['errors']));
        if ($invalid->isNotEmpty()) {
            return redirect()->route('imports.index')->withErrors(['file' => 'Fix all invalid rows before confirming the import.']);
        }
        $companyId = $this->companyId();
        $created = 0;
        $updated = 0;
        DB::transaction(function () use ($saved, $definition, $companyId, &$created, &$updated): void {
            foreach ($saved['rows'] as $row) {
                $action = ($definition['save'])($row['data'], $companyId);
                $action === 'created' ? $created++ : $updated++;
            }
        });

        return redirect()->route('imports.index')->with('status', "Import complete: {$created} created, {$updated} updated.");
    }

    /** @return array<string, array{label: string, permission: string}> */
    private function availableTypes(Request $request): array
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        return array_filter(['branches' => ['label' => 'Branches', 'permission' => 'branch.create'], 'departments' => ['label' => 'Departments', 'permission' => 'department.create'], 'positions' => ['label' => 'Positions', 'permission' => 'position.create'], 'employment-types' => ['label' => 'Employment types', 'permission' => 'employment-type.create'], 'employees' => ['label' => 'Employees', 'permission' => 'employee.create']], fn (array $item): bool => $user->can($item['permission']));
    }

    /**
     * @return array{
     *     title: string,
     *     headers: list<string>,
     *     example: list<string>,
     *     prepare: callable(array<string, string>): array{data: array<string, mixed>, errors: array<int, string>},
     *     save: callable(array<string, mixed>, int): string
     * }
     */
    private function definition(string $type, Request $request): array
    {
        $permissions = ['branches' => 'branch.create', 'departments' => 'department.create', 'positions' => 'position.create', 'employment-types' => 'employment-type.create', 'employees' => 'employee.create'];
        abort_unless(isset($permissions[$type]) && $request->user()->can($permissions[$type]), 404);
        $bool = fn ($value) => in_array(strtolower(trim((string) $value)), ['1', 'true', 'yes', 'y'], true);
        $valid = function (array $data, array $rules): array {
            $validator = Validator::make($data, $rules);

            return $validator->fails() ? $validator->errors()->all() : [];
        };

        return match ($type) {
            'branches' => ['title' => 'Branches', 'headers' => ['code', 'name', 'manager_name', 'email', 'phone', 'city', 'address', 'is_head_office', 'is_active'], 'example' => ['PPH-HO', 'Phnom Penh Head Office', 'Sok Dara', 'office@example.com', '012345678', 'Phnom Penh', 'Street 1', 'yes', 'yes'], 'prepare' => function ($row) use ($bool, $valid) {
                $data = [...$row, 'is_head_office' => $bool($row['is_head_office']), 'is_active' => $bool($row['is_active'])];

                return ['data' => $data, 'errors' => $valid($data, ['code' => ['required', 'max:50'], 'name' => ['required', 'max:180'], 'email' => ['nullable', 'email'], 'phone' => ['nullable', 'max:50']])];
            }, 'save' => fn ($data, $companyId) => Branch::query()->updateOrCreate(['company_id' => $companyId, 'code' => $data['code']], ['name' => $data['name'], 'manager_name' => $data['manager_name'] ?: null, 'email' => $data['email'] ?: null, 'phone' => $data['phone'] ?: null, 'city' => $data['city'] ?: null, 'address' => $data['address'] ?: null, 'is_head_office' => $data['is_head_office'], 'is_active' => $data['is_active']])->wasRecentlyCreated ? 'created' : 'updated'],
            'departments' => ['title' => 'Departments', 'headers' => ['branch_code', 'code', 'name', 'manager_name', 'phone', 'email', 'description', 'is_active'], 'example' => ['PPH-HO', 'HR', 'Human Resources', 'Sok Dara', '012345678', 'hr@example.com', 'People operations', 'yes'], 'prepare' => function ($row) use ($bool, $valid) {
                $branch = Branch::query()->where('company_id', $this->companyId())->where('code', $row['branch_code'])->first();
                $data = [...$row, 'branch_id' => $branch?->id, 'is_active' => $bool($row['is_active'])];
                $errors = $valid($data, ['branch_code' => ['required'], 'code' => ['required', 'max:50'], 'name' => ['required', 'max:180'], 'email' => ['nullable', 'email']]);
                if (! $branch) {
                    $errors[] = 'Branch code does not exist.';
                }

                return ['data' => $data, 'errors' => $errors];
            }, 'save' => fn ($data, $companyId) => Department::query()->updateOrCreate(['company_id' => $companyId, 'branch_id' => $data['branch_id'], 'code' => $data['code']], ['name' => $data['name'], 'manager_name' => $data['manager_name'] ?: null, 'phone' => $data['phone'] ?: null, 'email' => $data['email'] ?: null, 'description' => $data['description'] ?: null, 'is_active' => $data['is_active']])->wasRecentlyCreated ? 'created' : 'updated'],
            'positions' => ['title' => 'Positions', 'headers' => ['branch_code', 'department_code', 'code', 'title', 'minimum_salary', 'maximum_salary', 'is_manager_position', 'is_active', 'sort_order', 'description'], 'example' => ['PPH-HO', 'HR', 'HR-OFF', 'HR Officer', '400', '700', 'no', 'yes', '10', 'HR operations role'], 'prepare' => function ($row) use ($bool, $valid) {
                $branch = Branch::query()->where('company_id', $this->companyId())->where('code', $row['branch_code'])->first();
                $department = $branch ? Department::query()->where('company_id', $this->companyId())->where('branch_id', $branch->id)->where('code', $row['department_code'])->first() : null;
                $data = [...$row, 'branch_id' => $branch?->id, 'department_id' => $department?->id, 'minimum_salary' => $row['minimum_salary'] ?: null, 'maximum_salary' => $row['maximum_salary'] ?: null, 'is_manager_position' => $bool($row['is_manager_position']), 'is_active' => $bool($row['is_active']), 'sort_order' => (int) ($row['sort_order'] ?: 0)];
                $errors = $valid($data, ['code' => ['required', 'max:50'], 'title' => ['required', 'max:180'], 'minimum_salary' => ['nullable', 'numeric', 'min:0'], 'maximum_salary' => ['nullable', 'numeric', 'gte:minimum_salary']]);
                if (! $branch) {
                    $errors[] = 'Branch code does not exist.';
                } if (! $department) {
                    $errors[] = 'Department code does not exist in the selected branch.';
                }

                return ['data' => $data, 'errors' => $errors];
            }, 'save' => fn ($data, $companyId) => Position::query()->updateOrCreate(['company_id' => $companyId, 'branch_id' => $data['branch_id'], 'department_id' => $data['department_id'], 'code' => $data['code']], ['title' => $data['title'], 'minimum_salary' => $data['minimum_salary'], 'maximum_salary' => $data['maximum_salary'], 'is_manager_position' => $data['is_manager_position'], 'is_active' => $data['is_active'], 'sort_order' => $data['sort_order'], 'description' => $data['description'] ?: null])->wasRecentlyCreated ? 'created' : 'updated'],
            'employment-types' => ['title' => 'Employment types', 'headers' => ['code', 'name', 'description', 'is_active', 'sort_order'], 'example' => ['FULL', 'Full time', 'Standard full-time employment', 'yes', '10'], 'prepare' => function ($row) use ($bool, $valid) {
                $data = [...$row, 'is_active' => $bool($row['is_active']), 'sort_order' => (int) ($row['sort_order'] ?: 0)];

                return ['data' => $data, 'errors' => $valid($data, ['code' => ['required', 'max:50'], 'name' => ['required', 'max:180']])];
            }, 'save' => fn ($data, $companyId) => EmploymentType::query()->updateOrCreate(['company_id' => $companyId, 'code' => $data['code']], ['name' => $data['name'], 'description' => $data['description'] ?: null, 'is_active' => $data['is_active'], 'sort_order' => $data['sort_order']])->wasRecentlyCreated ? 'created' : 'updated'],
            'employees' => ['title' => 'Employees', 'headers' => ['employee_code', 'first_name', 'last_name', 'full_name_km', 'full_name_en', 'branch_code', 'department_code', 'position_code', 'employment_type_code', 'email', 'phone', 'hire_date', 'base_salary', 'salary_currency', 'employment_status'], 'example' => ['EMP-001', 'Dara', 'Sok', '', 'Dara Sok', 'PPH-HO', 'HR', 'HR-OFF', 'FULL', 'dara@example.com', '012345678', '2026-01-01', '500', 'USD', 'Active'], 'prepare' => function ($row) use ($valid) {
                $companyId = $this->companyId();
                $branch = Branch::query()->where('company_id', $companyId)->where('code', $row['branch_code'])->first();
                $department = $branch ? Department::query()->where('company_id', $companyId)->where('branch_id', $branch->id)->where('code', $row['department_code'])->first() : null;
                $position = filled($row['position_code']) && $department ? Position::query()->where('company_id', $companyId)->where('branch_id', $branch->id)->where('department_id', $department->id)->where('code', $row['position_code'])->first() : null;
                $employment = filled($row['employment_type_code']) ? EmploymentType::query()->where('company_id', $companyId)->where('code', $row['employment_type_code'])->first() : null;
                $data = [...$row, 'branch_id' => $branch?->id, 'department_id' => $department?->id, 'position_id' => $position?->id, 'employment_type_id' => $employment?->id, 'base_salary' => $row['base_salary'] ?: null, 'salary_currency' => strtoupper($row['salary_currency'] ?: 'USD')];
                $errors = $valid($data, ['employee_code' => ['required', 'max:50'], 'first_name' => ['required', 'max:255'], 'last_name' => ['required', 'max:255'], 'email' => ['nullable', 'email'], 'hire_date' => ['required', 'date'], 'base_salary' => ['nullable', 'numeric', 'min:0'], 'salary_currency' => ['in:USD,KHR'], 'employment_status' => ['required', 'in:Draft,Active,On probation,On leave,Suspended,Resigned,Terminated,Retired']]);
                if (! $branch) {
                    $errors[] = 'Branch code does not exist.';
                } if (! $department) {
                    $errors[] = 'Department code does not exist in the selected branch.';
                } if (filled($row['position_code']) && ! $position) {
                    $errors[] = 'Position code does not exist in the selected department.';
                } if (filled($row['employment_type_code']) && ! $employment) {
                    $errors[] = 'Employment type code does not exist.';
                }

                return ['data' => $data, 'errors' => $errors];
            }, 'save' => fn ($data, $companyId) => Employee::query()->updateOrCreate(['company_id' => $companyId, 'employee_code' => $data['employee_code']], ['first_name' => $data['first_name'], 'last_name' => $data['last_name'], 'full_name_km' => $data['full_name_km'] ?: null, 'full_name_en' => $data['full_name_en'] ?: null, 'branch_id' => $data['branch_id'], 'department_id' => $data['department_id'], 'position_id' => $data['position_id'], 'employment_type_id' => $data['employment_type_id'], 'email' => $data['email'] ?: null, 'phone' => $data['phone'] ?: null, 'hire_date' => $data['hire_date'], 'base_salary' => $data['base_salary'], 'salary_currency' => $data['salary_currency'], 'employment_status' => $data['employment_status'], 'is_active' => in_array($data['employment_status'], ['Active', 'On probation', 'On leave'], true)])->wasRecentlyCreated ? 'created' : 'updated'],
        };
    }

    private function companyId(): int
    {
        return (int) Company::query()->value('id');
    }
}
