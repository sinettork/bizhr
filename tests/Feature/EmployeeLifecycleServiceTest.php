<?php

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmploymentHistory;
use App\Models\User;
use App\Services\EmployeeLifecycleService;

it('records probation confirmation when probation becomes active', function (): void {
    $company = Company::factory()->create();
    $actor = User::factory()->create();
    $employee = Employee::factory()->create([
        'company_id' => $company->id,
        'employment_status' => 'On probation',
        'is_active' => true,
    ]);

    $this->actingAs($actor);

    expect(app(EmployeeLifecycleService::class)->transitionStatus($employee, 'Active', reason: 'Probation passed'))
        ->toBeTrue();

    expect($employee->fresh()->employment_status)->toBe('Active');
    expect(EmploymentHistory::query()
        ->where('employee_id', $employee->id)
        ->latest('id')
        ->value('event_type'))->toBe('probation_confirmation');
});

it('reinstates a separated employee with a valid status and reactivates access', function (): void {
    $company = Company::factory()->create();
    $user = User::factory()->create(['is_active' => false]);
    $employee = Employee::factory()->create([
        'company_id' => $company->id,
        'user_id' => $user->id,
        'employment_status' => 'Resigned',
        'is_active' => false,
    ]);

    $this->actingAs(User::factory()->create());

    expect(app(EmployeeLifecycleService::class)->reinstate($employee, reason: 'Return to employment'))
        ->toBeTrue();

    expect($employee->fresh()->employment_status)->toBe('Active')
        ->and($employee->fresh()->is_active)->toBeTrue()
        ->and($user->fresh()->is_active)->toBeTrue();

    expect(EmploymentHistory::query()
        ->where('employee_id', $employee->id)
        ->latest('id')
        ->value('event_type'))->toBe('reinstatement');
});

it('rejects unsupported reinstatement states instead of reporting success', function (): void {
    $employee = Employee::factory()->create([
        'employment_status' => 'Active',
        'is_active' => true,
    ]);

    expect(app(EmployeeLifecycleService::class)->reinstate($employee))->toBeFalse();
});
