<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KpiTemplateItem extends Model
{
    protected $fillable = [
        'name', 'description', 'measurement_unit', 'target_value',
        'weight', 'scoring_direction', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:2',
            'weight' => 'decimal:2',
        ];
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(KpiTemplate::class, 'kpi_template_id');
    }
}
