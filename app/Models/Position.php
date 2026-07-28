<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    protected $fillable = [
        'company_id',
        'branch_id',
        'department_id',
        'title',
        'code',
        'description',
        'minimum_salary',
        'maximum_salary',
        'is_manager_position',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'minimum_salary' => 'decimal:2',
            'maximum_salary' => 'decimal:2',
            'is_manager_position' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}