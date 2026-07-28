<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Branch extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'code',
        'email',
        'phone',
        'address',
        'city',
        'manager_name',
        'is_head_office',
        'is_active',
    ];
    protected $casts = [
        'is_head_office' => 'boolean',
        'is_active' => 'boolean',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }
        public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
