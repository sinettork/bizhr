<?php

use App\Models\Asset;
use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\EmploymentContract;
use App\Models\ExpenseClaim;
use App\Models\JobApplicant;
use App\Models\JobVacancy;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\PayrollPeriod;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Gate;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
    $this->company = Company::query()->create(['name' => 'Policy company']);
    $this->branch = Branch::query()->create(['company_id' => $this->company->id, 'name' => 'Policy branch', 'code' => 'POLICY', 'is_active' => true]);
    $this->department = Department::query()->create(['company_id' => $this->company->id, 'branch_id' => $this->branch->id, 'name' => 'Policy department', 'code' => 'POLICY', 'is_active' => true]);
    $this->otherCompany = Company::query()->create(['name' => 'Other policy company']);
    $this->otherBranch = Branch::query()->create(['company_id' => $this->otherCompany->id, 'name' => 'Other branch', 'code' => 'OTHER-P', 'is_active' => true]);
    $this->otherDepartment = Department::query()->create(['company_id' => $this->otherCompany->id, 'branch_id' => $this->otherBranch->id, 'name' => 'Other department', 'code' => 'OTHER-P', 'is_active' => true]);
    $this->user = User::factory()->create();
    $this->employee = Employee::query()->create(['company_id' => $this->company->id, 'branch_id' => $this->branch->id, 'department_id' => $this->department->id, 'user_id' => $this->user->id, 'employee_code' => 'POLICY-USER', 'first_name' => 'Policy', 'last_name' => 'User', 'hire_date' => today(), 'employment_status' => 'Active', 'salary_currency' => 'USD', 'is_active' => true]);
    $this->otherEmployee = Employee::query()->create(['company_id' => $this->otherCompany->id, 'branch_id' => $this->otherBranch->id, 'department_id' => $this->otherDepartment->id, 'employee_code' => 'POLICY-OTHER', 'first_name' => 'Other', 'last_name' => 'Employee', 'hire_date' => today(), 'employment_status' => 'Active', 'salary_currency' => 'USD', 'is_active' => true]);
});

it('enforces own-record and cross-company employee policy boundaries', function () {
    $this->user->givePermissionTo(['employee.view-own', 'employee.edit-own', 'employee.view-sensitive']);

    expect(Gate::forUser($this->user)->allows('view', $this->employee))->toBeTrue()
        ->and(Gate::forUser($this->user)->allows('update', $this->employee))->toBeTrue()
        ->and(Gate::forUser($this->user)->allows('view', $this->otherEmployee))->toBeFalse();
});

it('applies company scope to every high-risk domain policy', function () {
    $this->user->givePermissionTo(['employee.view-sensitive', 'contract.view', 'contract.edit', 'contract.approve', 'contract.terminate', 'payroll.view', 'payroll.approve', 'payroll.process', 'attendance.report', 'leave.approve', 'expense.view', 'expense.approve-manager', 'expense.approve-accounting', 'expense.pay', 'asset.view', 'asset.manage', 'recruitment.view', 'recruitment.manage']);
    $document = EmployeeDocument::query()->create(['employee_id' => $this->otherEmployee->id, 'document_type' => 'identity', 'original_name' => 'x.pdf', 'file_path' => 'private/x.pdf']);
    $contract = EmploymentContract::query()->create(['company_id' => $this->otherCompany->id, 'employee_id' => $this->otherEmployee->id, 'contract_number' => 'POLICY-CONTRACT', 'type' => 'udc', 'status' => 'draft', 'start_date' => today(), 'salary_amount' => 500, 'salary_currency' => 'USD', 'pay_type' => 'monthly', 'work_hours_per_day' => 8, 'work_days_per_week' => 5]);
    $period = PayrollPeriod::query()->create(['company_id' => $this->otherCompany->id, 'name' => 'Other payroll', 'start_date' => today()->startOfMonth(), 'end_date' => today()->endOfMonth(), 'payment_date' => today()->endOfMonth(), 'status' => 'draft']);
    $attendance = Attendance::query()->create(['employee_id' => $this->otherEmployee->id, 'branch_id' => $this->otherBranch->id, 'work_date' => today(), 'status' => 'present']);
    $leave = LeaveRequest::query()->create(['employee_id' => $this->otherEmployee->id, 'leave_type_id' => LeaveType::query()->create(['company_id' => $this->otherCompany->id, 'name' => 'Policy leave', 'code' => 'POL', 'days_per_year' => 1, 'is_active' => true])->id, 'start_date' => today(), 'end_date' => today(), 'total_days' => 1, 'status' => 'pending']);
    $expense = ExpenseClaim::query()->create(['company_id' => $this->otherCompany->id, 'employee_id' => $this->otherEmployee->id, 'expense_date' => today(), 'category' => 'Other', 'amount' => 1, 'currency' => 'USD', 'business_purpose' => 'Other company expense purpose.', 'status' => 'pending_manager']);
    $asset = Asset::query()->create(['company_id' => $this->otherCompany->id, 'asset_code' => 'OTHER-ASSET', 'name' => 'Other asset', 'category' => 'Device', 'currency' => 'USD', 'condition' => 'good', 'status' => 'available']);
    $vacancy = JobVacancy::query()->create(['company_id' => $this->otherCompany->id, 'title' => 'Other vacancy', 'description' => 'Other company confidential vacancy.', 'openings' => 1, 'open_date' => today(), 'status' => 'open', 'created_by' => $this->user->id]);
    $applicant = JobApplicant::query()->create(['job_vacancy_id' => $vacancy->id, 'full_name' => 'Other applicant', 'email' => 'other.policy@example.test', 'phone' => '1', 'status' => 'applied', 'applied_at' => now()]);

    foreach ([[$document, 'view'], [$contract, 'view'], [$contract, 'approve'], [$period, 'approve'], [$period, 'pay'], [$attendance, 'view'], [$leave, 'approve'], [$expense, 'managerApprove'], [$expense, 'accountingApprove'], [$expense, 'pay'], [$asset, 'manage'], [$vacancy, 'view'], [$applicant, 'manage']] as [$model, $ability]) {
        expect(Gate::forUser($this->user)->allows($ability, $model))->toBeFalse("{$ability} should deny cross-company access to ".get_class($model));
    }
});

it('does not let the global super-admin permission shortcut bypass tenant policies', function () {
    $this->user->assignRole('Super Admin');

    expect(Gate::forUser($this->user)->allows('employee.view'))->toBeTrue()
        ->and(Gate::forUser($this->user)->allows('view', $this->otherEmployee))->toBeFalse();
});
