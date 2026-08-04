<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class EmployeeImportService
{
    public function validate(array $rows, int $companyId): array
    {
        $results = [];
        foreach ($rows as $index => $row) {
            $validator = Validator::make($row, [
                'employee_code' => 'required|string|max:50',
                'first_name' => 'required|string|max:100',
                'last_name' => 'required|string|max:100',
                'branch_id' => 'required|integer|exists:branches,id',
                'department_id' => 'required|integer|exists:departments,id',
                'hire_date' => 'required|date',
                'employment_status' => 'required|in:Active,Draft,On probation,On leave,Suspended,Resigned,Terminated,Retired',
                'base_salary' => 'nullable|numeric|min:0',
                'salary_currency' => 'nullable|in:USD,KHR',
            ]);

            $rowErrors = $validator->errors()->all();
            
            // Extra check for code uniqueness if it passed basic validation
            if (!$validator->errors()->has('employee_code')) {
                if (Employee::where('employee_code', $row['employee_code'])->exists()) {
                    $rowErrors[] = "លេខកូដបុគ្គលិក '{$row['employee_code']}' មានរួចហើយ។";
                }
            }

            $results[] = [
                'row' => $index + 2, // +1 for 0-index, +1 for header
                'data' => $row,
                'errors' => $rowErrors,
                'is_valid' => count($rowErrors) === 0,
            ];
        }

        return $results;
    }

    public function commit(array $rows, int $companyId): int
    {
        return DB::transaction(function () use ($rows, $companyId) {
            $count = 0;
            foreach ($rows as $row) {
                Employee::create([
                    'company_id' => $companyId,
                    'branch_id' => $row['branch_id'],
                    'department_id' => $row['department_id'],
                    'employee_code' => $row['employee_code'],
                    'first_name' => $row['first_name'],
                    'last_name' => $row['last_name'],
                    'full_name_km' => $row['full_name_km'] ?? null,
                    'hire_date' => $row['hire_date'],
                    'employment_status' => $row['employment_status'],
                    'base_salary' => $row['base_salary'] ?? 0,
                    'salary_currency' => $row['salary_currency'] ?? 'USD',
                    'is_active' => true,
                ]);
                $count++;
            }
            return $count;
        });
    }
}
