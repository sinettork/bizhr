<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmploymentContract extends Model
{
    protected $fillable = [
        'company_id', 'employee_id', 'previous_contract_id', 'contract_number',
        'type', 'status', 'start_date', 'end_date', 'signed_at',
        'probation_category', 'probation_end_date', 'position_title',
        'department_name', 'branch_name', 'salary_amount', 'salary_currency',
        'pay_type', 'work_hours_per_day', 'work_days_per_week', 'document_path',
        'original_name', 'renewal_notice_date', 'submitted_by', 'submitted_at',
        'approved_by', 'approved_at', 'terminated_by', 'terminated_at',
        'termination_date', 'termination_reason', 'terms', 'checksum',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'signed_at' => 'date',
            'probation_end_date' => 'date',
            'renewal_notice_date' => 'date',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
            'terminated_at' => 'datetime',
            'termination_date' => 'date',
            'salary_amount' => 'decimal:2',
            'work_hours_per_day' => 'decimal:2',
            'work_days_per_week' => 'decimal:1',
            'terms' => 'array',
        ];
    }

    public function company(): BelongsTo { return $this->belongsTo(Company::class); }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function previousContract(): BelongsTo { return $this->belongsTo(self::class, 'previous_contract_id'); }
    public function renewals(): HasMany { return $this->hasMany(self::class, 'previous_contract_id'); }
    public function submitter(): BelongsTo { return $this->belongsTo(User::class, 'submitted_by'); }
    public function approver(): BelongsTo { return $this->belongsTo(User::class, 'approved_by'); }
    public function terminator(): BelongsTo { return $this->belongsTo(User::class, 'terminated_by'); }
}
