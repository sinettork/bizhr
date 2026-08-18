<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PayrollItem;
use App\Models\PayrollPayment;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Services\PayrollCalculatorService;
use App\Services\PayrollWorkflowService;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

function payrollFixture(array $periodOverrides = [], array $itemOverrides = []): array
{
    $company = Company::query()->create([
        'name' => 'BizHR Test Company',
        'currency' => 'USD',
        'timezone' => 'Asia/Phnom_Penh',
        'date_format' => 'd/m/Y',
    ]);

    $branch = Branch::query()->create([
        'company_id' => $company->id,
        'name' => 'Head Office',
        'code' => 'HQ-'.$company->id,
        'is_active' => true,
    ]);

    $department = Department::query()->create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'name' => 'Finance',
        'code' => 'FIN-'.$company->id,
        'is_active' => true,
    ]);

    $employee = Employee::query()->create([
        'company_id' => $company->id,
        'branch_id' => $branch->id,
        'department_id' => $department->id,
        'employee_code' => 'PAY-'.$company->id,
        'first_name' => 'Dara',
        'last_name' => 'Sok',
        'hire_date' => '2026-01-01',
        'employment_status' => 'Active',
        'base_salary' => 1000,
        'salary_currency' => 'USD',
        'is_active' => true,
    ]);

    $processor = User::factory()->create();
    $approver = User::factory()->create();

    $period = PayrollPeriod::query()->create(array_merge([
        'company_id' => $company->id,
        'name' => 'January 2026',
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-31',
        'payment_date' => '2026-02-01',
        'status' => 'awaiting_approval',
        'processed_by' => $processor->id,
        'processed_at' => now(),
    ], $periodOverrides));

    $item = PayrollItem::query()->create(array_merge([
        'payroll_period_id' => $period->id,
        'employee_id' => $employee->id,
        'currency' => 'USD',
        'base_salary' => 1000,
        'gross_salary' => 1000,
        'net_salary' => 950,
        'payment_status' => 'unpaid',
        'exception_count' => 0,
    ], $itemOverrides));

    return compact('company', 'branch', 'department', 'employee', 'processor', 'approver', 'period', 'item');
}

it('approves an exception-free payroll exactly once', function () {
    $fixture = payrollFixture();

    $approved = app(PayrollWorkflowService::class)->approve(
        $fixture['period'],
        $fixture['approver'],
    );

    expect($approved->status)->toBe('approved')
        ->and($approved->approved_by)->toBe($fixture['approver']->id)
        ->and($approved->approved_at)->not->toBeNull();

    expect(fn () => app(PayrollWorkflowService::class)->approve(
        $approved,
        $fixture['approver'],
    ))->toThrow(ValidationException::class);
});

it('blocks approval when calculation exceptions remain', function () {
    $fixture = payrollFixture(itemOverrides: ['exception_count' => 2]);

    expect(fn () => app(PayrollWorkflowService::class)->approve(
        $fixture['period'],
        $fixture['approver'],
    ))->toThrow(ValidationException::class);

    expect($fixture['period']->fresh()->status)->toBe('awaiting_approval')
        ->and($fixture['period']->fresh()->approved_by)->toBeNull();
});

it('prevents a processor from approving their own payroll', function () {
    $fixture = payrollFixture();

    expect(fn () => app(PayrollWorkflowService::class)->approve(
        $fixture['period'],
        $fixture['processor'],
    ))->toThrow(ValidationException::class);

    expect($fixture['period']->fresh()->status)->toBe('awaiting_approval');
});

it('records one immutable payment and marks every item paid', function () {
    $fixture = payrollFixture(periodOverrides: ['status' => 'approved']);

    $payment = app(PayrollWorkflowService::class)->recordPayment(
        $fixture['period'],
        $fixture['approver'],
        [
            'payment_method' => 'bank_transfer',
            'reference_number' => 'PAY-2026-0001',
            'paid_at' => '2026-02-01 09:00:00',
            'notes' => 'Verified bank transfer',
        ],
    );

    expect($payment)->toBeInstanceOf(PayrollPayment::class)
        ->and((float) $payment->total_usd)->toBe(950.0)
        ->and((float) $payment->total_khr)->toBe(0.0)
        ->and($payment->checksum)->toHaveLength(64)
        ->and($fixture['period']->fresh()->status)->toBe('paid')
        ->and($fixture['item']->fresh()->payment_status)->toBe('paid');

    expect(fn () => app(PayrollWorkflowService::class)->recordPayment(
        $fixture['period']->fresh(),
        $fixture['approver'],
        [
            'payment_method' => 'cash',
            'reference_number' => 'PAY-2026-0002',
            'paid_at' => '2026-02-01 10:00:00',
            'notes' => null,
        ],
    ))->toThrow(ValidationException::class);

    expect(PayrollPayment::query()->where('payroll_period_id', $fixture['period']->id)->count())
        ->toBe(1);
});

it('rolls back item and period updates when payment creation fails', function () {
    $first = payrollFixture(periodOverrides: [
        'name' => 'January 2026 A',
        'start_date' => '2026-01-01',
        'end_date' => '2026-01-15',
        'status' => 'approved',
    ]);

    app(PayrollWorkflowService::class)->recordPayment(
        $first['period'],
        $first['approver'],
        [
            'payment_method' => 'bank_transfer',
            'reference_number' => 'DUPLICATE-REFERENCE',
            'paid_at' => '2026-01-16 09:00:00',
            'notes' => null,
        ],
    );

    $second = payrollFixture(periodOverrides: [
        'name' => 'January 2026 B',
        'start_date' => '2026-01-16',
        'end_date' => '2026-01-31',
        'status' => 'approved',
    ]);

    expect(fn () => app(PayrollWorkflowService::class)->recordPayment(
        $second['period'],
        $second['approver'],
        [
            'payment_method' => 'bank_transfer',
            'reference_number' => 'DUPLICATE-REFERENCE',
            'paid_at' => '2026-02-01 09:00:00',
            'notes' => null,
        ],
    ))->toThrow(QueryException::class);

    expect($second['period']->fresh()->status)->toBe('approved')
        ->and($second['item']->fresh()->payment_status)->toBe('unpaid')
        ->and($second['period']->payment()->exists())->toBeFalse();
});

it('does not recalculate an approved paid or closed payroll', function (string $status) {
    $fixture = payrollFixture(periodOverrides: ['status' => $status]);

    expect(fn () => app(PayrollCalculatorService::class)->generate($fixture['period']))
        ->toThrow(RuntimeException::class);
})->with(['approved', 'paid', 'closed']);

it('rejects a stale competing generation request after the first lock owner finishes', function () {
    $fixture = payrollFixture(periodOverrides: [
        'status' => 'draft',
        'processed_by' => null,
        'processed_at' => null,
    ]);
    $staleRequestCopy = PayrollPeriod::query()->findOrFail($fixture['period']->id);

    $count = app(PayrollCalculatorService::class)->generate(
        $fixture['period'],
        $fixture['processor'],
    );

    expect($count)->toBe(1)
        ->and($fixture['period']->fresh()->status)->toBe('awaiting_approval')
        ->and($fixture['period']->fresh()->processed_by)->toBe($fixture['processor']->id);

    expect(fn () => app(PayrollCalculatorService::class)->generate(
        $staleRequestCopy,
        $fixture['processor'],
    ))->toThrow(RuntimeException::class);

    expect(PayrollItem::query()
        ->where('payroll_period_id', $fixture['period']->id)
        ->where('employee_id', $fixture['employee']->id)
        ->count())->toBe(1);
});
