<?php

namespace App\Policies;

use App\Models\ExpenseClaim;
use App\Models\User;

class ExpenseClaimPolicy
{
    public function view(User $user, ExpenseClaim $claim): bool
    {
        return $claim->company_id === $user->companyId() && ($user->can('expense.view') || ($claim->employee?->user_id === $user->id && $user->can('expense.view-own')));
    }

    public function managerApprove(User $user, ExpenseClaim $claim): bool
    {
        return $claim->company_id === $user->companyId() && $claim->employee?->user_id !== $user->id && $user->can('expense.approve-manager');
    }

    public function accountingApprove(User $user, ExpenseClaim $claim): bool
    {
        return $claim->company_id === $user->companyId() && $claim->employee?->user_id !== $user->id && $user->can('expense.approve-accounting');
    }

    public function pay(User $user, ExpenseClaim $claim): bool
    {
        return $claim->company_id === $user->companyId() && $user->can('expense.pay');
    }
}
