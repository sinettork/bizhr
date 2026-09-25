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
 * @property int $id_card_verification_generation
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
        'id_card_verification_generation',
        'rehire_requested_at', 'rehire_requested_by', 'rehire_effective_date', 'rehire_reason', 'rehire_approved_at', 'rehire_approved_by',
        'employment_status',
        'is_active',
        'marital_status',
        'spouse_name',
        'spouse_work_status',
        'tin_number',
        'tax_dependents',
        'nssf_number',
        'nssf_enrolled',
        'is_tax_resident',
        'reports_to_id',
        'termination_date',
        'termination_reason',
        'turnover_type',
        'work_email',
        'current_address',
        'default_shift_id',
        'seniority_date',
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
            'id_card_verification_generation' => 'integer',
            'rehire_requested_at' => 'datetime',
            'rehire_effective_date' => 'date',
            'rehire_approved_at' => 'datetime',
            'base_salary' => 'decimal:2',
            'is_active' => 'boolean',
            'termination_date' => 'date',
            'seniority_date' => 'date',
            'nssf_enrolled' => 'boolean',
            'is_tax_resident' => 'boolean',
            'tax_dependents' => 'integer',
        ];
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /** @return BelongsTo<Department, $this> */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** @return BelongsTo<Position, $this> */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /** @return BelongsTo<EmploymentType, $this> */
    public function employmentType(): BelongsTo
    {
        return $this->belongsTo(EmploymentType::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<EmployeeSchedule, $this> */
    public function schedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class);
    }

    /** @return HasMany<Attendance, $this> */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

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

    /** @return BelongsTo<Employee, $this> */
    public function reportsTo(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reports_to_id');
    }

    /** @return HasMany<Employee, $this> */
    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'reports_to_id');
    }

    /** @return BelongsTo<WorkShift, $this> */
    public function defaultShift(): BelongsTo
    {
        return $this->belongsTo(WorkShift::class, 'default_shift_id');
    }

    public function getFullName(): string
    {
        if ($this->full_name_en) {
            return $this->full_name_en;
        }

        return "{$this->first_name} {$this->last_name}";
    }

    public function getFullNameKm(): string
    {
        return $this->full_name_km ?? $this->getFullName();
    }

    /** @param Builder<Employee> $query */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /** @param Builder<Employee> $query */
    public function scopeByStatus(Builder $query, string $status): void
    {
        $query->where('employment_status', $status);
    }

    /** @param Builder<Employee> $query */
    public function scopeByBranch(Builder $query, int $branchId): void
    {
        $query->where('branch_id', $branchId);
    }

    /** @param Builder<Employee> $query */
    public function scopeByDepartment(Builder $query, int $departmentId): void
    {
        $query->where('department_id', $departmentId);
    }
}
