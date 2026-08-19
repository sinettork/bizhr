<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Notification;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Services\PayrollWorkflowService;
use Illuminate\Validation\ValidationException;

function payrollWorkflowCompanyUser(Company $company, Branch $branch, Department $department, string $code): User
{
    $user = User::factory()->create();
    Employee::query()->create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'department_id' => $department->id,
        'user_id' => $user->id,
        'employee_code' => $code,
        'first_name' => 'Payroll',
        'last_name' => 'User',
        'hire_date' => '2026-01-01',
        'employment_status' => 'Active',
        'salary_currency' => 'USD',
        'is_active' => true,
    ]);

    return $user->fresh();
}

it('notifies a linked employee when payroll is recorded paid', function (): void {
    $company = Company::factory()->create();
    $branch = Branch::factory()->create(['company_id' => $company->id]);
    $department = Department::factory()->create(['company_id' => $company->id, 'branch_id' => $branch->id]);
    $employeeUser = payrollWorkflowCompanyUser($company, $branch, $department, 'PAY-EMPLOYEE');
    $recorder = payrollWorkflowCompanyUser($company, $branch, $department, 'PAY-RECORDER');
    $employee = Employee::query()->where('user_id', $employeeUser->id)->firstOrFail();

    $period = PayrollPeriod::query()->create([
        'company_id' => $company->id,
        'name' => 'August 2026',
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-31',
        'payment_date' => '2026-09-01',
        'status' => 'approved',
    ]);
    PayrollItem::query()->create([
        'payroll_period_id' => $period->id,
        'employee_id' => $employee->id,
        'currency' => 'USD',
        'base_salary' => 1000,
        'gross_salary' => 1000,
        'net_salary' => 950,
        'payment_status' => 'unpaid',
        'exception_count' => 0,
    ]);

    app(PayrollWorkflowService::class)->recordPayment($period, $recorder, [
        'payment_method' => 'bank_transfer',
        'reference_number' => 'AUG-2026-PAID',
        'paid_at' => '2026-09-01 09:00:00',
        'notes' => null,
    ]);

    expect(Notification::query()
        ->where('user_id', $employeeUser->id)
        ->where('company_id', $company->id)
        ->where('type', 'payroll_paid')
        ->count())->toBe(1);
});

it('rejects payroll approval by an actor from another company at the service boundary', function (): void {
    $company = Company::factory()->create();
    $branch = Branch::factory()->create(['company_id' => $company->id]);
    $department = Department::factory()->create(['company_id' => $company->id, 'branch_id' => $branch->id]);
    $processor = payrollWorkflowCompanyUser($company, $branch, $department, 'PAY-PROCESSOR');

    $otherCompany = Company::factory()->create();
    $otherBranch = Branch::factory()->create(['company_id' => $otherCompany->id]);
    $otherDepartment = Department::factory()->create(['company_id' => $otherCompany->id, 'branch_id' => $otherBranch->id]);
    $foreignApprover = payrollWorkflowCompanyUser($otherCompany, $otherBranch, $otherDepartment, 'FOREIGN-APPROVER');

    $employee = Employee::factory()->create(['company_id' => $company->id, 'branch_id' => $branch->id, 'department_id' => $department->id]);
    $period = PayrollPeriod::query()->create([
        'company_id' => $company->id,
        'name' => 'Boundary payroll',
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-31',
        'payment_date' => '2026-09-01',
        'status' => 'awaiting_approval',
        'processed_by' => $processor->id,
        'processed_at' => now(),
    ]);
    PayrollItem::query()->create([
        'payroll_period_id' => $period->id,
        'employee_id' => $employee->id,
        'currency' => 'USD',
        'base_salary' => 500,
        'gross_salary' => 500,
        'net_salary' => 500,
        'payment_status' => 'unpaid',
        'exception_count' => 0,
    ]);

    expect(fn () => app(PayrollWorkflowService::class)->approve($period, $foreignApprover))
        ->toThrow(ValidationException::class, 'does not belong');
});
