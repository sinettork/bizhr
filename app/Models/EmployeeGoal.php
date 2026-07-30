<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeGoal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'employee_id', 'kpi_template_item_id', 'title', 'description',
        'measurement_unit', 'target_value', 'current_value', 'employee_reported_value',
        'weight', 'scoring_direction', 'start_date', 'due_date', 'status',
        'employee_note', 'manager_note', 'assigned_by', 'activated_at',
        'submitted_at', 'reviewed_by', 'reviewed_at', 'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'target_value' => 'decimal:2', 'current_value' => 'decimal:2',
            'employee_reported_value' => 'decimal:2', 'weight' => 'decimal:2',
            'start_date' => 'date', 'due_date' => 'date', 'activated_at' => 'datetime',
            'submitted_at' => 'datetime', 'reviewed_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function templateItem(): BelongsTo { return $this->belongsTo(KpiTemplateItem::class, 'kpi_template_item_id'); }
    public function assigner(): BelongsTo { return $this->belongsTo(User::class, 'assigned_by'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
}
