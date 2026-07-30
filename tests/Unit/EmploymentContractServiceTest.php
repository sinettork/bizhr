<?php

use App\Services\EmploymentContractService;
use Carbon\CarbonImmutable;

it('enforces Cambodia probation maximums', function (string $category, int $months) {
    $end = app(EmploymentContractService::class)
        ->maximumProbationEnd(CarbonImmutable::parse('2026-01-15'), $category);

    expect($end->toDateString())->toBe(CarbonImmutable::parse('2026-01-15')->addMonthsNoOverflow($months)->toDateString());
})->with([
    ['regular', 3],
    ['specialized', 2],
    ['non_specialized', 1],
]);

it('rejects an initial fixed duration contract longer than two years', function () {
    app(EmploymentContractService::class)->validateInitialFdcTerm(
        '2026-01-01',
        '2028-01-02',
        true,
    );
})->throws(\DomainException::class);

it('requires a written document for a fixed duration contract', function () {
    app(EmploymentContractService::class)->validateInitialFdcTerm(
        '2026-01-01',
        '2027-01-01',
        false,
    );
})->throws(\DomainException::class);
