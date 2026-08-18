<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmploymentContract;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);

    $this->primaryCompany = Company::query()->create(['name' => 'Primary company']);
    $this->otherCompany = Company::query()->create(['name' => 'Other company']);
    $this->owner = User::factory()->create();
    $this->owner->assignRole('Owner');
    $this->primaryBranch = Branch::query()->create([
        'company_id' => $this->primaryCompany->id,
        'name' => 'Primary branch',
        'code' => 'PRIMARY',
        'is_active' => true,
    ]);
    $this->otherBranch = Branch::query()->create([
        'company_id' => $this->otherCompany->id,
        'name' => 'Other branch',
        'code' => 'OTHER',
        'is_active' => true,
    ]);
    $this->primaryDepartment = Department::query()->create([
        'company_id' => $this->primaryCompany->id,
        'branch_id' => $this->primaryBranch->id,
        'name' => 'Primary department',
        'code' => 'PRIMARY-DEPT',
        'is_active' => true,
    ]);
    $this->otherDepartment = Department::query()->create([
        'company_id' => $this->otherCompany->id,
        'branch_id' => $this->otherBranch->id,
        'name' => 'Other department',
        'code' => 'OTHER-DEPT',
        'is_active' => true,
    ]);
});

it('does not expose branches from another company in the directory', function () {
    Branch::query()->create([
        'company_id' => $this->otherCompany->id,
        'name' => 'Hidden tenant branch',
        'code' => 'OTHER-HIDDEN',
        'is_active' => true,
    ]);

    $this->actingAs($this->owner)
        ->get(route('branches.index'))
        ->assertOk()
        ->assertSee('Primary branch')
        ->assertDontSee('Hidden tenant branch');
});

it('rejects contract mutations belonging to another company', function (string $routeName, string $method) {
    $employee = Employee::query()->create([
        'company_id' => $this->otherCompany->id,
        'branch_id' => $this->otherBranch->id,
        'department_id' => $this->otherDepartment->id,
        'employee_code' => 'OTHER-001',
        'first_name' => 'Other',
        'last_name' => 'Employee',
        'hire_date' => today(),
        'employment_status' => 'Active',
        'salary_currency' => 'USD',
        'is_active' => true,
    ]);
    $contract = EmploymentContract::query()->create([
        'company_id' => $this->otherCompany->id,
        'employee_id' => $employee->id,
        'contract_number' => 'OTHER-CONTRACT-001',
        'type' => 'udc',
        'status' => 'pending_approval',
        'start_date' => today(),
        'salary_amount' => 500,
        'salary_currency' => 'USD',
        'pay_type' => 'monthly',
        'work_hours_per_day' => 8,
        'work_days_per_week' => 5,
    ]);

    $response = match ($method) {
        'post' => $this->actingAs($this->owner)->post(route($routeName, $contract), [
            'termination_date' => today()->toDateString(),
            'termination_reason' => 'Tenant isolation verification reason.',
        ]),
        default => $this->actingAs($this->owner)->get(route($routeName, $contract)),
    };

    $response->assertNotFound();
})->with([
    'approve' => ['contracts.approve', 'post'],
    'terminate' => ['contracts.terminate', 'post'],
    'renew' => ['contracts.renew', 'get'],
]);

it('allows an employee to download only their own cross-company contract document', function () {
    Storage::fake('local');
    Storage::disk('local')->put('private/employment-contracts/own.pdf', 'test');

    $user = User::factory()->create();
    $user->givePermissionTo('contract.view-own');
    $employee = Employee::query()->create([
        'company_id' => $this->otherCompany->id,
        'branch_id' => $this->otherBranch->id,
        'department_id' => $this->otherDepartment->id,
        'user_id' => $user->id,
        'employee_code' => 'OWN-001',
        'first_name' => 'Own',
        'last_name' => 'Employee',
        'hire_date' => today(),
        'employment_status' => 'Active',
        'salary_currency' => 'USD',
        'is_active' => true,
    ]);
    $contract = EmploymentContract::query()->create([
        'company_id' => $this->otherCompany->id,
        'employee_id' => $employee->id,
        'contract_number' => 'OWN-CONTRACT-001',
        'type' => 'udc',
        'status' => 'active',
        'start_date' => today(),
        'salary_amount' => 500,
        'salary_currency' => 'USD',
        'pay_type' => 'monthly',
        'work_hours_per_day' => 8,
        'work_days_per_week' => 5,
        'document_path' => 'private/employment-contracts/own.pdf',
        'original_name' => 'own.pdf',
    ]);

    $this->actingAs($user)
        ->get(route('contracts.download', $contract))
        ->assertOk();
});

it('rejects verification of a task belonging to another company', function () {
    $employee = Employee::query()->create([
        'company_id' => $this->otherCompany->id,
        'branch_id' => $this->otherBranch->id,
        'department_id' => $this->otherDepartment->id,
        'employee_code' => 'OTHER-TASK-001',
        'first_name' => 'Other',
        'last_name' => 'Assignee',
        'hire_date' => today(),
        'employment_status' => 'Active',
        'salary_currency' => 'USD',
        'is_active' => true,
    ]);
    $task = Task::query()->create([
        'company_id' => $this->otherCompany->id,
        'assigned_to' => $employee->id,
        'assigned_by' => $this->owner->id,
        'title' => 'Other company task',
        'priority' => 'medium',
        'status' => 'waiting_verification',
        'progress' => 100,
        'start_date' => today(),
        'due_date' => today(),
    ]);

    $this->actingAs($this->owner)
        ->post(route('tasks.verify', [$task, 'approve']))
        ->assertNotFound();
});
