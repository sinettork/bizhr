<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollSetting extends Model
{
    protected $fillable = [
        'company_id',
        'khr_per_usd',
        'working_days_per_month',
        'hours_per_day',
        'default_overtime_multiplier',
        'require_overtime_approval',
        'deduct_unpaid_absence',
        'salary_tax_enabled',
        'dependent_relief_khr',
        'nssf_employee_health_rate',
        'nssf_employer_health_rate',
        'nssf_employer_risk_rate',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'khr_per_usd' => 'decimal:2',
            'hours_per_day' => 'decimal:2',
            'default_overtime_multiplier' => 'decimal:2',
            'require_overtime_approval' => 'boolean',
            'deduct_unpaid_absence' => 'boolean',
            'salary_tax_enabled' => 'boolean',
            'nssf_employee_health_rate' => 'decimal:2',
            'nssf_employer_health_rate' => 'decimal:2',
            'nssf_employer_risk_rate' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function forCompany(int $companyId): self
    {
        return static::query()->firstOrCreate(['company_id' => $companyId]);
    }
}
