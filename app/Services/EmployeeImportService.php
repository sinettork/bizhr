<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmployeeImportService
{
    public const HEADERS = [
        'employee_code',
        'first_name',
        'last_name',
        'full_name_km',
        'branch_id',
        'department_id',
        'hire_date',
        'employment_status',
        'base_salary',
        'salary_currency',
        'gender',
        'email',
        'phone',
    ];

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array{row: int, data: array<string, string>, errors: list<string>, is_valid: bool}>
     */
    public function validate(array $rows, int $companyId): array
    {
        $results = [];
        $codesInFile = [];

        foreach ($rows as $index => $rawRow) {
            $row = $this->normalizeRow($rawRow);
            $branchId = filter_var($row['branch_id'] ?? null, FILTER_VALIDATE_INT) ?: 0;

            $validator = Validator::make($row, [
                'employee_code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9._-]+$/'],
                'first_name' => ['required', 'string', 'max:100'],
                'last_name' => ['required', 'string', 'max:100'],
                'full_name_km' => ['nullable', 'string', 'max:200'],
                'branch_id' => [
                    'required',
                    'integer',
                    Rule::exists('branches', 'id')->where(
                        fn ($query) => $query
                            ->where('company_id', $companyId)
                            ->where('is_active', true),
                    ),
                ],
                'department_id' => [
                    'required',
                    'integer',
                    Rule::exists('departments', 'id')->where(
                        fn ($query) => $query
                            ->where('company_id', $companyId)
                            ->where('branch_id', $branchId)
                            ->where('is_active', true),
                    ),
                ],
                'hire_date' => ['required', 'date_format:Y-m-d'],
                'employment_status' => [
                    'required',
                    Rule::in([
                        'Active',
                        'Draft',
                        'On probation',
                        'On leave',
                        'Suspended',
                        'Resigned',
                        'Terminated',
                        'Retired',
                    ]),
                ],
                'base_salary' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
                'salary_currency' => ['required', Rule::in(['USD', 'KHR'])],
                'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
                'email' => ['nullable', 'email:rfc', 'max:255'],
                'phone' => ['nullable', 'string', 'max:50'],
            ], [
                'employee_code.regex' => 'លេខកូដបុគ្គលិកអាចមានតែអក្សរឡាតាំង លេខ សញ្ញាចុច សញ្ញាគូសក្រោម និងសញ្ញាដក។',
                'branch_id.exists' => 'សាខាមិនត្រឹមត្រូវ មិនសកម្ម ឬមិនស្ថិតក្នុងក្រុមហ៊ុននេះ។',
                'department_id.exists' => 'ផ្នែកមិនត្រឹមត្រូវ ឬមិនស្ថិតក្នុងសាខាដែលបានជ្រើស។',
                'hire_date.date_format' => 'ថ្ងៃចូលធ្វើការត្រូវប្រើទម្រង់ YYYY-MM-DD។',
            ]);

            $rowErrors = $validator->errors()->all();
            if (filled($rawRow['__row_error'] ?? null)) {
                $rowErrors[] = (string) $rawRow['__row_error'];
            }
            $employeeCode = $row['employee_code'] ?? '';

            if ($employeeCode !== '') {
                if (isset($codesInFile[$employeeCode])) {
                    $rowErrors[] = "លេខកូដបុគ្គលិក '{$employeeCode}' ស្ទួនជាមួយជួរដេក {$codesInFile[$employeeCode]} ក្នុងឯកសារនេះ។";
                } else {
                    $codesInFile[$employeeCode] = $index + 2;
                }

                if (Employee::withTrashed()->where('employee_code', $employeeCode)->exists()) {
                    $rowErrors[] = "លេខកូដបុគ្គលិក '{$employeeCode}' មានរួចហើយក្នុងប្រព័ន្ធ។";
                }
            }

            $results[] = [
                'row' => $index + 2,
                'data' => $row,
                'errors' => array_values(array_unique($rowErrors)),
                'is_valid' => $rowErrors === [],
            ];
        }

        return $results;
    }

    /** @param list<array<string, mixed>> $rows */
    public function commit(array $rows, int $companyId): int
    {
        $results = $this->validate($rows, $companyId);
        $invalidRows = array_values(array_filter(
            $results,
            fn (array $result): bool => ! $result['is_valid'],
        ));

        if ($invalidRows !== []) {
            throw ValidationException::withMessages([
                'importFile' => 'ឯកសារបានផ្លាស់ប្ដូរ ឬមានទិន្នន័យមិនត្រឹមត្រូវ។ សូមត្រួតពិនិត្យឡើងវិញមុននាំចូល។',
            ]);
        }

        return DB::transaction(function () use ($results, $companyId): int {
            foreach ($results as $result) {
                $row = $result['data'];

                Employee::query()->create([
                    'company_id' => $companyId,
                    'branch_id' => (int) $row['branch_id'],
                    'department_id' => (int) $row['department_id'],
                    'employee_code' => $row['employee_code'],
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'full_name_km' => $row['full_name_km'] ?: null,
                    'hire_date' => $row['hire_date'],
                    'employment_status' => $row['employment_status'],
                    'base_salary' => $row['base_salary'] !== '' ? $row['base_salary'] : null,
                    'salary_currency' => $row['salary_currency'],
                    'gender' => $row['gender'] ?: null,
                    'email' => $row['email'] ?: null,
                    'phone' => $row['phone'] ?: null,
                    'is_active' => in_array($row['employment_status'], ['Active', 'On probation', 'On leave'], true),
                ]);
            }

            return count($results);
        });
    }

    /**
     * @param  list<mixed>  $headers
     * @return list<string>
     */
    public function normalizeHeaders(array $headers): array
    {
        return array_map(
            fn ($header): string => strtolower(trim((string) $header, " \t\n\r\0\x0B\xEF\xBB\xBF")),
            $headers,
        );
    }

    /** @param list<mixed> $headers */
    public function headersAreValid(array $headers): bool
    {
        return $this->normalizeHeaders($headers) === self::HEADERS;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array<string, string>
     */
    private function normalizeRow(array $row): array
    {
        $normalized = [];

        foreach (self::HEADERS as $header) {
            $normalized[$header] = trim((string) ($row[$header] ?? ''));
        }

        $normalized['employee_code'] = strtoupper($normalized['employee_code']);
        $normalized['salary_currency'] = strtoupper($normalized['salary_currency'] ?: 'USD');
        $normalized['gender'] = strtolower($normalized['gender']);
        $normalized['employment_status'] = $normalized['employment_status'] ?: 'Active';

        return $normalized;
    }
}
