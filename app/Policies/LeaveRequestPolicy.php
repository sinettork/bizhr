<?php

namespace App\Policies;

use App\Models\LeaveRequest;
use App\Models\User;

class LeaveRequestPolicy
{
    public function view(User $user, LeaveRequest $leave): bool
    {
        return $leave->employee?->company_id === $user->companyId() && ($user->can('leave.approve') || ($leave->employee?->user_id === $user->id && $user->can('leave.request')));
    }

    public function approve(User $user, LeaveRequest $leave): bool
    {
        return $leave->employee?->company_id === $user->companyId() && $leave->employee?->user_id !== $user->id && $user->can('leave.approve');
    }
}
