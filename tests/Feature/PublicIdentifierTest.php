<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\DataExport;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmploymentContract;
use App\Models\EmploymentHistory;
use App\Models\LeaveRequest;
use Illuminate\Support\Str;

function publicIdentifierEmployee(): Employee
{
    $company = Company::query()->create([
        'name' => 'Public Identifier Company',
        'currency' => 'USD',
        'timezone' => 'Asia/Phnom_Penh',
        'date_format' => 'd/m/Y',
    ]);

    $branch = Branch::query()->create([
        'company_id' => $company->id,
        'name' => 'Head Office',
        'code' => 'PUBLIC-HQ',
        'is_active' => true,
    ]);

    $department = Department::query()->create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'name' => 'People Operations',
        'code' => 'PUBLIC-HR',
        'is_active' => true,
    ]);

    return Employee::query()->create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'department_id' => $department->id,
        'employee_code' => 'PUBLIC-EMP-001',
        'first_name' => 'Dara',
        'last_name' => 'Sok',
        'hire_date' => '2026-01-01',
        'employment_status' => 'Active',
        'is_active' => true,
    ]);
}

it('generates a public UUID and uses it in employee URLs', function () {
    $employee = publicIdentifierEmployee();

    expect(Str::isUuid($employee->public_id))->toBeTrue()
        ->and($employee->getRouteKeyName())->toBe('public_id')
        ->and(parse_url(route('employees.show', $employee), PHP_URL_PATH))
        ->toBe('/employees/'.$employee->public_id);
});

it('rejects numeric internal ids for public route binding', function () {
    $employee = publicIdentifierEmployee();

    expect((new Employee)->resolveRouteBinding((string) $employee->id))->toBeNull();
});

it('uses public UUID route keys for every externally linked record', function (string $model) {
    expect((new $model)->getRouteKeyName())->toBe('public_id');
})->with([
    EmployeeDocument::class,
    EmploymentHistory::class,
    LeaveRequest::class,
    EmploymentContract::class,
    DataExport::class,
]);
