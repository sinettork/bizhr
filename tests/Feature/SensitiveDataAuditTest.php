<?php

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;

test('sensitive audit logs are append only', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $employee = new Employee;
    $employee->id = 123;

    $log = AuditLog::record($employee, 'viewed_sensitive_profile', [], [
        'fields' => ['base_salary', 'bank_details'],
        'own_record' => true,
    ]);

    expect($log->action)->toBe('viewed_sensitive_profile')
        ->and($log->user_id)->toBe($user->id)
        ->and($log->new_values['fields'])->toContain('base_salary');

    expect(fn () => $log->update(['action' => 'changed']))
        ->toThrow(LogicException::class);
});

test('document audit metadata does not contain document contents', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $document = new EmployeeDocument;
    $document->id = 456;

    $log = AuditLog::record($document, 'downloaded', [], [
        'employee_id' => 10,
        'document_type' => 'contract',
        'original_name' => 'contract.pdf',
    ]);

    expect($log->new_values)->toHaveKeys([
        'employee_id',
        'document_type',
        'original_name',
    ])->not->toHaveKey('contents');
});

test('automatic audit logs redact credentials and identity numbers', function () {
    $actor = User::factory()->create();
    $this->actingAs($actor);

    $target = User::factory()->create();
    $userLog = AuditLog::query()
        ->where('record_type', User::class)
        ->where('record_id', (string) $target->id)
        ->where('action', 'created')
        ->firstOrFail();

    expect($userLog->new_values['password'])->toBe('[REDACTED]');

    $company = Company::query()->create(['name' => 'Security Test']);
    $branch = Branch::query()->create([
        'company_id' => $company->id,
        'name' => 'Head Office',
        'code' => 'SEC-HQ',
    ]);
    $department = Department::query()->create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'name' => 'Security',
        'code' => 'SEC-DEP',
    ]);

    $employee = Employee::query()->create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'department_id' => $department->id,
        'employee_code' => 'SEC-001',
        'first_name' => 'Sok',
        'last_name' => 'Dara',
        'national_id' => '123456789',
        'passport_number' => 'P1234567',
        'bank_account_number' => '0011223344',
        'hire_date' => now()->toDateString(),
        'employment_status' => 'Active',
        'is_active' => true,
    ]);
    $employeeLog = AuditLog::query()
        ->where('record_type', Employee::class)
        ->where('record_id', (string) $employee->id)
        ->where('action', 'created')
        ->firstOrFail();

    expect($employeeLog->new_values)
        ->national_id->toBe('[REDACTED]')
        ->passport_number->toBe('[REDACTED]')
        ->bank_account_number->toBe('[REDACTED]');
});
