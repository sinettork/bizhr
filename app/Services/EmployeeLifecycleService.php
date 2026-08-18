<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmploymentHistory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EmployeeLifecycleService
{
    /**
     * Record an employment status change and create history entry
     *
     * @param  array<string, mixed>|null  $changes
     */
    public function recordStatusChange(
        Employee $employee,
        string $eventType,
        ?Carbon $effectiveDate = null,
        ?array $changes = null,
        ?string $reason = null
    ): EmploymentHistory {
        $effectiveDate ??= now()->toDateString();

        $historyData = [
            'employee_id' => $employee->id,
            'event_type' => $eventType,
            'effective_date' => $effectiveDate,
            'recorded_by' => Auth::id(),
        ];

        // Capture current state if provided
        if ($changes) {
            $historyData = array_merge($historyData, $changes);
        } else {
            // Otherwise capture current employee state
            $historyData = array_merge($historyData, [
                'branch_id' => $employee->branch_id,
                'department_id' => $employee->department_id,
                'position_id' => $employee->position_id,
                'base_salary' => $employee->base_salary,
                'salary_currency' => $employee->salary_currency,
            ]);
        }

        if ($reason) {
            $historyData['notes'] = $reason;
        }

        return EmploymentHistory::create($historyData);
    }

    /**
     * Transition employee to a new status
     */
    public function transitionStatus(
        Employee $employee,
        string $newStatus,
        ?Carbon $effectiveDate = null,
        ?string $reason = null
    ): bool {
        $validTransitions = $this->getValidTransitions($employee->employment_status);

        if (! in_array($newStatus, $validTransitions, true)) {
            return false;
        }

        $employee->update(['employment_status' => $newStatus]);

        $this->recordStatusChange(
            $employee,
            match ($newStatus) {
                'Draft' => 'hire',
                'Active' => $employee->employment_status === 'On probation' ? 'probation_confirmation' : 'activation',
                'On probation' => 'probation_confirmation',
                'On leave' => 'leave_start',
                'Suspended' => 'suspension',
                'Resigned' => 'resignation',
                'Terminated' => 'termination',
                'Retired' => 'retirement',
                default => 'status_change',
            },
            $effectiveDate,
            ['base_salary' => $employee->base_salary, 'salary_currency' => $employee->salary_currency],
            $reason
        );

        return true;
    }

    /**
     * Record a salary change and create history entry
     */
    public function recordSalaryChange(
        Employee $employee,
        float $newSalary,
        string $currency = 'USD',
        ?Carbon $effectiveDate = null,
        ?string $reason = null
    ): EmploymentHistory {
        $oldSalary = $employee->base_salary;

        $employee->update([
            'base_salary' => $newSalary,
            'salary_currency' => $currency,
        ]);

        return $this->recordStatusChange(
            $employee,
            'salary_change',
            $effectiveDate,
            [
                'base_salary' => $newSalary,
                'salary_currency' => $currency,
                'notes' => "Changed from {$oldSalary} {$employee->salary_currency} to {$newSalary} {$currency}. {$reason}",
            ]
        );
    }

    /**
     * Record a transfer (branch/department/position change)
     */
    public function recordTransfer(
        Employee $employee,
        ?int $branchId = null,
        ?int $departmentId = null,
        ?int $positionId = null,
        ?Carbon $effectiveDate = null,
        ?string $reason = null
    ): EmploymentHistory {
        $changes = [
            'branch_id' => $branchId ?? $employee->branch_id,
            'department_id' => $departmentId ?? $employee->department_id,
            'position_id' => $positionId ?? $employee->position_id,
            'base_salary' => $employee->base_salary,
            'salary_currency' => $employee->salary_currency,
        ];

        $employee->update($changes);

        return $this->recordStatusChange(
            $employee,
            'transfer',
            $effectiveDate,
            $changes,
            $reason
        );
    }

    /**
     * Record a promotion (with optional salary increase)
     */
    public function recordPromotion(
        Employee $employee,
        int $newPositionId,
        ?float $newSalary = null,
        ?Carbon $effectiveDate = null,
        ?string $reason = null
    ): EmploymentHistory {
        $changes = [
            'position_id' => $newPositionId,
            'base_salary' => $newSalary ?? $employee->base_salary,
            'salary_currency' => $employee->salary_currency,
        ];

        $employee->update($changes);

        $noteText = "Promoted to position {$newPositionId}";
        if ($newSalary !== null && $newSalary !== $employee->base_salary) {
            $noteText .= " with salary increase to {$newSalary}";
        }
        if ($reason) {
            $noteText .= ". {$reason}";
        }

        return $this->recordStatusChange(
            $employee,
            'promotion',
            $effectiveDate,
            $changes,
            $noteText
        );
    }

    /**
     * Get valid transitions from current status
     *
     * @return list<string>
     */
    public function getValidTransitions(string $currentStatus): array
    {
        return match ($currentStatus) {
            'Draft' => ['On probation', 'Active'],
            'On probation' => ['Active', 'Suspended', 'Resigned', 'Terminated'],
            'Active' => ['On leave', 'Suspended', 'Resigned', 'Terminated', 'Retired'],
            'On leave' => ['Active', 'Suspended', 'Resigned', 'Terminated'],
            'Suspended' => ['Active', 'Resigned', 'Terminated'],
            'Resigned' => [],
            'Terminated' => [],
            'Retired' => [],
            default => [],
        };
    }

    /**
     * Get employment history for an employee
     *
     * @return HasMany<EmploymentHistory, Employee>
     */
    public function getEmploymentHistory(Employee $employee): HasMany
    {
        return $employee->employmentHistories()
            ->orderByDesc('effective_date')
            ->orderByDesc('created_at');
    }

    /**
     * Check if employee can be reinstated
     */
    public function canReinstate(Employee $employee): bool
    {
        return in_array($employee->employment_status, ['Resigned', 'Terminated'], true)
            && $employee->trashed() === false;
    }

    /**
     * Reinstate an employee
     */
    public function reinstate(
        Employee $employee,
        string $newStatus = 'active',
        ?Carbon $effectiveDate = null,
        ?string $reason = null
    ): bool {
        if (! $this->canReinstate($employee)) {
            return false;
        }

        $this->transitionStatus($employee, $newStatus, $effectiveDate, "Reinstatement: {$reason}");

        return true;
    }

    /**
     * Begin separation (mark for separation, not immediate)
     */
    public function initiateSeparation(
        Employee $employee,
        string $type = 'resignation', // resignation|termination|retirement
        ?Carbon $effectiveDate = null,
        ?string $reason = null
    ): bool {
        $validTypes = ['resignation', 'termination', 'retirement'];

        if (! in_array($type, $validTypes, true)) {
            return false;
        }

        return $this->transitionStatus(
            $employee,
            match ($type) {
                'resignation' => 'Resigned',
                'termination' => 'Terminated',
                'retirement' => 'Retired',
            },
            $effectiveDate,
            $reason ?? "Employee initiated {$type}"
        );
    }

    /**
     * Complete separation (soft delete and deactivate)
     */
    public function completeSeparation(Employee $employee): bool
    {
        if (! in_array($employee->employment_status, ['Resigned', 'Terminated', 'Retired'], true)) {
            return false;
        }

        $employee->delete(); // Soft delete

        // Optionally deactivate user account
        if ($employee->user) {
            $employee->user->update(['is_active' => false]);
        }

        return true;
    }
}
