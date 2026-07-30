<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItem extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'calculation_details' => 'array',
        ];
    }

    public function period(): BelongsTo { return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id'); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
}
