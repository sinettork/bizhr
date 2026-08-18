<?php

namespace App\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'legal_name',
        'local_name',
        'logo_path',
        'email',
        'phone',
        'website',
        'registration_number',
        'tax_id',
        'address',
        'city',
        'country',
        'currency',
        'timezone',
        'date_format',
        'fiscal_year_start_month',
        'week_start_day',
        'number_format',
        'locale',
    ];

    /** @return HasMany<Branch, $this> */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    /** @return HasMany<Department, $this> */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /** @return HasMany<Position, $this> */
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    /** @return HasMany<EmploymentType, $this> */
    public function employmentTypes(): HasMany
    {
        return $this->hasMany(EmploymentType::class);
    }

    /** @return HasMany<Employee, $this> */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    /** @return HasMany<WorkShift, $this> */
    public function workShifts(): HasMany
    {
        return $this->hasMany(WorkShift::class);
    }

    /** @return HasMany<LeaveType, $this> */
    public function leaveTypes(): HasMany
    {
        return $this->hasMany(LeaveType::class);
    }
}
