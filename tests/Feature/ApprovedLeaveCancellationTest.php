<?php

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Notification;
use App\Models\PayrollPeriod;
use App\Models\User;
use App\Services\LeaveApprovalService;
use App\Services\LeaveDayCalculator;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;

beforeEach(function (): void {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $this->owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
    $this->employeeUser = User::query()->where('email', 'piseth@bizhr.local')->firstOrFail();
    $this->employee = Employee::query()->where('user_id', $this->employeeUser->id)->firstOrFail();
    $this->leaveType = LeaveType::query()->where('company_id', $this->employee->company_id)->firstOrFail();

    $start = CarbonImmutable::parse(today()->addWeek()->startOfWeek());
    if ($start->lte(CarbonImmutable::today())) {
        $start = $start->addWeek();
    }
    $this->start = $start;
    $this->end = $start;
    $this->totalDays = array_sum(app(LeaveDayCalculator::class)->daysByYear($this->employee, $this->start, $this->end));

    $this->balance = LeaveBalance::query()->updateOrCreate([
        'employee_id' => $this->employee->id,
        'leave_type_id' => $this->leaveType->id,
        'year' => $this->start->year,
    ], [
        'opening_balance' => 10,
        'earned_days' => 0,
        'used_days' => 0,
        'adjustment_days' => 0,
        'remaining_days' => 10,
    ]);

    $this->leaveRequest = LeaveRequest::query()->create([
        'employee_id' => $this->employee->id,
        'leave_type_id' => $this->leaveType->id,
        'start_date' => $this->start,
        'end_date' => $this->end,
        'total_days' => $this->totalDays,
        'reason' => 'Approved future leave used for cancellation coverage.',
        'status' => 'manager_approved',
    ]);

    app(LeaveApprovalService::class)->approve($this->leaveRequest, $this->owner, 'Approved for test coverage.');
});

it('lets HR cancel future approved leave and restores the deducted balance', function (): void {
    expect($this->leaveRequest->fresh()->status)->toBe('approved')
        ->and((float) $this->balance->fresh()->used_days)->toBe((float) $this->totalDays)
        ->and((float) $this->balance->fresh()->remaining_days)->toBe(10.0 - $this->totalDays);

    $this->actingAs($this->owner)
        ->post(route('leave.requests.reject', $this->leaveRequest), [
            'note' => 'Employee plans changed before the approved leave started.',
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $cancelled = $this->leaveRequest->fresh();
    expect($cancelled->status)->toBe('cancelled')
        ->and($cancelled->cancelled_by)->toBe($this->owner->id)
        ->and($cancelled->cancelled_at)->not->toBeNull()
        ->and($cancelled->cancellation_reason)->toBe('Employee plans changed before the approved leave started.')
        ->and((float) $this->balance->fresh()->used_days)->toBe(0.0)
        ->and((float) $this->balance->fresh()->remaining_days)->toBe(10.0)
        ->and(Notification::query()->where('user_id', $this->employeeUser->id)->where('type', 'leave_cancelled')->exists())->toBeTrue();
});

it('blocks approved leave cancellation when an overlapping payroll period is locked', function (): void {
    PayrollPeriod::query()->create([
        'company_id' => $this->employee->company_id,
        'name' => 'Locked payroll covering approved leave',
        'start_date' => $this->start->startOfMonth(),
        'end_date' => $this->start->endOfMonth(),
        'payment_date' => $this->start->endOfMonth()->addDays(5),
        'status' => 'approved',
        'tax_exchange_rate_khr' => 4000,
    ]);

    $this->actingAs($this->owner)
        ->post(route('leave.requests.reject', $this->leaveRequest), [
            'note' => 'Attempting cancellation after payroll lock.',
        ])
        ->assertSessionHasErrors('status');

    expect($this->leaveRequest->fresh()->status)->toBe('approved')
        ->and((float) $this->balance->fresh()->used_days)->toBe((float) $this->totalDays)
        ->and((float) $this->balance->fresh()->remaining_days)->toBe(10.0 - $this->totalDays);
});
