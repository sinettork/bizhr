<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $hire_date
 * @property Carbon|null $id_card_expiry_date
 * @property Carbon|null $id_card_verification_expires_at
 * @property Carbon|null $id_card_verification_revoked_at
 * @property Carbon|null $rehire_effective_date
 * @property int $branch_id
 * @property string $public_id
 */
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory, HasPublicId, SoftDeletes;

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
        'id_card_expiry_date',
        'id_card_verification_token_hash',
        'id_card_verification_expires_at',
        'id_card_verification_revoked_at',
        'rehire_requested_at', 'rehire_requested_by', 'rehire_effective_date', 'rehire_reason', 'rehire_approved_at', 'rehire_approved_by',
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
            'id_card_expiry_date' => 'date',
            'id_card_verification_expires_at' => 'datetime',
            'id_card_verification_revoked_at' => 'datetime',
            'rehire_requested_at' => 'datetime',
            'rehire_effective_date' => 'date',
            'rehire_approved_at' => 'datetime',
            'base_salary' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the company this employee belongs to
     */
    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the branch this employee is assigned to
     */
    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the department this employee belongs to
     */
    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the position this employee holds
     */
    /** @return BelongsTo<Position, $this> */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the employment type
     */
    /** @return BelongsTo<EmploymentType, $this> */
    public function employmentType(): BelongsTo
    {
        return $this->belongsTo(EmploymentType::class);
    }

    /**
     * Get the user account associated with this employee
     */
    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the employee's work schedules
     */
    /** @return HasMany<EmployeeSchedule, $this> */
    public function schedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class);
    }

    /**
     * Get the employee's attendance records
     */
    /** @return HasMany<Attendance, $this> */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the employee's attendance corrections
     */
    /** @return HasMany<AttendanceCorrection, $this> */
    public function attendanceCorrections(): HasMany
    {
        return $this->hasMany(AttendanceCorrection::class);
    }

    /** @return HasMany<EmployeeDocument, $this> */
    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    /** @return HasMany<EmploymentHistory, $this> */
    public function employmentHistories(): HasMany
    {
        return $this->hasMany(EmploymentHistory::class);
    }

    /** @return HasMany<LeaveBalance, $this> */
    public function leaveBalances(): HasMany
    {
        return $this->hasMany(LeaveBalance::class);
    }

    /** @return HasMany<LeaveRequest, $this> */
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
    /** @param Builder<Employee> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Scope to get employees by status
     */
    /** @param Builder<Employee> $query */
    public function scopeByStatus(Builder $query, string $status): void
    {
        $query->where('employment_status', $status);
    }

    /**
     * Scope to get employees by branch
     */
    /** @param Builder<Employee> $query */
    public function scopeByBranch(Builder $query, int $branchId): void
    {
        $query->where('branch_id', $branchId);
    }

    /**
     * Scope to get employees by department
     */
    /** @param Builder<Employee> $query */
    public function scopeByDepartment(Builder $query, int $departmentId): void
    {
        $query->where('department_id', $departmentId);
    }
}
