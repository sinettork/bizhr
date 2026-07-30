<?php

use App\Models\Employee;
use App\Models\PayrollSetting;
use App\Services\PayrollStatutoryCalculator;

it('calculates the progressive resident salary tax brackets in KHR', function (
    float $salary,
    float $expected,
) {
    expect(app(PayrollStatutoryCalculator::class)->residentSalaryTaxKhr($salary))
        ->toBe($expected);
})->with([
    [1_500_000, 0.0],
    [2_000_000, 25_000.0],
    [8_500_000, 675_000.0],
    [12_500_000, 1_275_000.0],
    [15_000_000, 1_775_000.0],
]);

it('uses the official NSSF contributory wage bands and cap', function (
    float $gross,
    int $expected,
) {
    expect(app(PayrollStatutoryCalculator::class)->nssfContributoryWageKhr($gross))
        ->toBe($expected);
})->with([
    [150_000, 200_000],
    [225_000, 225_000],
    [275_000, 275_000],
    [1_500_000, 1_200_000],
]);

it('separates employee deductions from employer-only NSSF costs', function () {
    $employee = (new Employee())->forceFill([
        'is_tax_resident' => true,
        'tax_dependents' => 1,
        'nssf_enrolled' => true,
    ]);
    $settings = new PayrollSetting([
        'khr_per_usd' => 4000,
        'salary_tax_enabled' => true,
        'dependent_relief_khr' => 150000,
        'nssf_employee_health_rate' => 1.3,
        'nssf_employer_health_rate' => 1.3,
        'nssf_employer_risk_rate' => 0.8,
    ]);

    $result = app(PayrollStatutoryCalculator::class)
        ->calculate(500, 'USD', $employee, $settings);

    expect($result['taxable_salary_khr'])->toBe(1_850_000.0)
        ->and($result['tax_amount'])->toBe(4.38)
        ->and($result['nssf_employee_amount'])->toBe(3.9)
        ->and($result['nssf_employer_amount'])->toBe(6.3);
});

it('uses the payroll period GDT rate for salary tax without changing the business NSSF rate', function () {
    $employee = (new Employee())->forceFill([
        'is_tax_resident' => true,
        'tax_dependents' => 0,
        'nssf_enrolled' => true,
    ]);
    $settings = new PayrollSetting([
        'khr_per_usd' => 4000,
        'salary_tax_enabled' => true,
        'dependent_relief_khr' => 150000,
        'nssf_employee_health_rate' => 1.3,
        'nssf_employer_health_rate' => 1.3,
        'nssf_employer_risk_rate' => 0.8,
    ]);

    $result = app(PayrollStatutoryCalculator::class)
        ->calculate(500, 'USD', $employee, $settings, 4042);

    expect($result['tax_exchange_rate'])->toBe(4042.0)
        ->and($result['taxable_salary_khr'])->toBe(2_021_000.0)
        ->and($result['nssf_contributory_wage_khr'])->toBe(1_200_000);
});
