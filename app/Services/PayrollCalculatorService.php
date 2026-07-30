<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\LeaveRequest;
use App\Models\PayrollAdjustment;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\PayrollSetting;
use App\Models\PublicHoliday;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PayrollCalculatorService
{
    public const WORKING_DAYS = 26;
    public const HOURS_PER_DAY = 8;
    public const OVERTIME_MULTIPLIER = 1.5;
    public const KHR_PER_USD = 4000;
    protected float $exchangeRate = self::KHR_PER_USD;

    public function __construct(
        private readonly ?PayrollStatutoryCalculator $statutoryCalculator = null,
    ) {}

    public function generate(PayrollPeriod $period): int
    {
        return DB::transaction(function () use ($period): int {
            $employees = Employee::query()
                ->where('company_id', $period->company_id)
                ->where('is_active', true)
                ->whereNotNull('base_salary')
                ->get();

            foreach ($employees as $employee) {
                $this->generateEmployee($period, $employee);
            }

            return $employees->count();
        });
    }

    protected function generateEmployee(PayrollPeriod $period, Employee $employee): PayrollItem
    {
        $settings = PayrollSetting::forCompany($period->company_id);
        $workingDays = max(1, (int) $settings->working_days_per_month);
        $hoursPerDay = max(0.25, (float) $settings->hours_per_day);
        $this->exchangeRate = max(1, (float) $settings->khr_per_usd);
        $currency = strtoupper($employee->salary_currency ?: 'USD');
        $payType = in_array($employee->pay_type, ['monthly', 'daily', 'hourly'], true)
            ? $employee->pay_type
            : 'monthly';
        $base = (float) $employee->base_salary;
        $exceptions = [];

        $schedules = EmployeeSchedule::query()
            ->with('workShift')
            ->where('employee_id', $employee->id)
            ->whereBetween('work_date', [$period->start_date, $period->end_date])
            ->where('is_rest_day', false)
            ->orderBy('work_date')
            ->get();

        if ($schedules->isEmpty()) {
            $exceptions[] = 'មិនមានកាលវិភាគការងារក្នុងវគ្គប្រាក់ខែនេះ។';
        }

        $attendances = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('work_date', [$period->start_date, $period->end_date])
            ->get()
            ->keyBy(fn (Attendance $attendance) => Carbon::parse($attendance->work_date)->toDateString());

        $leaves = LeaveRequest::query()
            ->with('leaveType')
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $period->end_date)
            ->whereDate('end_date', '>=', $period->start_date)
            ->get();

        $leaveCoverage = $this->buildLeaveCoverage($leaves, $employee->id, $period);
        $paidHolidays = PublicHoliday::query()
            ->where('company_id', $period->company_id)
            ->where('is_paid', true)
            ->whereBetween('holiday_date', [$period->start_date, $period->end_date])
            ->pluck('name', 'holiday_date')
            ->mapWithKeys(fn ($name, $date) => [Carbon::parse($date)->toDateString() => $name]);

        $metrics = [
            'scheduled' => 0,
            'worked' => 0,
            'paid_leave' => 0,
            'unpaid_leave' => 0,
            'holiday' => 0,
            'absent' => 0,
            'late' => 0,
            'early_leave' => 0,
            'overtime' => 0,
            'worked_days' => 0.0,
            'absent_days' => 0.0,
            'paid_leave_days' => 0.0,
            'unpaid_leave_days' => 0.0,
            'payable_day_equivalents' => 0.0,
        ];

        foreach ($schedules as $schedule) {
            $date = $schedule->work_date->toDateString();
            $shift = $schedule->workShift;

            if (! $shift) {
                $exceptions[] = "កាលវិភាគ {$date} មិនមានវេនការងារ។";
                continue;
            }

            $scheduledMinutes = max(0, (int) $shift->getDurationMinutes());
            if ($scheduledMinutes === 0) {
                $exceptions[] = "វេនការងារ {$date} មានរយៈពេលសូន្យ។";
                continue;
            }

            $metrics['scheduled'] += $scheduledMinutes;

            if ($paidHolidays->has($date)) {
                $metrics['holiday'] += $scheduledMinutes;
                $metrics['payable_day_equivalents'] += 1;
                continue;
            }

            $coverage = $leaveCoverage[$date] ?? ['paid' => 0.0, 'unpaid' => 0.0];
            $paidRatio = min(1, max(0, (float) $coverage['paid']));
            $unpaidRatio = min(1 - $paidRatio, max(0, (float) $coverage['unpaid']));
            $paidLeaveMinutes = (int) round($scheduledMinutes * $paidRatio);
            $unpaidLeaveMinutes = (int) round($scheduledMinutes * $unpaidRatio);
            $attendanceRequired = max(0, $scheduledMinutes - $paidLeaveMinutes - $unpaidLeaveMinutes);

            $metrics['paid_leave'] += $paidLeaveMinutes;
            $metrics['unpaid_leave'] += $unpaidLeaveMinutes;
            $metrics['paid_leave_days'] += $paidRatio;
            $metrics['unpaid_leave_days'] += $unpaidRatio;

            $attendance = $attendances->get($date);
            $regularWorked = 0;

            if ($attendance) {
                $metrics['late'] += max(0, (int) $attendance->late_minutes);
                $metrics['early_leave'] += max(0, (int) $attendance->early_leave_minutes);

                if ($attendance->check_in_at && $attendance->check_out_at) {
                    $regularWorked = min(
                        $attendanceRequired,
                        max(0, (int) $attendance->worked_minutes),
                    );
                } elseif ($attendanceRequired > 0 && ($attendance->check_in_at || $attendance->check_out_at)) {
                    $exceptions[] = "ទិន្នន័យចូល/ចេញមិនពេញលេញនៅ {$date}។";
                }

                if (! $settings->require_overtime_approval || $attendance->overtime_approved) {
                    $metrics['overtime'] += max(0, (int) $attendance->overtime_minutes);
                } elseif ((int) $attendance->overtime_minutes > 0) {
                    $exceptions[] = "ម៉ោងបន្ថែមនៅ {$date} មិនទាន់បានអនុម័ត។";
                }
            }

            $missingMinutes = max(0, $attendanceRequired - $regularWorked);
            $metrics['worked'] += $regularWorked;
            $metrics['absent'] += $missingMinutes;
            $metrics['worked_days'] += $regularWorked / $scheduledMinutes;
            $metrics['absent_days'] += $missingMinutes / $scheduledMinutes;
            $metrics['payable_day_equivalents'] +=
                ($regularWorked + $paidLeaveMinutes) / $scheduledMinutes;
        }

        $fallbackScheduledMinutes = (int) round($workingDays * $hoursPerDay * 60);
        $rateDivisorMinutes = $metrics['scheduled'] > 0
            ? $metrics['scheduled']
            : $fallbackScheduledMinutes;
        $monthlyMinuteRate = $base / $rateDivisorMinutes;
        $dailyRate = (float) ($employee->daily_rate ?: ($base / $workingDays));
        $hourlyRate = (float) ($employee->hourly_rate ?: match ($payType) {
            'daily' => $dailyRate / $hoursPerDay,
            default => ($base / $rateDivisorMinutes) * 60,
        });
        $overtimeMultiplier = (float) ($employee->overtime_multiplier ?: $settings->default_overtime_multiplier);

        $payableBase = match ($payType) {
            'daily' => $dailyRate * $metrics['payable_day_equivalents'],
            'hourly' => $hourlyRate * (
                ($metrics['worked'] + $metrics['paid_leave'] + $metrics['holiday']) / 60
            ),
            default => $settings->deduct_unpaid_absence
                ? $base - (($metrics['absent'] + $metrics['unpaid_leave']) * $monthlyMinuteRate)
                : $base,
        };
        $payableBase = max(0, round($payableBase, 2));

        $overtimeHours = round($metrics['overtime'] / 60, 2);
        $overtimeAmount = round($overtimeHours * $hourlyRate * $overtimeMultiplier, 2);
        $adjustments = $this->adjustmentsFor($period, $employee);
        $allowance = $this->sumTypes($adjustments, ['allowance', 'reimbursement'], $currency);
        $bonus = $this->sumTypes($adjustments, ['bonus'], $currency);
        $commission = $this->sumTypes($adjustments, ['commission'], $currency);
        $deduction = $this->sumTypes($adjustments, ['deduction', 'penalty'], $currency);
        $loan = $this->sumTypes($adjustments, ['loan'], $currency);
        $advance = $this->sumTypes($adjustments, ['advance'], $currency);
        $fringeBenefitAmount = round((float) $adjustments
            ->where('is_fringe_benefit', true)
            ->sum(fn (PayrollAdjustment $adjustment) => $this->convertCurrency(
                (float) $adjustment->amount,
                strtoupper($adjustment->currency ?: $currency),
                $currency,
            )), 2);
        $fringeBenefitTaxAmount = round((float) $adjustments
            ->where('is_fringe_benefit', true)
            ->sum(fn (PayrollAdjustment $adjustment) => $this->convertCurrency(
                (float) $adjustment->amount * max(0, (float) $adjustment->fringe_benefit_tax_rate) / 100,
                strtoupper($adjustment->currency ?: $currency),
                $currency,
            )), 2);

        $attendanceDeduction = $payType === 'monthly'
            ? round($base - $payableBase, 2)
            : 0;
        $grossBase = $payType === 'monthly' ? $base : $payableBase;
        $totalDeduction = $attendanceDeduction + $deduction;
        $gross = round($grossBase + $overtimeAmount + $allowance + $bonus + $commission, 2);
        $statutory = ($this->statutoryCalculator ?? app(PayrollStatutoryCalculator::class))->calculate(
            $gross,
            $currency,
            $employee,
            $settings,
            (float) $period->tax_exchange_rate_khr,
        );
        $net = max(0, round(
            $gross
            - $totalDeduction
            - $loan
            - $advance
            - $statutory['tax_amount']
            - $statutory['nssf_employee_amount'],
            2,
        ));
        $employerTotalCost = round($gross + $statutory['nssf_employer_amount'] + $fringeBenefitTaxAmount, 2);

        return PayrollItem::query()->updateOrCreate(
            ['payroll_period_id' => $period->id, 'employee_id' => $employee->id],
            [
                'currency' => $currency,
                'pay_type' => $payType,
                'base_salary' => $base,
                'payable_base_amount' => $payableBase,
                'scheduled_minutes' => $metrics['scheduled'],
                'worked_minutes' => $metrics['worked'],
                'paid_leave_minutes' => $metrics['paid_leave'],
                'unpaid_leave_minutes' => $metrics['unpaid_leave'],
                'holiday_minutes' => $metrics['holiday'],
                'absent_minutes' => $metrics['absent'],
                'late_minutes' => $metrics['late'],
                'early_leave_minutes' => $metrics['early_leave'],
                'approved_overtime_minutes' => $metrics['overtime'],
                'worked_days' => round($metrics['worked_days'], 2),
                'absent_days' => round($metrics['absent_days'], 2),
                'paid_leave_days' => round($metrics['paid_leave_days'], 2),
                'unpaid_leave_days' => round($metrics['unpaid_leave_days'], 2),
                'overtime_hours' => $overtimeHours,
                'overtime_amount' => $overtimeAmount,
                'allowance_amount' => $allowance,
                'bonus_amount' => $bonus,
                'commission_amount' => $commission,
                'deduction_amount' => $totalDeduction,
                'loan_deduction' => $loan,
                'advance_deduction' => $advance,
                'taxable_salary_khr' => $statutory['taxable_salary_khr'],
                'salary_tax_exchange_rate' => $statutory['tax_exchange_rate'],
                'tax_amount' => $statutory['tax_amount'],
                'nssf_employee_amount' => $statutory['nssf_employee_amount'],
                'nssf_employer_amount' => $statutory['nssf_employer_amount'],
                'employer_total_cost' => $employerTotalCost,
                'fringe_benefit_amount' => $fringeBenefitAmount,
                'fringe_benefit_tax_amount' => $fringeBenefitTaxAmount,
                'gross_salary' => $gross,
                'net_salary' => $net,
                'exception_count' => count($exceptions),
                'calculation_details' => [
                    'rules' => [
                        'working_days_fallback' => $workingDays,
                        'hours_per_day_fallback' => $hoursPerDay,
                        'overtime_multiplier' => $overtimeMultiplier,
                        'khr_per_usd' => $this->exchangeRate,
                    ],
                    'rates' => [
                        'daily_rate' => round($dailyRate, 2),
                        'hourly_rate' => round($hourlyRate, 4),
                    ],
                    'statutory' => [
                        'tax_resident' => (bool) $employee->is_tax_resident,
                        'tax_dependents' => (int) $employee->tax_dependents,
                        'dependent_relief_khr' => $statutory['dependent_relief_khr'],
                        'nssf_enrolled' => (bool) $employee->nssf_enrolled,
                        'nssf_contributory_wage_khr' => $statutory['nssf_contributory_wage_khr'],
                        'gdt_tax_exchange_rate' => $statutory['tax_exchange_rate'],
                        'gdt_tax_rate_date' => $period->tax_rate_date?->toDateString(),
                        'gdt_tax_rate_source' => $period->tax_rate_source,
                        'fringe_benefit_amount' => $fringeBenefitAmount,
                        'fringe_benefit_tax_amount' => $fringeBenefitTaxAmount,
                    ],
                    'exceptions' => $exceptions,
                ],
            ],
        );
    }

    protected function buildLeaveCoverage(
        Collection $leaves,
        int $employeeId,
        PayrollPeriod $period,
    ): array {
        $coverage = [];

        foreach ($leaves as $leave) {
            $scheduledDays = EmployeeSchedule::query()
                ->where('employee_id', $employeeId)
                ->whereBetween('work_date', [$leave->start_date, $leave->end_date])
                ->where('is_rest_day', false)
                ->whereNotNull('work_shift_id')
                ->count();
            $divisor = max(1, $scheduledDays ?: $leave->start_date->diffInDays($leave->end_date) + 1);
            $ratio = min(1, max(0, (float) $leave->total_days / $divisor));
            $kind = $leave->leaveType?->is_paid ? 'paid' : 'unpaid';
            $start = $leave->start_date->greaterThan($period->start_date)
                ? $leave->start_date
                : $period->start_date;
            $end = $leave->end_date->lessThan($period->end_date)
                ? $leave->end_date
                : $period->end_date;

            foreach (CarbonPeriod::create($start, $end) as $date) {
                $key = $date->toDateString();
                $coverage[$key] ??= ['paid' => 0.0, 'unpaid' => 0.0];
                $coverage[$key][$kind] = min(1, $coverage[$key][$kind] + $ratio);
            }
        }

        return $coverage;
    }

    protected function adjustmentsFor(PayrollPeriod $period, Employee $employee): Collection
    {
        return PayrollAdjustment::query()
            ->where('employee_id', $employee->id)
            ->whereDate('effective_date', '<=', $period->end_date)
            ->where(fn ($query) => $query
                ->whereNull('end_date')
                ->orWhereDate('end_date', '>=', $period->start_date))
            ->where(fn ($query) => $query
                ->whereNull('payroll_period_id')
                ->orWhere('payroll_period_id', $period->id))
            ->get();
    }

    protected function sumTypes(
        Collection $adjustments,
        array $types,
        string $targetCurrency,
    ): float {
        return round((float) $adjustments
            ->whereIn('type', $types)
            ->sum(fn (PayrollAdjustment $adjustment) => $this->convertCurrency(
                (float) $adjustment->amount,
                strtoupper($adjustment->currency ?: $targetCurrency),
                $targetCurrency,
            )), 2);
    }

    protected function convertCurrency(float $amount, string $from, string $to): float
    {
        if ($from === $to) {
            return $amount;
        }

        if ($from === 'KHR' && $to === 'USD') {
            return $amount / $this->exchangeRate;
        }

        if ($from === 'USD' && $to === 'KHR') {
            return $amount * $this->exchangeRate;
        }

        return $amount;
    }
}
