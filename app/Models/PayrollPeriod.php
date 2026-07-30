<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PayrollPeriod extends Model
{
    protected $fillable = ['company_id', 'name', 'start_date', 'end_date', 'payment_date', 'status', 'processed_by', 'processed_at', 'approved_by', 'approved_at', 'notes', 'tax_exchange_rate_khr', 'tax_rate_date', 'tax_rate_source', 'tax_rate_locked_at'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'payment_date' => 'date', 'tax_rate_date' => 'date', 'processed_at' => 'datetime', 'approved_at' => 'datetime', 'tax_rate_locked_at' => 'datetime'];
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function items(): HasMany { return $this->hasMany(PayrollItem::class); }
    public function adjustments(): HasMany { return $this->hasMany(PayrollAdjustment::class); }
    public function payment(): HasOne { return $this->hasOne(PayrollPayment::class); }
    public function processor(): BelongsTo { return $this->belongsTo(User::class, 'processed_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
}
