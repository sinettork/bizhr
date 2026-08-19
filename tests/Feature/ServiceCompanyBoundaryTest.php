<?php

use App\Models\Asset;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmploymentContract;
use App\Models\PayrollPeriod;
use App\Models\Task;
use App\Models\User;
use App\Services\AssetWorkflowService;
use App\Services\EmploymentContractService;
use App\Services\PayrollCalculatorService;
use App\Services\TaskWorkflowService;
use DomainException;

function companyUser(Company $company): User
{
    $user = User::factory()->create();
    Employee::factory()->create([
        'company_id' => $company->id,
        'user_id' => $user->id,
    ]);

    return $user->fresh();
}

it('rejects payroll processing by a user from another company', function () {
    $actorCompany = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $actor = companyUser($actorCompany);

    $period = PayrollPeriod::query()->create([
        'company_id' => $otherCompany->id,
        'name' => 'Other company payroll',
        'start_date' => '2026-08-01',
        'end_date' => '2026-08-31',
        'payment_date' => '2026-09-05',
        'status' => 'draft',
        'tax_exchange_rate_khr' => 4000,
    ]);

    expect(fn () => app(PayrollCalculatorService::class)->generate($period, $actor))
        ->toThrow(DomainException::class, 'processor does not belong');
});

it('rejects task workflow changes by a user from another company', function () {
    $actorCompany = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $actor = companyUser($actorCompany);
    $assignee = Employee::factory()->create(['company_id' => $otherCompany->id]);

    $task = Task::query()->create([
        'company_id' => $otherCompany->id,
        'assigned_by' => $actor->id,
        'assigned_to' => $assignee->id,
        'title' => 'Cross-company task',
        'priority' => 'normal',
        'start_date' => today(),
        'due_date' => today()->addWeek(),
        'status' => 'not_started',
        'progress' => 0,
    ]);

    expect(fn () => app(TaskWorkflowService::class)->cancel($task, $actor, 'Security boundary test'))
        ->toThrow(DomainException::class, 'does not belong');
});

it('rejects contract workflow changes by a user from another company', function () {
    $actorCompany = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $actor = companyUser($actorCompany);
    $employee = Employee::factory()->create(['company_id' => $otherCompany->id]);

    $contract = EmploymentContract::query()->create([
        'company_id' => $otherCompany->id,
        'employee_id' => $employee->id,
        'contract_number' => 'SEC-OTHER-001',
        'type' => 'udc',
        'status' => 'draft',
        'start_date' => today(),
        'salary_amount' => 500,
        'salary_currency' => 'USD',
    ]);

    expect(fn () => app(EmploymentContractService::class)->submit($contract, $actor))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

it('rejects asset assignment by a user from another company', function () {
    $actorCompany = Company::factory()->create();
    $otherCompany = Company::factory()->create();
    $actor = companyUser($actorCompany);
    $employee = Employee::factory()->create(['company_id' => $otherCompany->id]);

    $asset = Asset::query()->create([
        'company_id' => $otherCompany->id,
        'asset_code' => 'SEC-ASSET-001',
        'name' => 'Other company laptop',
        'category' => 'Computer',
        'currency' => 'USD',
        'condition' => 'good',
        'status' => 'available',
    ]);

    expect(fn () => app(AssetWorkflowService::class)->assign($asset, $employee, $actor, 'good', null))
        ->toThrow(DomainException::class, 'does not belong');
});
