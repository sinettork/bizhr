<?php

namespace App\Policies;

use App\Models\PayrollPeriod;
use App\Models\User;

class PayrollPeriodPolicy
{
    public function view(User $user, PayrollPeriod $period): bool
    {
        return $this->sameCompany($user, $period) && $user->can('payroll.view');
    }

    public function approve(User $user, PayrollPeriod $period): bool
    {
        return $this->sameCompany($user, $period) && $user->can('payroll.approve');
    }

    public function pay(User $user, PayrollPeriod $period): bool
    {
        return $this->sameCompany($user, $period) && $user->can('payroll.process');
    }

    public function process(User $user, PayrollPeriod $period): bool
    {
        return $this->sameCompany($user, $period) && $user->can('payroll.process');
    }

    private function sameCompany(User $user, PayrollPeriod $period): bool
    {
        return $user->companyId() !== null && $period->company_id === $user->companyId();
    }
}
