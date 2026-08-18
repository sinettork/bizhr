<?php

namespace App\Policies;

use App\Models\JobVacancy;
use App\Models\User;

class JobVacancyPolicy
{
    public function view(User $user, JobVacancy $vacancy): bool
    {
        return $vacancy->company_id === $user->companyId() && $user->can('recruitment.view');
    }

    public function manage(User $user, JobVacancy $vacancy): bool
    {
        return $vacancy->company_id === $user->companyId() && $user->can('recruitment.manage');
    }
}
