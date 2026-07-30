<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id', 'assigned_by', 'assigned_to', 'title', 'description',
        'priority', 'start_date', 'due_date', 'status', 'progress',
        'employee_note', 'manager_note', 'submitted_at', 'completed_at',
        'verified_by', 'verified_at', 'cancelled_at', 'cancellation_reason',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date', 'due_date' => 'date', 'submitted_at' => 'datetime',
            'completed_at' => 'datetime', 'verified_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class, 'assigned_to'); }
    public function assigner(): BelongsTo { return $this->belongsTo(User::class, 'assigned_by'); }
    public function verifier(): BelongsTo { return $this->belongsTo(User::class, 'verified_by'); }

    public function getEffectiveStatusAttribute(): string
    {
        return ! in_array($this->status, ['completed', 'verified', 'cancelled'], true)
            && $this->due_date->isPast() ? 'overdue' : $this->status;
    }
}
