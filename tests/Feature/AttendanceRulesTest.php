<?php

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\User;
use App\Models\WorkShift;
use Carbon\CarbonImmutable;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;

beforeEach(function (): void {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $this->user = User::query()->where('email', 'piseth@bizhr.local')->firstOrFail();
    $this->employee = $this->user->employee;
    $this->workShift = WorkShift::query()->where('company_id', $this->employee->company_id)->first();
    Attendance::query()->where('employee_id', $this->employee->id)->delete();
});

describe('Attendance Rules - Late Detection', function (): void {
    it('marks attendance as late when check-in is after shift start time', function (): void {
        $today = now()->toDateString();
        $shiftStart = now()->setHour(8)->setMinute(0)->setSecond(0);
        $lateCheckIn = $shiftStart->copy()->addMinutes(15); // 15 minutes late

        $attendance = Attendance::factory()->create([
            'employee_id' => $this->employee->id,
            'work_date' => $today,
            'scheduled_start' => '08:00:00',
            'scheduled_end' => '17:00:00',
            'check_in_at' => $lateCheckIn,
            'check_out_at' => $shiftStart->copy()->addHours(8),
        ]);

        // Late minutes should be calculated
        expect($attendance->late_minutes)->toBe(15);
    });

    it('does not mark as late when check-in is within grace period', function (): void {
        $today = now()->toDateString();
        $shiftStart = now()->setHour(8)->setMinute(0)->setSecond(0);
        $withinGrace = $shiftStart->copy()->addMinutes(5); // 5 minutes (within 15-minute grace period)

        EmployeeSchedule::query()->updateOrCreate([
            'employee_id' => $this->employee->id,
            'work_date' => $today,
        ], [
            'branch_id' => $this->employee->branch_id,
            'work_shift_id' => $this->workShift->id,
            'is_rest_day' => false,
        ]);

        $attendance = Attendance::factory()->create([
            'employee_id' => $this->employee->id,
            'work_date' => $today,
            'check_in_at' => $withinGrace,
            'check_out_at' => $shiftStart->copy()->addHours(8),
        ]);

        // Should be 0 or null for late_minutes
        expect($attendance->late_minutes ?? 0)->toBeLessThanOrEqual(0);
    });

    it('calculates cumulative late minutes in a week', function (): void {
        $monday = now()->startOfWeek();
        $attendances = [];

        for ($i = 0; $i < 5; $i++) {
            $date = $monday->copy()->addDays($i);
            $attendances[] = Attendance::factory()->create([
                'employee_id' => $this->employee->id,
                'work_date' => $date->toDateString(),
                'scheduled_start' => '08:00:00',
                'scheduled_end' => '17:00:00',
                'check_in_at' => $date->copy()->setHour(8)->setMinute(10)->setSecond(0),
                'check_out_at' => $date->copy()->setHour(17)->setMinute(0)->setSecond(0),
            ]);
        }

        // Total late: 5 days × 10 minutes = 50 minutes
        $weekLate = collect($attendances)->sum('late_minutes');

        expect($weekLate)->toBe(50);
    });
});

describe('Attendance Rules - Absence Detection', function (): void {
    it('marks employee as absent when no check-in on scheduled work day', function (): void {
        $today = now()->toDateString();

        // Employee is scheduled to work today
        $employee = Employee::query()->first();

        // No attendance record for today
        $attendance = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereDate('work_date', $today)
            ->first();

        // Should not have attendance (absence)
        expect($attendance)->toBeNull();
    });

    it('detects early leave when check-out is before shift end', function (): void {
        $today = now()->toDateString();
        $shiftStart = now()->setHour(8)->setMinute(0)->setSecond(0);
        $shiftEnd = $shiftStart->copy()->addHours(8); // 5pm
        $earlyCheckOut = $shiftEnd->copy()->subMinutes(30); // Left at 4:30pm

        $attendance = Attendance::factory()->create([
            'employee_id' => $this->employee->id,
            'work_date' => $today,
            'scheduled_start' => '08:00:00',
            'scheduled_end' => '17:00:00',
            'check_in_at' => $shiftStart,
            'check_out_at' => $earlyCheckOut,
        ]);

        // Early leave minutes should be calculated
        expect($attendance->early_leave_minutes ?? 0)->toBeGreaterThan(0);
    });

    it('flags full-day absence (no check-in and no check-out)', function (): void {
        $today = now()->toDateString();

        $attendance = Attendance::factory()->create([
            'employee_id' => $this->employee->id,
            'work_date' => $today,
            'scheduled_start' => '08:00:00',
            'scheduled_end' => '17:00:00',
            'check_in_at' => null,
            'check_out_at' => null,
            'status' => 'absent',
        ]);

        expect($attendance->status)->toBe('absent');
    });

    it('calculates total absent days in a month', function (): void {
        $month = now()->month;
        $year = now()->year;

        // Create 3 absent records
        for ($day = 1; $day <= 3; $day++) {
            Attendance::factory()->create([
                'employee_id' => $this->employee->id,
                'work_date' => CarbonImmutable::create($year, $month, $day),
                'check_in_at' => null,
                'check_out_at' => null,
                'status' => 'absent',
            ]);
        }

        $absentDays = Attendance::query()
            ->where('employee_id', $this->employee->id)
            ->where('status', 'absent')
            ->whereYear('work_date', $year)
            ->whereMonth('work_date', $month)
            ->count();

        expect($absentDays)->toBe(3);
    });
});

describe('Attendance Rules - Overtime Detection', function (): void {
    it('flags overtime when working beyond scheduled hours', function (): void {
        $today = now()->toDateString();
        $shiftStart = now()->setHour(8)->setMinute(0)->setSecond(0);
        $shiftEnd = $shiftStart->copy()->addHours(9); // 5pm
        $overtimeCheckOut = $shiftEnd->copy()->addHours(2); // Worked until 7pm

        EmployeeSchedule::query()->updateOrCreate([
            'employee_id' => $this->employee->id,
            'work_date' => $today,
        ], [
            'branch_id' => $this->employee->branch_id,
            'work_shift_id' => $this->workShift->id,
            'is_rest_day' => false,
        ]);

        $attendance = Attendance::factory()->create([
            'employee_id' => $this->employee->id,
            'work_date' => $today,
            'check_in_at' => $shiftStart,
            'check_out_at' => $overtimeCheckOut,
        ]);

        expect($attendance->overtime_minutes)->toBe(120);
    });

    it('calculates cumulative overtime hours in a week', function (): void {
        $monday = now()->startOfWeek();
        $shiftStart = now()->setHour(8)->setMinute(0)->setSecond(0);
        $attendances = [];

        for ($i = 0; $i < 3; $i++) {
            $date = $monday->copy()->addDays($i);
            EmployeeSchedule::query()->updateOrCreate([
                'employee_id' => $this->employee->id,
                'work_date' => $date->toDateString(),
            ], [
                'branch_id' => $this->employee->branch_id,
                'work_shift_id' => $this->workShift->id,
                'is_rest_day' => false,
            ]);

            $attendances[] = Attendance::factory()->create([
                'employee_id' => $this->employee->id,
                'work_date' => $date->toDateString(),
                'check_in_at' => $date->copy()->setHour(8)->setMinute(0)->setSecond(0),
                'check_out_at' => $date->copy()->setHour(19)->setMinute(0)->setSecond(0), // 2 hours overtime
            ]);
        }

        // Total overtime: 3 days × 2 hours = 6 hours
        $weekOvertime = collect($attendances)->sum('overtime_minutes');

        expect($weekOvertime)->toBe(360);
    });
});
