<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $period_start
 * @property Carbon $period_end
 */
class PerformanceReview extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'employee_id', 'reviewer_id', 'period_start', 'period_end',
        'status', 'overall_score', 'strengths', 'areas_for_improvement',
        'manager_comment', 'employee_comment', 'manager_submitted_at',
        'hr_approved_by', 'hr_approved_at', 'employee_acknowledged_at',
        'closed_by', 'closed_at', 'reopened_by', 'reopened_at', 'reopen_reason',
        'version', 'snapshot_checksum',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date', 'period_end' => 'date',
            'overall_score' => 'decimal:2', 'manager_submitted_at' => 'datetime',
            'hr_approved_at' => 'datetime', 'employee_acknowledged_at' => 'datetime',
            'closed_at' => 'datetime', 'reopened_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return BelongsTo<User, $this> */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /** @return BelongsTo<User, $this> */
    public function hrApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_approved_by');
    }

    /** @return HasMany<PerformanceReviewScore, $this> */
    public function scores(): HasMany
    {
        return $this->hasMany(PerformanceReviewScore::class)->orderBy('sort_order');
    }
}
