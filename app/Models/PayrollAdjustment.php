<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollAdjustment extends Model
{
    protected $fillable = ['payroll_period_id', 'employee_id', 'type', 'name', 'amount', 'currency', 'is_recurring', 'effective_date', 'end_date', 'notes', 'created_by', 'is_fringe_benefit', 'fringe_benefit_tax_rate'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'is_recurring' => 'boolean', 'is_fringe_benefit' => 'boolean', 'fringe_benefit_tax_rate' => 'decimal:2', 'effective_date' => 'date', 'end_date' => 'date'];
    }

    public function period(): BelongsTo { return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id'); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
}
