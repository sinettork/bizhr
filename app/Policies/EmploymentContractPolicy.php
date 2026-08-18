<?php

namespace App\Policies;

use App\Models\EmploymentContract;
use App\Models\User;

class EmploymentContractPolicy
{
    public function view(User $user, EmploymentContract $contract): bool
    {
        return $this->sameCompany($user, $contract) && ($user->can('contract.view') || ($contract->employee?->user_id === $user->id && $user->can('contract.view-own')));
    }

    public function update(User $user, EmploymentContract $contract): bool
    {
        return $this->sameCompany($user, $contract) && $user->can('contract.edit');
    }

    public function approve(User $user, EmploymentContract $contract): bool
    {
        return $this->sameCompany($user, $contract) && $user->can('contract.approve');
    }

    public function terminate(User $user, EmploymentContract $contract): bool
    {
        return $this->sameCompany($user, $contract) && $user->can('contract.terminate');
    }

    private function sameCompany(User $user, EmploymentContract $contract): bool
    {
        return $user->companyId() !== null && $contract->company_id === $user->companyId();
    }
}
