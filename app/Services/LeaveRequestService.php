<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LeaveRequestService
{
    public function __construct(private readonly LeaveDayCalculator $dayCalculator) {}

    public function submit(Employee $employee, LeaveType $leaveType, string $startDate, string $endDate, ?string $reason = null): LeaveRequest
    {
        $start = CarbonImmutable::parse($startDate)->startOfDay();
        $end = CarbonImmutable::parse($endDate)->startOfDay();

        if ($end->isBefore($start)) {
            throw ValidationException::withMessages(['end_date' => 'ថ្ងៃបញ្ចប់ត្រូវតែនៅក្រោយ ឬស្មើថ្ងៃចាប់ផ្តើម។']);
        }

        $daysByYear = $this->dayCalculator->daysByYear($employee, $start, $end);
        $totalDays = array_sum($daysByYear);

        if ($totalDays <= 0) {
            throw ValidationException::withMessages(['start_date' => 'ចន្លោះថ្ងៃដែលបានជ្រើសរើសមិនមានថ្ងៃធ្វើការទេ។']);
        }

        return DB::transaction(function () use ($employee, $leaveType, $start, $end, $daysByYear, $totalDays, $reason): LeaveRequest {
            $overlaps = LeaveRequest::query()
                ->where('employee_id', $employee->id)
                ->whereIn('status', ['pending', 'manager_approved', 'approved'])
                ->whereDate('start_date', '<=', $end)
                ->whereDate('end_date', '>=', $start)
                ->lockForUpdate()
                ->exists();

            if ($overlaps) {
                throw ValidationException::withMessages(['start_date' => 'សំណើនេះជាន់គ្នាជាមួយសំណើឈប់សម្រាកដែលមានស្រាប់។']);
            }

            foreach ($daysByYear as $year => $days) {
                $balance = LeaveBalance::query()->firstOrCreate(
                    ['employee_id' => $employee->id, 'leave_type_id' => $leaveType->id, 'year' => $year],
                    ['earned_days' => $leaveType->days_per_year, 'remaining_days' => $leaveType->days_per_year],
                );

                $balance = LeaveBalance::query()->whereKey($balance->id)->lockForUpdate()->firstOrFail();

                if ((float) $balance->remaining_days < $days) {
                    throw ValidationException::withMessages([
                        'start_date' => "សមតុល្យឈប់សម្រាកឆ្នាំ {$year} មិនគ្រប់គ្រាន់ទេ។",
                    ]);
                }
            }

            return LeaveRequest::query()->create([
                'employee_id' => $employee->id,
                'leave_type_id' => $leaveType->id,
                'start_date' => $start,
                'end_date' => $end,
                'total_days' => $totalDays,
                'reason' => $reason,
                'status' => 'pending',
            ]);
        });
    }
}
