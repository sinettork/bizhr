<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmploymentHistory;
use DomainException;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmployeeLifecycleService
{
    public function __construct(
        private readonly EmployeeOffboardingReadinessService $offboardingReadiness,
    ) {}

    /** @param array<string, mixed>|null $changes */
    public function recordStatusChange(
        Employee $employee,
        string $eventType,
        ?Carbon $effectiveDate = null,
        ?array $changes = null,
        ?string $reason = null,
    ): EmploymentHistory {
        $effectiveDate ??= now();

        $historyData = [
            'employee_id' => $employee->id,
            'event_type' => $eventType,
            'effective_date' => $effectiveDate,
            'recorded_by' => Auth::id(),
        ];

        if ($changes) {
            $historyData = array_merge($historyData, $changes);
        } else {
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

        return EmploymentHistory::query()->create($historyData);
    }

    public function transitionStatus(
        Employee $employee,
        string $newStatus,
        ?Carbon $effectiveDate = null,
        ?string $reason = null,
    ): bool {
        $oldStatus = $employee->employment_status;
        if (! in_array($newStatus, $this->getValidTransitions($oldStatus), true)) {
            return false;
        }

        DB::transaction(function () use ($employee, $oldStatus, $newStatus, $effectiveDate, $reason): void {
            $employee->update([
                'employment_status' => $newStatus,
                'is_active' => ! in_array($newStatus, ['Resigned', 'Terminated', 'Retired'], true),
            ]);

            $eventType = match (true) {
                $oldStatus === 'On probation' && $newStatus === 'Active' => 'probation_confirmation',
                $oldStatus === 'On leave' && $newStatus === 'Active' => 'leave_end',
                $oldStatus === 'Suspended' && $newStatus === 'Active' => 'reinstatement',
                $newStatus === 'On probation' => 'probation_start',
                $newStatus === 'On leave' => 'leave_start',
                $newStatus === 'Suspended' => 'suspension',
                $newStatus === 'Resigned' => 'resignation',
                $newStatus === 'Terminated' => 'termination',
                $newStatus === 'Retired' => 'retirement',
                $newStatus === 'Active' => 'activation',
                default => 'status_change',
            };

            $this->recordStatusChange(
                $employee,
                $eventType,
                $effectiveDate,
                ['base_salary' => $employee->base_salary, 'salary_currency' => $employee->salary_currency],
                $reason,
            );
        });

        return true;
    }

    public function recordSalaryChange(
        Employee $employee,
        float $newSalary,
        string $currency = 'USD',
        ?Carbon $effectiveDate = null,
        ?string $reason = null,
    ): EmploymentHistory {
        $oldSalary = $employee->base_salary;
        $oldCurrency = $employee->salary_currency;

        return DB::transaction(function () use ($employee, $oldSalary, $oldCurrency, $newSalary, $currency, $effectiveDate, $reason): EmploymentHistory {
            $employee->update([
                'base_salary' => $newSalary,
                'salary_currency' => $currency,
            ]);

            $note = "Changed from {$oldSalary} {$oldCurrency} to {$newSalary} {$currency}.";
            if ($reason) {
                $note .= ' '.trim($reason);
            }

            return $this->recordStatusChange(
                $employee,
                'salary_change',
                $effectiveDate,
                [
                    'branch_id' => $employee->branch_id,
                    'department_id' => $employee->department_id,
                    'position_id' => $employee->position_id,
                    'base_salary' => $newSalary,
                    'salary_currency' => $currency,
                ],
                $note,
            );
        });
    }

    public function recordTransfer(
        Employee $employee,
        ?int $branchId = null,
        ?int $departmentId = null,
        ?int $positionId = null,
        ?Carbon $effectiveDate = null,
        ?string $reason = null,
    ): EmploymentHistory {
        $changes = [
            'branch_id' => $branchId ?? $employee->branch_id,
            'department_id' => $departmentId ?? $employee->department_id,
            'position_id' => $positionId ?? $employee->position_id,
            'base_salary' => $employee->base_salary,
            'salary_currency' => $employee->salary_currency,
        ];

        return DB::transaction(function () use ($employee, $changes, $effectiveDate, $reason): EmploymentHistory {
            $employee->update($changes);

            return $this->recordStatusChange($employee, 'transfer', $effectiveDate, $changes, $reason);
        });
    }

    public function recordPromotion(
        Employee $employee,
        int $newPositionId,
        ?float $newSalary = null,
        ?Carbon $effectiveDate = null,
        ?string $reason = null,
    ): EmploymentHistory {
        $oldSalary = $employee->base_salary;
        $changes = [
            'position_id' => $newPositionId,
            'base_salary' => $newSalary ?? $oldSalary,
            'salary_currency' => $employee->salary_currency,
        ];

        return DB::transaction(function () use ($employee, $changes, $oldSalary, $newPositionId, $newSalary, $effectiveDate, $reason): EmploymentHistory {
            $employee->update($changes);

            $noteText = "Promoted to position {$newPositionId}";
            if ($newSalary !== null && (float) $newSalary !== (float) $oldSalary) {
                $noteText .= " with salary change from {$oldSalary} to {$newSalary}";
            }
            if ($reason) {
                $noteText .= '. '.trim($reason);
            }

            return $this->recordStatusChange($employee, 'promotion', $effectiveDate, $changes, $noteText);
        });
    }

    /** @return list<string> */
    public function getValidTransitions(string $currentStatus): array
    {
        return match ($currentStatus) {
            'Draft' => ['On probation', 'Active'],
            'On probation' => ['Active', 'Suspended', 'Resigned', 'Terminated'],
            'Active' => ['On leave', 'Suspended', 'Resigned', 'Terminated', 'Retired'],
            'On leave' => ['Active', 'Suspended', 'Resigned', 'Terminated'],
            'Suspended' => ['Active', 'Resigned', 'Terminated'],
            'Resigned', 'Terminated', 'Retired' => [],
            default => [],
        };
    }

    /** @return HasMany<EmploymentHistory, Employee> */
    public function getEmploymentHistory(Employee $employee): HasMany
    {
        return $employee->employmentHistories()
            ->orderByDesc('effective_date')
            ->orderByDesc('created_at');
    }

    public function canReinstate(Employee $employee): bool
    {
        return in_array($employee->employment_status, ['Resigned', 'Terminated'], true)
            && ! $employee->trashed();
    }

    public function reinstate(
        Employee $employee,
        string $newStatus = 'Active',
        ?Carbon $effectiveDate = null,
        ?string $reason = null,
    ): bool {
        if (! $this->canReinstate($employee) || ! in_array($newStatus, ['Active', 'On probation'], true)) {
            return false;
        }

        DB::transaction(function () use ($employee, $newStatus, $effectiveDate, $reason): void {
            $employee->update([
                'employment_status' => $newStatus,
                'is_active' => true,
            ]);

            if ($employee->user_id) {
                DB::table('users')->where('id', $employee->user_id)->update(['is_active' => true, 'updated_at' => now()]);
            }

            $this->recordStatusChange(
                $employee,
                'reinstatement',
                $effectiveDate,
                ['base_salary' => $employee->base_salary, 'salary_currency' => $employee->salary_currency],
                $reason ? 'Reinstatement: '.trim($reason) : 'Employee reinstated.',
            );
        });

        return true;
    }

    public function initiateSeparation(
        Employee $employee,
        string $type = 'resignation',
        ?Carbon $effectiveDate = null,
        ?string $reason = null,
    ): bool {
        $newStatus = match ($type) {
            'resignation' => 'Resigned',
            'termination' => 'Terminated',
            'retirement' => 'Retired',
            default => null,
        };

        if ($newStatus === null) {
            return false;
        }

        return $this->transitionStatus(
            $employee,
            $newStatus,
            $effectiveDate,
            $reason ?? "Employee initiated {$type}",
        );
    }

    public function completeSeparation(Employee $employee): bool
    {
        if (! in_array($employee->employment_status, ['Resigned', 'Terminated', 'Retired'], true)) {
            return false;
        }

        $readiness = $this->offboardingReadiness->forEmployee($employee);
        if (! $readiness['ready']) {
            throw new DomainException(
                'Employee offboarding still has '.$readiness['outstanding'].' outstanding area'.($readiness['outstanding'] === 1 ? '' : 's').'. Resolve the readiness checklist before archiving the employee record.'
            );
        }

        DB::transaction(function () use ($employee): void {
            if ($employee->user_id) {
                DB::table('users')->where('id', $employee->user_id)->update(['is_active' => false, 'updated_at' => now()]);
                DB::table('sessions')->where('user_id', $employee->user_id)->delete();
            }

            $employee->delete();
        });

        return true;
    }
}
