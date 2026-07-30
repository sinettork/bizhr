<?php

use App\Services\EmployeeGoalService;

it('calculates higher-is-better progress', function () {
    expect(app(EmployeeGoalService::class)->progressPercent(75, 100, 'higher_is_better'))->toBe(75.0);
});

it('caps KPI progress at one hundred percent', function () {
    expect(app(EmployeeGoalService::class)->progressPercent(150, 100, 'higher_is_better'))->toBe(100.0);
});

it('calculates lower-is-better progress', function () {
    expect(app(EmployeeGoalService::class)->progressPercent(4, 2, 'lower_is_better'))->toBe(50.0);
});

it('rejects an invalid scoring direction', function () {
    app(EmployeeGoalService::class)->progressPercent(1, 1, 'invalid');
})->throws(\DomainException::class);
