<?php

namespace App\Services;

use App\Models\KpiTemplate;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class KpiTemplateService
{
    public function save(array $templateData, array $items, User $actor, ?KpiTemplate $template = null): KpiTemplate
    {
        $this->validateItems($items);

        return DB::transaction(function () use ($templateData, $items, $actor, $template) {
            $template ??= new KpiTemplate();
            $template->fill($templateData);
            $template->created_by ??= $actor->id;
            $template->save();

            $template->items()->delete();
            foreach (array_values($items) as $index => $item) {
                $template->items()->create([
                    'name' => trim($item['name']),
                    'description' => trim($item['description'] ?? ''),
                    'measurement_unit' => $item['measurement_unit'],
                    'target_value' => $item['target_value'],
                    'weight' => $item['weight'],
                    'scoring_direction' => $item['scoring_direction'],
                    'sort_order' => $index + 1,
                ]);
            }

            return $template->load(['items', 'position']);
        });
    }

    public function validateItems(array $items): void
    {
        if ($items === []) {
            throw new DomainException('KPI template must contain at least one criterion.');
        }

        $totalWeight = 0.0;
        foreach ($items as $index => $item) {
            if (trim((string) ($item['name'] ?? '')) === '') {
                throw new DomainException('Every KPI criterion requires a name.');
            }
            if (! in_array($item['measurement_unit'] ?? null, ['number', 'percent', 'currency_usd', 'currency_khr', 'days', 'hours', 'score'], true)) {
                throw new DomainException('A KPI criterion has an invalid measurement unit.');
            }
            if (! in_array($item['scoring_direction'] ?? null, ['higher_is_better', 'lower_is_better', 'target_is_best'], true)) {
                throw new DomainException('A KPI criterion has an invalid scoring direction.');
            }
            if (! is_numeric($item['target_value'] ?? null) || (float) $item['target_value'] < 0) {
                throw new DomainException('Every KPI target must be zero or greater.');
            }
            if (! is_numeric($item['weight'] ?? null) || (float) $item['weight'] <= 0 || (float) $item['weight'] > 100) {
                throw new DomainException('Every KPI weight must be greater than 0 and no more than 100.');
            }
            $totalWeight += (float) $item['weight'];
        }

        if (abs($totalWeight - 100.0) > 0.001) {
            throw new DomainException('The total KPI weight must equal exactly 100%.');
        }
    }
}
