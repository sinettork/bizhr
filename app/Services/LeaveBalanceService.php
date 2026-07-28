<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class LeaveBalanceService
{
    /**
     * Create missing balance records for every active employee
     * and every active leave type for a selected year.
     */
    public function initializeYearForCompany(
        int $companyId,
        int $year
    ): int {
        $this->validateYear($year);

        return DB::transaction(
            function () use (
                $companyId,
                $year
            ): int {
                $employees = Employee::query()
                    ->where(
                        'company_id',
                        $companyId
                    )
                    ->where('is_active', true)
                    ->orderBy('id')
                    ->get();

                $leaveTypes = LeaveType::query()
                    ->where(
                        'company_id',
                        $companyId
                    )
                    ->where('is_active', true)
                    ->orderBy('id')
                    ->get();

                $createdCount = 0;

                foreach ($employees as $employee) {
                    foreach ($leaveTypes as $leaveType) {
                        $balance = $this
                            ->initializeForEmployeeAndType(
                                $employee,
                                $leaveType,
                                $year
                            );

                        if ($balance->wasRecentlyCreated) {
                            $createdCount++;
                        }
                    }
                }

                return $createdCount;
            }
        );
    }

    /**
     * Create one balance record when it does not exist.
     */
    public function initializeForEmployeeAndType(
        Employee $employee,
        LeaveType $leaveType,
        int $year
    ): LeaveBalance {
        $this->validateYear($year);

        if (
            (int) $employee->company_id
            !== (int) $leaveType->company_id
        ) {
            throw new InvalidArgumentException(
                'Employee and leave type must belong to the same company.'
            );
        }

        $openingBalance = $this
            ->calculateCarryForward(
                $employee,
                $leaveType,
                $year
            );

        $earnedDays = round(
            (float) $leaveType->days_per_year,
            2
        );

        $balance = LeaveBalance::query()
            ->firstOrCreate(
                [
                    'employee_id' =>
                        $employee->id,

                    'leave_type_id' =>
                        $leaveType->id,

                    'year' =>
                        $year,
                ],
                [
                    'opening_balance' =>
                        $openingBalance,

                    'earned_days' =>
                        $earnedDays,

                    'used_days' =>
                        0,

                    'adjustment_days' =>
                        0,

                    'remaining_days' =>
                        round(
                            $openingBalance
                            + $earnedDays,
                            2
                        ),
                ]
            );

        if (! $balance->wasRecentlyCreated) {
            $this->recalculate($balance);
        }

        return $balance->fresh([
            'employee',
            'leaveType',
        ]);
    }

    /**
     * Set an HR adjustment and recalculate remaining days.
     */
    public function setAdjustment(
        LeaveBalance $balance,
        float $adjustmentDays
    ): LeaveBalance {
        return DB::transaction(
            function () use (
                $balance,
                $adjustmentDays
            ): LeaveBalance {
                $lockedBalance = LeaveBalance::query()
                    ->lockForUpdate()
                    ->findOrFail($balance->id);

                $lockedBalance->adjustment_days =
                    round(
                        $adjustmentDays,
                        2
                    );

                $lockedBalance->save();

                return $this->recalculate(
                    $lockedBalance
                );
            }
        );
    }

    /**
     * Synchronize used days from approved leave requests.
     */
    public function syncUsedDays(
        LeaveBalance $balance
    ): LeaveBalance {
        return DB::transaction(
            function () use (
                $balance
            ): LeaveBalance {
                $lockedBalance = LeaveBalance::query()
                    ->lockForUpdate()
                    ->findOrFail($balance->id);

                $usedDays = LeaveRequest::query()
                    ->where(
                        'employee_id',
                        $lockedBalance->employee_id
                    )
                    ->where(
                        'leave_type_id',
                        $lockedBalance->leave_type_id
                    )
                    ->where(
                        'status',
                        'approved'
                    )
                    ->whereYear(
                        'start_date',
                        $lockedBalance->year
                    )
                    ->sum('total_days');

                $lockedBalance->used_days =
                    round(
                        (float) $usedDays,
                        2
                    );

                $lockedBalance->save();

                return $this->recalculate(
                    $lockedBalance
                );
            }
        );
    }

    /**
     * Synchronize all balances for a company and year.
     */
    public function syncCompanyYear(
        int $companyId,
        int $year
    ): int {
        $this->validateYear($year);

        $this->initializeYearForCompany(
            $companyId,
            $year
        );

        $balances = LeaveBalance::query()
            ->where('year', $year)
            ->whereHas(
                'employee',
                fn ($query) => $query->where(
                    'company_id',
                    $companyId
                )
            )
            ->get();

        foreach ($balances as $balance) {
            $this->syncUsedDays($balance);
        }

        return $balances->count();
    }

    /**
     * Recalculate remaining balance.
     */
    public function recalculate(
        LeaveBalance $balance
    ): LeaveBalance {
        $remainingDays = round(
            (float) $balance->opening_balance
            + (float) $balance->earned_days
            + (float) $balance->adjustment_days
            - (float) $balance->used_days,
            2
        );

        $balance->remaining_days =
            $remainingDays;

        $balance->save();

        return $balance->fresh([
            'employee',
            'leaveType',
        ]);
    }

    /**
     * Calculate carry-forward from the previous year.
     */
    private function calculateCarryForward(
        Employee $employee,
        LeaveType $leaveType,
        int $year
    ): float {
        if (! $leaveType->carry_forward_allowed) {
            return 0;
        }

        $previousBalance = LeaveBalance::query()
            ->where(
                'employee_id',
                $employee->id
            )
            ->where(
                'leave_type_id',
                $leaveType->id
            )
            ->where(
                'year',
                $year - 1
            )
            ->first();

        if (! $previousBalance) {
            return 0;
        }

        $remainingDays = max(
            0,
            (float) $previousBalance
                ->remaining_days
        );

        $maximumCarryForward = max(
            0,
            (float) $leaveType
                ->maximum_carry_forward_days
        );

        return round(
            min(
                $remainingDays,
                $maximumCarryForward
            ),
            2
        );
    }

    private function validateYear(
        int $year
    ): void {
        if (
            $year < 2000
            || $year > 2100
        ) {
            throw new InvalidArgumentException(
                'Leave balance year must be between 2000 and 2100.'
            );
        }
    }
}