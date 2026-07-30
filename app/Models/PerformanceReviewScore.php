<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceReviewScore extends Model
{
    protected $fillable = [
        'employee_goal_id', 'criterion_name', 'criterion_description',
        'measurement_unit', 'target_value', 'actual_value', 'weight',
        'scoring_direction', 'manager_score', 'weighted_score',
        'manager_comment', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:2', 'actual_value' => 'decimal:2',
            'weight' => 'decimal:2', 'weighted_score' => 'decimal:3',
        ];
    }

    public function review(): BelongsTo { return $this->belongsTo(PerformanceReview::class, 'performance_review_id'); }
    public function goal(): BelongsTo { return $this->belongsTo(EmployeeGoal::class, 'employee_goal_id'); }
}
