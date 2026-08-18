<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(
            Employee::class
        );
    }

    /** @return BelongsTo<LeaveType, $this> */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(
            LeaveType::class
        );
    }

    /** @return HasMany<LeaveBalanceAdjustment, $this> */
    public function adjustments(): HasMany
    {
        return $this->hasMany(LeaveBalanceAdjustment::class);
    }
}
