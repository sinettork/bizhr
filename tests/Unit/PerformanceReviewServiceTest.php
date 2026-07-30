<?php

use App\Services\PerformanceReviewService;

it('calculates a weighted one-to-five performance score', function () {
    $score = app(PerformanceReviewService::class)->calculateWeightedScore([
        ['score' => 5, 'weight' => 60],
        ['score' => 3, 'weight' => 40],
    ]);
    expect($score)->toBe(4.2);
});

it('rejects review weights that do not total one hundred percent', function () {
    app(PerformanceReviewService::class)->calculateWeightedScore([
        ['score' => 5, 'weight' => 50],
        ['score' => 3, 'weight' => 40],
    ]);
})->throws(\DomainException::class);

it('rejects scores outside the one-to-five scale', function () {
    app(PerformanceReviewService::class)->calculateWeightedScore([
        ['score' => 6, 'weight' => 100],
    ]);
})->throws(\DomainException::class);
