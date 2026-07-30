<?php

use App\Services\PayrollCalculatorService;

function convertPayrollCurrency(float $amount, string $from, string $to): float
{
    $service = new PayrollCalculatorService();
    $method = new ReflectionMethod($service, 'convertCurrency');

    return $method->invoke($service, $amount, $from, $to);
}

it('converts KHR to USD using the configured production default', function () {
    expect(convertPayrollCurrency(4_000_000, 'KHR', 'USD'))->toBe(1_000.0);
});

it('converts USD to KHR using the configured production default', function () {
    expect(convertPayrollCurrency(250, 'USD', 'KHR'))->toBe(1_000_000.0);
});

it('does not change an amount when both currencies match', function () {
    expect(convertPayrollCurrency(1_234.56, 'USD', 'USD'))->toBe(1_234.56);
});
