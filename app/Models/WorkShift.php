<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class WorkShift extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'code',
        'start_time',
        'end_time',
        'break_minutes',
        'late_grace_minutes',
        'early_leave_grace_minutes',
        'is_night_shift',
        'is_active',
    ];

    protected $casts = [
        'is_night_shift' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the company that owns the work shift.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the employee schedules for this shift.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(EmployeeSchedule::class);
    }

    /**
     * Get shift duration in minutes (accounting for breaks).
     */
    public function getDurationMinutes(): int
    {
        $start = Carbon::createFromFormat('H:i:s', $this->start_time);
        $end = Carbon::createFromFormat('H:i:s', $this->end_time);

        // Handle night shifts that cross midnight
        if ($this->is_night_shift && $end->lessThan($start)) {
            $end->addDay();
        }

        $totalMinutes = $end->diffInMinutes($start);
        return $totalMinutes - $this->break_minutes;
    }

    /**
     * Get formatted shift time range.
     */
    public function getTimeRangeFormatted(): string
    {
        return sprintf(
            '%s - %s',
            Carbon::createFromFormat('H:i:s', $this->start_time)->format('H:i'),
            Carbon::createFromFormat('H:i:s', $this->end_time)->format('H:i')
        );
    }
}
