<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveBalanceAdjustment extends Model
{
    protected $fillable = ['leave_balance_id', 'previous_days', 'adjustment_days', 'reason', 'adjusted_by'];

    protected function casts(): array
    {
        return ['previous_days' => 'decimal:2', 'adjustment_days' => 'decimal:2'];
    }

    /** @return BelongsTo<LeaveBalance, $this> */
    public function balance(): BelongsTo
    {
        return $this->belongsTo(LeaveBalance::class, 'leave_balance_id');
    }

    /** @return BelongsTo<User, $this> */
    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
