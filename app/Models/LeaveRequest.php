<?php

namespace App\Models;

use App\Models\Concerns\HasPublicId;
use Database\Factories\LeaveRequestFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $start_date
 * @property Carbon $end_date
 */
class LeaveRequest extends Model
{
    /** @use HasFactory<LeaveRequestFactory> */
    use HasFactory, HasPublicId, SoftDeletes;

    protected $fillable = ['employee_id', 'leave_type_id', 'start_date', 'end_date', 'total_days', 'reason', 'attachment', 'status', 'manager_id', 'manager_reviewed_at', 'manager_note', 'hr_id', 'hr_reviewed_at', 'hr_note'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'end_date' => 'date', 'total_days' => 'decimal:2', 'manager_reviewed_at' => 'datetime', 'hr_reviewed_at' => 'datetime'];
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return BelongsTo<LeaveType, $this> */
    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    /** @return BelongsTo<User, $this> */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /** @return BelongsTo<User, $this> */
    public function hr(): BelongsTo
    {
        return $this->belongsTo(User::class, 'hr_id');
    }
}
