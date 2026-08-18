<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\EmploymentHistory;
use Illuminate\Support\Facades\Auth;

class EmployeeObserver
{
    /**
     * Handle the Employee "created" event.
     */
    public function created(Employee $employee): void
    {
        // Record initial employment record
        EmploymentHistory::create([
            'employee_id' => $employee->id,
            'event_type' => 'hire',
            'effective_date' => $employee->hire_date ?? now()->toDateString(),
            'branch_id' => $employee->branch_id,
            'department_id' => $employee->department_id,
            'position_id' => $employee->position_id,
            'base_salary' => $employee->base_salary,
            'salary_currency' => $employee->salary_currency,
            'recorded_by' => Auth::id(),
            'notes' => 'Employee record created',
        ]);
    }

    /**
     * Handle the Employee "updated" event.
     * Only records significant changes automatically.
     */
    public function updated(Employee $employee): void
    {
        $original = $employee->getOriginal();
        $changes = $employee->getChanges();

        // Detect significant changes
        $significantFields = ['employment_status', 'branch_id', 'department_id', 'position_id', 'base_salary'];
        $hasSignificantChange = false;

        foreach ($significantFields as $field) {
            if (isset($changes[$field]) && $changes[$field] !== ($original[$field] ?? null)) {
                $hasSignificantChange = true;
                break;
            }
        }

        // If significant changes detected, we rely on the EmployeeLifecycleService to record history
        // This observer doesn't create duplicate entries if the service is already recording
        // We can add audit-log style tracking here if needed for all changes
    }

    /**
     * Handle the Employee "deleted" event (soft delete).
     */
    public function deleted(Employee $employee): void
    {
        if ($employee->trashed()) {
            // Record separation in history
            EmploymentHistory::create([
                'employee_id' => $employee->id,
                'event_type' => 'separation',
                'effective_date' => now()->toDateString(),
                'base_salary' => $employee->base_salary,
                'salary_currency' => $employee->salary_currency,
                'recorded_by' => Auth::id(),
                'notes' => 'Employee record archived/separated',
            ]);
        }
    }

    /**
     * Handle the Employee "restored" event (restore from soft delete).
     */
    public function restored(Employee $employee): void
    {
        // Optionally log restoration if needed
        EmploymentHistory::create([
            'employee_id' => $employee->id,
            'event_type' => 'reinstatement',
            'effective_date' => now()->toDateString(),
            'base_salary' => $employee->base_salary,
            'salary_currency' => $employee->salary_currency,
            'recorded_by' => Auth::id(),
            'notes' => 'Employee record restored',
        ]);
    }

    /**
     * Handle the Employee "force deleted" event.
     * Only for complete removal (rarely used in HR systems).
     */
    public function forceDeleted(Employee $employee): void
    {
        // In a production HR system, hard deletes should be extremely rare
        // Consider preventing them entirely for audit compliance
    }
}
