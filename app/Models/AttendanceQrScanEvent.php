<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceQrScanEvent extends Model
{
    protected $fillable = [
        'attendance_qr_session_id',
        'employee_id',
        'attendance_id',
        'branch_id',
        'action',
        'latitude',
        'longitude',
        'accuracy_meters',
        'distance_meters',
        'ip_address',
        'user_agent',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'accuracy_meters' => 'float',
            'distance_meters' => 'integer',
            'recorded_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<AttendanceQrSession, $this> */
    public function session(): BelongsTo
    {
        return $this->belongsTo(AttendanceQrSession::class, 'attendance_qr_session_id');
    }

    /** @return BelongsTo<Employee, $this> */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return BelongsTo<Attendance, $this> */
    public function attendance(): BelongsTo
    {
        return $this->belongsTo(Attendance::class);
    }

    /** @return BelongsTo<Branch, $this> */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
