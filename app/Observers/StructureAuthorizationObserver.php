<?php

namespace App\Observers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\EmploymentType;
use App\Models\Position;
use Illuminate\Database\Eloquent\Model;

class StructureAuthorizationObserver
{
    public function creating(Model $model): void
    {
        $this->authorize($model, 'create');
    }

    public function updating(Model $model): void
    {
        $this->authorize($model, 'edit');
    }

    public function deleting(Model $model): void
    {
        $this->authorize($model, 'delete');
    }

    private function authorize(Model $model, string $action): void
    {
        if (app()->runningInConsole()) {
            return;
        }

        $ability = match ($model::class) {
            Company::class => 'company.edit',
            Branch::class => 'branch.'.$action,
            Department::class => 'department.'.$action,
            Position::class => 'position.'.$action,
            EmploymentType::class => 'employment-type.'.$action,
            default => null,
        };

        abort_unless($ability && auth()->user()?->can($ability), 403);
    }
}
