<?php

namespace App\Policies;

use App\Models\JobApplicant;
use App\Models\User;

class JobApplicantPolicy
{
    public function view(User $user, JobApplicant $applicant): bool
    {
        return $applicant->vacancy !== null && $user->can('view', $applicant->vacancy);
    }

    public function manage(User $user, JobApplicant $applicant): bool
    {
        return $applicant->vacancy !== null && $user->can('manage', $applicant->vacancy);
    }
}
