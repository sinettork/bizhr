<?php

use App\Models\Company;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Services\LeaveBalanceService;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;

afterEach(function (): void {
    Carbon::setTestNow();
});

it('accrues statutory annual leave monthly from the employee hire date', function () {
    Carbon::setTestNow('2026-08-13 12:00:00');
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);

    $company = Company::query()->firstOrFail();
    $employee = Employee::query()->where('company_id', $company->id)->firstOrFail();
    $employee->update(['hire_date' => '2026-01-01']);
    $type = LeaveType::query()->where('company_id', $company->id)->where('code', 'ANNUAL')->firstOrFail();

    $balance = app(LeaveBalanceService::class)->initializeForEmployeeAndType($employee->fresh(), $type, 2026);

    expect($type->is_statutory_annual_leave)->toBeTrue()
        ->and((float) $balance->earned_days)->toBe(12.0)
        ->and((float) $balance->remaining_days)->toBe(12.0);
});
