<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'branch_id',
        'department_id',
        'position_id',
        'employment_type_id',
        'user_id',
        'employee_code',
        'first_name',
        'last_name',
        'full_name_km',
        'full_name_en',
        'gender',
        'date_of_birth',
        'phone',
        'email',
        'national_id',
        'passport_number',
        'address',
        'city',
        'profile_photo',
        'hire_date',
        'probation_end_date',
        'contract_start_date',
        'contract_end_date',
        'base_salary',
        'salary_currency',
        'payment_method',
        'bank_name',
        'bank_account_name',
        'bank_account_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'employment_status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'hire_date' => 'date',
            'probation_end_date' => 'date',
            'contract_start_date' => 'date',
            'contract_end_date' => 'date',
            'base_salary' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the company this employee belongs to
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch this employee is assigned to
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the department this employee belongs to
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the position this employee holds
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the employment type
     */
    public function employmentType(): BelongsTo
    {
        return $this->belongsTo(EmploymentType::class);
    }

    /**
     * Get the user account associated with this employee
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the employee's work schedules
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class);
    }

    /**
     * Get the employee's attendance records
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the employee's attendance corrections
     */
    public function attendanceCorrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function employmentHistories(): HasMany
    {
        return $this->hasMany(EmploymentHistory::class);
    }

    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    /**
     * Get the full name in English (or Khmer if available)
     */
    public function getFullName(): string
    {
        if ($this->full_name_en) {
            return $this->full_name_en;
        }

        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the full Khmer name
     */
    public function getFullNameKm(): string
    {
        return $this->full_name_km ?? $this->getFullName();
    }

    /**
     * Scope to get active employees
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get employees by status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('employment_status', $status);
    }

    /**
     * Scope to get employees by branch
     */
    public function scopeByBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to get employees by department
     */
    public function scopeByDepartment($query, int $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }
}
