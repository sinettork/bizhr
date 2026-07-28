<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    protected $fillable = [
        'name',
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
    ];

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }
    public function employmentTypes(): HasMany
    {
        return $this->hasMany(EmploymentType::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function workShifts(): HasMany
    {
        return $this->hasMany(WorkShift::class);
    }

    public function leaveTypes(): HasMany
    {
        return $this->hasMany(LeaveType::class);
    }
}
