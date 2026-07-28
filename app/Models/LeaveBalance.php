<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalance extends Model
{
    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'year',
        'opening_balance',
        'earned_days',
        'used_days',
        'adjustment_days',
        'remaining_days',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',

            'opening_balance' => 'decimal:2',
            'earned_days' => 'decimal:2',
            'used_days' => 'decimal:2',
            'adjustment_days' => 'decimal:2',
            'remaining_days' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class
        );
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(
            LeaveType::class
        );
    }
}