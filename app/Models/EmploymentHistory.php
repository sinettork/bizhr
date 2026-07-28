<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmploymentHistory extends Model
{
    protected $fillable = [
        'employee_id', 'branch_id', 'department_id', 'position_id', 'employment_type',
        'event_type', 'effective_date', 'base_salary', 'salary_currency', 'notes', 'recorded_by',
    ];

    protected function casts(): array
    {
        return ['effective_date' => 'date', 'base_salary' => 'decimal:2'];
    }

    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function position(): BelongsTo { return $this->belongsTo(Position::class); }
    public function recordedBy(): BelongsTo { return $this->belongsTo(User::class, 'recorded_by'); }
}
