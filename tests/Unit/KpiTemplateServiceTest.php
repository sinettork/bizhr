<?php

use App\Services\KpiTemplateService;

function validKpiItems(): array
{
    return [
        ['name' => 'Sales', 'measurement_unit' => 'currency_usd', 'target_value' => 10000, 'weight' => 60, 'scoring_direction' => 'higher_is_better'],
        ['name' => 'Returns', 'measurement_unit' => 'percent', 'target_value' => 2, 'weight' => 40, 'scoring_direction' => 'lower_is_better'],
    ];
}

it('accepts KPI criteria whose weights total exactly one hundred percent', function () {
    app(KpiTemplateService::class)->validateItems(validKpiItems());
    expect(true)->toBeTrue();
});

it('rejects KPI criteria whose weights do not total one hundred percent', function () {
    $items = validKpiItems();
    $items[1]['weight'] = 30;
    app(KpiTemplateService::class)->validateItems($items);
})->throws(\DomainException::class);

it('rejects an invalid KPI scoring direction', function () {
    $items = validKpiItems();
    $items[0]['scoring_direction'] = 'wrong';
    app(KpiTemplateService::class)->validateItems($items);
})->throws(\DomainException::class);
