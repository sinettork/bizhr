<?php

namespace App\Models;

use Database\Factories\LeaveTypeFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    /** @use HasFactory<LeaveTypeFactory> */
    use HasFactory;

    protected $fillable = ['company_id', 'name', 'code', 'days_per_year', 'is_statutory_annual_leave', 'is_paid', 'requires_attachment', 'carry_forward_allowed', 'maximum_carry_forward_days', 'is_active'];

    protected function casts(): array
    {
        return ['days_per_year' => 'decimal:2', 'maximum_carry_forward_days' => 'decimal:2', 'is_statutory_annual_leave' => 'boolean', 'is_paid' => 'boolean', 'requires_attachment' => 'boolean', 'carry_forward_allowed' => 'boolean', 'is_active' => 'boolean'];
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return HasMany<LeaveBalance, $this> */
    public function balances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /** @return HasMany<LeaveRequest, $this> */
    public function requests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
