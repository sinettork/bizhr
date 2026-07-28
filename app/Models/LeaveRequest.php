<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use SoftDeletes;
    protected $fillable = ['employee_id', 'leave_type_id', 'start_date', 'end_date', 'total_days', 'reason', 'attachment', 'status', 'manager_id', 'manager_reviewed_at', 'manager_note', 'hr_id', 'hr_reviewed_at', 'hr_note'];
    protected function casts(): array { return ['start_date' => 'date', 'end_date' => 'date', 'total_days' => 'decimal:2', 'manager_reviewed_at' => 'datetime', 'hr_reviewed_at' => 'datetime']; }
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
    public function leaveType(): BelongsTo { return $this->belongsTo(LeaveType::class); }
    public function manager(): BelongsTo { return $this->belongsTo(User::class, 'manager_id'); }
    public function hr(): BelongsTo { return $this->belongsTo(User::class, 'hr_id'); }
}
