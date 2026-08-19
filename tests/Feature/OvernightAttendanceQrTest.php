<?php

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\User;
use App\Models\WorkShift;
use App\Services\AttendanceQrService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Support\Carbon;

beforeEach(function (): void {
    Carbon::setTestNow('2026-08-20 00:30:00');
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('checks out the previous work date after midnight for a scheduled night shift', function (): void {
    $user = User::query()->where('email', 'piseth@bizhr.local')->firstOrFail();
    $employee = Employee::query()->where('user_id', $user->id)->firstOrFail();
    $branch = Branch::query()->findOrFail($employee->branch_id);
    $branch->update([
        'attendance_qr_enabled' => true,
        'latitude' => '11.5564000',
        'longitude' => '104.9282000',
        'attendance_radius' => 200,
    ]);
    $branch->regenerateAttendanceQrToken();

    $shift = WorkShift::query()->create([
        'company_id' => $employee->company_id,
        'name' => 'Audit Night Shift',
        'code' => 'AUDIT-NIGHT',
        'start_time' => '22:00:00',
        'end_time' => '06:00:00',
        'break_minutes' => 0,
        'late_grace_minutes' => 0,
        'early_leave_grace_minutes' => 0,
        'is_night_shift' => true,
        'is_active' => true,
    ]);

    EmployeeSchedule::query()->updateOrCreate(
        ['employee_id' => $employee->id, 'work_date' => today()->subDay()->toDateString()],
        ['branch_id' => $branch->id, 'work_shift_id' => $shift->id, 'is_rest_day' => false],
    );

    $attendance = Attendance::query()->updateOrCreate(
        ['employee_id' => $employee->id, 'work_date' => today()->subDay()->toDateString()],
        [
            'branch_id' => $branch->id,
            'check_in_at' => today()->subDay()->setTime(22, 0),
            'check_out_at' => null,
            'check_in_method' => 'qr',
        ],
    );

    $payload = json_encode([
        'type' => 'bizhr_attendance',
        'branch_id' => $branch->id,
        'token' => $branch->attendance_qr_token,
    ], JSON_THROW_ON_ERROR);

    $result = app(AttendanceQrService::class)->process(
        $employee,
        $payload,
        11.5564,
        104.9282,
        '127.0.0.1',
        'Pest',
    );

    expect($result['action'])->toBe('check_out')
        ->and($attendance->fresh()->check_out_at)->not->toBeNull()
        ->and(Attendance::query()->where('employee_id', $employee->id)->whereDate('work_date', today())->exists())->toBeFalse();
});
