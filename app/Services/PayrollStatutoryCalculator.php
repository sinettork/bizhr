<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\PayrollSetting;

class PayrollStatutoryCalculator
{
    public function calculate(
        float $gross,
        string $currency,
        Employee $employee,
        PayrollSetting $settings,
        ?float $taxExchangeRate = null,
    ): array {
        $businessRate = max(1, (float) $settings->khr_per_usd);
        $taxRate = max(1, $taxExchangeRate ?: $businessRate);
        $grossKhr = $currency === 'KHR' ? $gross : $gross * $taxRate;
        $relief = max(0, (int) $employee->tax_dependents)
            * max(0, (int) $settings->dependent_relief_khr);
        $taxableKhr = max(0, $grossKhr - $relief);

        $taxKhr = ! $settings->salary_tax_enabled
            ? 0
            : ($employee->is_tax_resident
                ? $this->residentSalaryTaxKhr($taxableKhr)
                : round($taxableKhr * 0.20, 2));

        $nssfGrossKhr = $currency === 'KHR' ? $gross : $gross * $businessRate;
        $contributoryWage = $employee->nssf_enrolled
            ? $this->nssfContributoryWageKhr($nssfGrossKhr)
            : 0;
        $employeeNssfKhr = round(
            $contributoryWage * max(0, (float) $settings->nssf_employee_health_rate) / 100,
            2,
        );
        $employerNssfKhr = round(
            $contributoryWage * (
                max(0, (float) $settings->nssf_employer_health_rate)
                + max(0, (float) $settings->nssf_employer_risk_rate)
            ) / 100,
            2,
        );

        return [
            'taxable_salary_khr' => round($taxableKhr, 2),
            'tax_amount' => $this->fromKhr($taxKhr, $currency, $taxRate),
            'nssf_employee_amount' => $this->fromKhr($employeeNssfKhr, $currency, $businessRate),
            'nssf_employer_amount' => $this->fromKhr($employerNssfKhr, $currency, $businessRate),
            'tax_exchange_rate' => $taxRate,
            'nssf_contributory_wage_khr' => $contributoryWage,
            'dependent_relief_khr' => $relief,
        ];
    }

    public function residentSalaryTaxKhr(float $taxableKhr): float
    {
        return round(match (true) {
            $taxableKhr <= 1_500_000 => 0,
            $taxableKhr <= 2_000_000 => ($taxableKhr * 0.05) - 75_000,
            $taxableKhr <= 8_500_000 => ($taxableKhr * 0.10) - 175_000,
            $taxableKhr <= 12_500_000 => ($taxableKhr * 0.15) - 600_000,
            default => ($taxableKhr * 0.20) - 1_225_000,
        }, 2);
    }

    public function nssfContributoryWageKhr(float $grossKhr): int
    {
        if ($grossKhr <= 200_000) {
            return 200_000;
        }

        if ($grossKhr > 1_200_000) {
            return 1_200_000;
        }

        return 225_000 + ((int) floor(($grossKhr - 200_001) / 50_000) * 50_000);
    }

    private function fromKhr(float $amount, string $currency, float $exchangeRate): float
    {
        return round($currency === 'KHR' ? $amount : $amount / $exchangeRate, 2);
    }
}
