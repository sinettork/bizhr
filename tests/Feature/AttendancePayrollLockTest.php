<?php

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;

beforeEach(function (): void {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $this->employeeUser = User::query()->where('email', 'piseth@bizhr.local')->firstOrFail();
    $this->employee = Employee::query()->where('user_id', $this->employeeUser->id)->firstOrFail();
    $this->attendance = Attendance::query()
        ->where('employee_id', $this->employee->id)
        ->whereNotNull('check_in_at')
        ->latest('work_date')
        ->firstOrFail();
    $this->reviewer = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
});

function lockPayrollForAttendance(Attendance $attendance, int $companyId): PayrollPeriod
{
    return PayrollPeriod::query()->create([
        'company_id' => $companyId,
        'name' => 'Locked attendance payroll',
        'start_date' => $attendance->work_date,
        'end_date' => $attendance->work_date,
        'payment_date' => $attendance->work_date,
        'status' => 'approved',
        'tax_exchange_rate_khr' => 4000,
    ]);
}

it('blocks a new attendance correction when payroll is already locked', function (): void {
    lockPayrollForAttendance($this->attendance, (int) $this->employee->company_id);

    $this->actingAs($this->employeeUser)
        ->post(route('attendance.corrections.store'), [
            'attendance_id' => $this->attendance->id,
            'requested_check_in' => $this->attendance->check_in_at->copy()->addMinutes(5)->format('Y-m-d\TH:i'),
            'reason' => 'The recorded time needs a verified correction.',
        ])
        ->assertStatus(423);

    expect(AttendanceCorrection::query()->where('attendance_id', $this->attendance->id)->exists())->toBeFalse();
});

it('blocks reopening a rejected correction when payroll became locked', function (): void {
    $correction = AttendanceCorrection::query()->create([
        'attendance_id' => $this->attendance->id,
        'employee_id' => $this->employee->id,
        'requested_check_in' => $this->attendance->check_in_at->copy()->addMinutes(5),
        'reason' => 'The attendance device recorded the wrong minute.',
        'status' => 'rejected',
        'reviewed_by' => $this->reviewer->id,
        'reviewed_at' => now(),
        'review_note' => 'Please confirm with your supervisor.',
    ]);
    lockPayrollForAttendance($this->attendance, (int) $this->employee->company_id);

    $this->actingAs($this->employeeUser)
        ->post(route('attendance.corrections.reopen', $correction), [
            'reason' => 'The supervisor has now confirmed the correct time.',
        ])
        ->assertStatus(423);

    expect($correction->fresh()->status)->toBe('rejected');
});

it('blocks approval when payroll locks the attendance before review finishes', function (): void {
    $correction = AttendanceCorrection::query()->create([
        'attendance_id' => $this->attendance->id,
        'employee_id' => $this->employee->id,
        'requested_check_in' => $this->attendance->check_in_at->copy()->addMinutes(5),
        'reason' => 'The attendance device recorded the wrong minute.',
        'status' => 'pending',
    ]);
    lockPayrollForAttendance($this->attendance, (int) $this->employee->company_id);

    $before = $this->attendance->check_in_at->copy();

    $this->actingAs($this->reviewer)
        ->post(route('attendance.corrections.approve', $correction), ['note' => 'Verified by HR.'])
        ->assertStatus(423);

    expect($correction->fresh()->status)->toBe('pending')
        ->and($this->attendance->fresh()->check_in_at->equalTo($before))->toBeTrue();
});

it('blocks overtime review when finalized payroll already covers the attendance date', function (): void {
    $this->attendance->forceFill([
        'overtime_minutes' => 120,
        'overtime_approved' => false,
        'overtime_review_status' => 'pending',
        'overtime_review_note' => null,
        'overtime_approved_by' => null,
        'overtime_approved_at' => null,
    ])->save();
    lockPayrollForAttendance($this->attendance, (int) $this->employee->company_id);

    $this->actingAs($this->reviewer)
        ->post(route('payroll.overtime.review', [$this->attendance, 'approve']))
        ->assertStatus(423);

    $fresh = $this->attendance->fresh();
    expect($fresh->overtime_review_status)->toBe('pending')
        ->and((bool) $fresh->overtime_approved)->toBeFalse()
        ->and($fresh->overtime_approved_at)->toBeNull();
});
