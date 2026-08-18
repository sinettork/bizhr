<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function view(User $user, Employee $employee): bool
    {
        return $this->sameCompany($user, $employee) && ($user->can('employee.view') || ($employee->user_id === $user->id && $user->can('employee.view-own')));
    }

    public function viewSensitive(User $user, Employee $employee): bool
    {
        return $this->sameCompany($user, $employee) && ($user->can('employee.view-sensitive') || ($employee->user_id === $user->id && $user->can('employee.view-own')));
    }

    public function update(User $user, Employee $employee): bool
    {
        return $this->sameCompany($user, $employee) && ($user->can('employee.edit') || ($employee->user_id === $user->id && $user->can('employee.edit-own')));
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $this->sameCompany($user, $employee) && $user->can('employee.delete');
    }

    private function sameCompany(User $user, Employee $employee): bool
    {
        return $user->companyId() !== null && $employee->company_id === $user->companyId();
    }
}
