<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSchedule extends Model
{
    protected $fillable = [
        'employee_id',
        'branch_id',
        'work_shift_id',
        'work_date',
        'is_rest_day',
        'notes',
    ];

    protected $casts = [
        'work_date' => 'date',
        'is_rest_day' => 'boolean',
    ];

    /**
     * Get the employee who has this schedule.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the branch where the schedule applies.
     */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the work shift assigned to this schedule.
     */
    public function workShift(): BelongsTo
    {
        return $this->belongsTo(WorkShift::class);
    }
}
