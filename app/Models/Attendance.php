<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;
use App\Models\EmployeeSchedule;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'branch_id',
        'work_date',
        'scheduled_start',
        'scheduled_end',
        'check_in_at',
        'check_out_at',
        'check_in_method',
        'check_out_method',
        'check_in_location',
        'check_out_location',
        'late_minutes',
        'early_leave_minutes',
        'worked_minutes',
        'overtime_minutes',
        'status',
        'notes',
        'approved_by',
        'check_in_method',
        'check_out_method',

        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',

        'check_in_distance',
        'check_out_distance',

        'check_in_ip',
        'check_out_ip',

        'check_in_user_agent',
        'check_out_user_agent',

        'check_in_qr_token',
        'check_out_qr_token',
    ];

    protected $casts = [
        'work_date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
    ];
    protected function casts(): array
{
    return [
        'attendance_date' => 'date',

        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',

        'check_in_latitude' => 'decimal:7',
        'check_in_longitude' => 'decimal:7',
        'check_out_latitude' => 'decimal:7',
        'check_out_longitude' => 'decimal:7',

        'check_in_distance' => 'integer',
        'check_out_distance' => 'integer',
    ];
}

    protected static function booted(): void
    {
        static::saving(function (self $attendance): void {
            $attendance->recalculateMetrics();
        });
    }

    public function recalculateMetrics(): void
    {
        $this->late_minutes = $this->calculateLateness();
        $this->early_leave_minutes = $this->calculateEarlyLeave();
        $this->worked_minutes = $this->calculateWorkedMinutes();
        $this->overtime_minutes = max(
            0,
            $this->worked_minutes - $this->calculateScheduledWorkMinutes()
        );

        if (! $this->check_in_at && ! $this->check_out_at) {
            $this->status = 'absent';
            return;
        }

        if ($this->check_in_at && ! $this->check_out_at) {
            $this->status = $this->late_minutes > 0 ? 'late' : 'present';
            return;
        }

        if ($this->check_in_at && $this->check_out_at) {
            if ($this->late_minutes > 0) {
                $this->status = 'late';
                return;
            }

            $this->status = 'present';
            return;
        }
    }

    public function calculateScheduledWorkMinutes(): int
    {
        $scheduledStart = $this->scheduled_start;
        $scheduledEnd = $this->scheduled_end;
        $workShift = null;

        if (! $scheduledStart || ! $scheduledEnd) {
            $schedule = EmployeeSchedule::where('employee_id', $this->employee_id)
                ->whereDate('work_date', $this->work_date)
                ->with('workShift')
                ->first();

            $workShift = $schedule?->workShift;
            $scheduledStart = $scheduledStart ?: $workShift?->start_time;
            $scheduledEnd = $scheduledEnd ?: $workShift?->end_time;
        }

        if (! $scheduledStart || ! $scheduledEnd) {
            return 0;
        }

        $startDate = Carbon::parse($this->work_date->format('Y-m-d').' '.$scheduledStart);
        $endDate = Carbon::parse($this->work_date->format('Y-m-d').' '.$scheduledEnd);

        if ($endDate->lessThanOrEqualTo($startDate)) {
            $endDate->addDay();
        }

        $duration = $startDate->diffInMinutes($endDate);
        $breakMinutes = $workShift ? (int) $workShift->break_minutes : 0;

        return max(0, $duration - $breakMinutes);
    }

    /**
     * Get the employee who has this attendance.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the branch where attendance was recorded.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the user who approved this attendance.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the attendance corrections for this record.
     */
    public function corrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    /**
     * Check if employee is present.
     */
    public function isPresent(): bool
    {
        return in_array($this->status, ['present', 'late', 'half_day', 'remote_work', 'business_trip']);
    }

    /**
     * Check if employee is absent.
     */
    public function isAbsent(): bool
    {
        return $this->status === 'absent';
    }

    /**
     * Calculate lateness in minutes.
     */
    public function calculateLateness(): int
    {
        if (!$this->check_in_at) {
            return 0;
        }

        // Prefer scheduled_start on attendance; otherwise try to find schedule
        $scheduled = $this->scheduled_start;
        $grace = 0;

        if (!$scheduled) {
            $schedule = EmployeeSchedule::where('employee_id', $this->employee_id)
                ->whereDate('work_date', $this->work_date)
                ->with('workShift')
                ->first();
            if ($schedule && $schedule->workShift) {
                $scheduled = $schedule->workShift->start_time;
                $grace = (int) ($schedule->workShift->late_grace_minutes ?? 0);
            }
        }

        if ($scheduled) {
            $scheduledTime = Carbon::parse($scheduled);
            $checkInTime = Carbon::parse($this->check_in_at);

            if ($checkInTime->greaterThan($scheduledTime)) {
                $lateMinutes = $checkInTime->diffInMinutes($scheduledTime) - $grace;
                return max(0, $lateMinutes);
            }
        }

        return 0;
    }

    /**
     * Calculate early leave in minutes.
     */
    public function calculateEarlyLeave(): int
    {
        if (!$this->check_out_at) {
            return 0;
        }

        $scheduled = $this->scheduled_end;
        $grace = 0;

        if (!$scheduled) {
            $schedule = EmployeeSchedule::where('employee_id', $this->employee_id)
                ->whereDate('work_date', $this->work_date)
                ->with('workShift')
                ->first();
            if ($schedule && $schedule->workShift) {
                $scheduled = $schedule->workShift->end_time;
                $grace = (int) ($schedule->workShift->early_leave_grace_minutes ?? 0);
            }
        }

        if ($scheduled) {
            $scheduledTime = Carbon::parse($scheduled);
            $checkOutTime = Carbon::parse($this->check_out_at);

            if ($checkOutTime->lessThan($scheduledTime)) {
                $early = $scheduledTime->diffInMinutes($checkOutTime) - $grace;
                return max(0, $early);
            }
        }

        return 0;
    }

    /**
     * Calculate worked minutes.
     */
    public function calculateWorkedMinutes(): int
    {
        if (!$this->check_in_at || !$this->check_out_at) {
            return 0;
        }

        $minutes = Carbon::parse($this->check_in_at)->diffInMinutes(Carbon::parse($this->check_out_at));

        // Subtract scheduled break minutes if available via schedule->workShift
        $schedule = EmployeeSchedule::where('employee_id', $this->employee_id)
            ->whereDate('work_date', $this->work_date)
            ->with('workShift')
            ->first();

        if ($schedule && $schedule->workShift && $schedule->workShift->break_minutes) {
            $minutes = max(0, $minutes - (int) $schedule->workShift->break_minutes);
        }

        return $minutes;
    }

    /**
     * Check if employee checked in today.
     */
    public static function checkedInToday(int $employeeId): bool
    {
        return self::where('employee_id', $employeeId)
            ->whereDate('work_date', today())
            ->whereNotNull('check_in_at')
            ->exists();
    }

    /**
     * Get today's attendance for employee.
     */
    public static function today(int $employeeId): ?self
    {
        return self::where('employee_id', $employeeId)
            ->whereDate('work_date', today())
            ->first();
    }
}
