<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Services\EmployeeImportService;
use Illuminate\Validation\ValidationException;

function importStructure(string $suffix): array
{
    $company = Company::query()->create([
        'name' => "Import Company {$suffix}",
        'currency' => 'USD',
        'timezone' => 'Asia/Phnom_Penh',
        'date_format' => 'd/m/Y',
    ]);

    $branch = Branch::query()->create([
        'company_id' => $company->id,
        'name' => "Branch {$suffix}",
        'code' => "BR-{$suffix}",
        'is_active' => true,
    ]);

    $department = Department::query()->create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'name' => "Department {$suffix}",
        'code' => "DEP-{$suffix}",
        'is_active' => true,
    ]);

    return compact('company', 'branch', 'department');
}

function validImportRow(array $structure, string $code = 'EMP-IMPORT-001'): array
{
    return [
        'employee_code' => $code,
        'first_name' => 'Dara',
        'last_name' => 'Sok',
        'full_name_km' => 'សុខ ដារ៉ា',
        'branch_id' => (string) $structure['branch']->id,
        'department_id' => (string) $structure['department']->id,
        'hire_date' => '2026-01-01',
        'employment_status' => 'Active',
        'base_salary' => '500',
        'salary_currency' => 'usd',
        'gender' => 'MALE',
        'email' => 'dara@example.com',
        'phone' => '012345678',
    ];
}

it('normalizes validates and commits a valid employee CSV preview', function () {
    $structure = importStructure('A');
    $service = app(EmployeeImportService::class);
    $results = $service->validate([validImportRow($structure)], $structure['company']->id);

    expect($results)->toHaveCount(1)
        ->and($results[0]['is_valid'])->toBeTrue()
        ->and($results[0]['data']['salary_currency'])->toBe('USD')
        ->and($results[0]['data']['gender'])->toBe('male');

    expect($service->commit([$results[0]['data']], $structure['company']->id))->toBe(1);

    $employee = Employee::query()->where('employee_code', 'EMP-IMPORT-001')->firstOrFail();

    expect($employee->company_id)->toBe($structure['company']->id)
        ->and($employee->branch_id)->toBe($structure['branch']->id)
        ->and($employee->department_id)->toBe($structure['department']->id)
        ->and($employee->salary_currency)->toBe('USD')
        ->and($employee->gender)->toBe('male');
});

it('rejects branches and departments owned by another company', function () {
    $first = importStructure('B');
    $second = importStructure('C');
    $row = validImportRow($first, 'EMP-IMPORT-002');
    $row['branch_id'] = (string) $second['branch']->id;
    $row['department_id'] = (string) $second['department']->id;

    $result = app(EmployeeImportService::class)->validate([$row], $first['company']->id)[0];

    expect($result['is_valid'])->toBeFalse()
        ->and($result['errors'])->not->toBeEmpty();
});

it('rejects duplicate codes within the same file before commit', function () {
    $structure = importStructure('D');
    $service = app(EmployeeImportService::class);
    $rows = [
        validImportRow($structure, 'EMP-DUPLICATE'),
        validImportRow($structure, 'emp-duplicate'),
    ];

    $results = $service->validate($rows, $structure['company']->id);

    expect($results[0]['is_valid'])->toBeTrue()
        ->and($results[1]['is_valid'])->toBeFalse()
        ->and(implode(' ', $results[1]['errors']))->toContain('ស្ទួន');
});

it('revalidates the preview and rolls back the whole import when any row is invalid', function () {
    $structure = importStructure('E');
    $valid = validImportRow($structure, 'EMP-VALID');
    $invalid = validImportRow($structure, 'EMP-INVALID');
    $invalid['department_id'] = '999999';

    expect(fn () => app(EmployeeImportService::class)->commit(
        [$valid, $invalid],
        $structure['company']->id,
    ))->toThrow(ValidationException::class);

    expect(Employee::query()->whereIn('employee_code', ['EMP-VALID', 'EMP-INVALID'])->exists())
        ->toBeFalse();
});

it('requires the exact versioned CSV headers', function () {
    $service = app(EmployeeImportService::class);

    expect($service->headersAreValid(EmployeeImportService::HEADERS))->toBeTrue()
        ->and($service->headersAreValid(['employee_code', 'first_name']))->toBeFalse();
});
