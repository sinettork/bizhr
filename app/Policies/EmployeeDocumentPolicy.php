<?php

namespace App\Policies;

use App\Models\EmployeeDocument;
use App\Models\User;

class EmployeeDocumentPolicy
{
    public function view(User $user, EmployeeDocument $document): bool
    {
        return $document->employee !== null && $user->can('viewSensitive', $document->employee);
    }

    public function update(User $user, EmployeeDocument $document): bool
    {
        return $document->employee !== null && $user->can('update', $document->employee) && ($user->can('employee.view-sensitive') || $document->employee->user_id === $user->id);
    }
}
