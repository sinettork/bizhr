<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    public function view(User $user, Attendance $attendance): bool
    {
        return $attendance->employee?->company_id === $user->companyId() && ($user->can('attendance.report') || $attendance->employee?->user_id === $user->id);
    }

    public function correct(User $user, Attendance $attendance): bool
    {
        return $attendance->employee?->company_id === $user->companyId() && $attendance->employee?->user_id === $user->id && $user->can('attendance.correction.request');
    }

    public function reviewOvertime(User $user, Attendance $attendance): bool
    {
        return $attendance->employee?->company_id === $user->companyId() && $user->can('payroll.approve');
    }
}
