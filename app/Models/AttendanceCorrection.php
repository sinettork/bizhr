<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceCorrection extends Model
{
    protected $fillable = [
        'attendance_id',
        'employee_id',
        'requested_check_in',
        'requested_check_out',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_note',
        'reopened_by',
        'reopened_at',
        'reopen_reason',
    ];

    protected $casts = [
        'requested_check_in' => 'datetime',
        'requested_check_out' => 'datetime',
        'reviewed_at' => 'datetime',
        'reopened_at' => 'datetime',
    ];

    /**
     * Get the attendance record being corrected.
     */
    /** @return BelongsTo<Attendance, $this> */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /**
     * Get the employee requesting the correction.
     */
    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who reviewed the correction.
     */
    /** @return BelongsTo<User, $this> */
    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Check if correction is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Approve the correction.
     */
    public function approve(User $reviewer, string $note = ''): void
    {
        $this->update([
            'status' => 'approved',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_note' => $note,
        ]);

        // Update the attendance record with corrected times
        if ($this->requested_check_in) {
            $this->attendance->update(['check_in_at' => $this->requested_check_in]);
        }
        if ($this->requested_check_out) {
            $this->attendance->update(['check_out_at' => $this->requested_check_out]);
        }

        // Recalculate attendance metrics after applying corrections
        $attendance = $this->attendance()->firstOrFail();
        $attendance->recalculateMetrics();
        $attendance->save();
    }

    /**
     * Reject the correction.
     */
    public function reject(User $reviewer, string $note = ''): void
    {
        $this->update([
            'status' => 'rejected',
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_note' => $note,
        ]);
    }

    public function reopen(User $requester, string $reason): void
    {
        $this->update([
            'status' => 'pending',
            'reopened_by' => $requester->id,
            'reopened_at' => now(),
            'reopen_reason' => $reason,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_note' => null,
        ]);
    }
}
