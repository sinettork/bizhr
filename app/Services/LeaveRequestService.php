<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class LeaveRequestService
{
    public function submit(Employee $employee, LeaveType $leaveType, string $startDate, string $endDate, ?string $reason = null): LeaveRequest
    {
        $start = CarbonImmutable::parse($startDate)->startOfDay();
        $end = CarbonImmutable::parse($endDate)->startOfDay();

        if ($end->isBefore($start)) {
            throw ValidationException::withMessages(['end_date' => 'The end date must be on or after the start date.']);
        }

        $days = $this->workingDays($start, $end);
        if ($days <= 0) {
            throw ValidationException::withMessages(['start_date' => 'The selected range does not contain a working day.']);
        }

        $overlaps = LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['pending', 'manager_approved', 'approved'])
            ->whereDate('start_date', '<=', $end)
            ->whereDate('end_date', '>=', $start)
            ->exists();

        if ($overlaps) {
            throw ValidationException::withMessages(['start_date' => 'This leave request overlaps an existing request.']);
        }

        $balance = LeaveBalance::firstOrCreate(
            ['employee_id' => $employee->id, 'leave_type_id' => $leaveType->id, 'year' => $start->year],
            ['earned_days' => $leaveType->days_per_year, 'remaining_days' => $leaveType->days_per_year],
        );

        if ((float) $balance->remaining_days < $days) {
            throw ValidationException::withMessages(['start_date' => 'The employee does not have enough leave balance.']);
        }

        return LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => $start,
            'end_date' => $end,
            'total_days' => $days,
            'reason' => $reason,
            'status' => 'pending',
        ]);
    }

    private function workingDays(CarbonImmutable $start, CarbonImmutable $end): int
    {
        $days = 0;
        for ($date = $start; $date->lte($end); $date = $date->addDay()) {
            if (! $date->isWeekend()) {
                $days++;
            }
        }

        return $days;
    }
}
