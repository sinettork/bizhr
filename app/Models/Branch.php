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
    'address',
    'phone',
    'email',
    'is_active',

    'attendance_qr_token',
    'attendance_qr_enabled',
    'latitude',
    'longitude',
    'attendance_radius',
    'qr_last_regenerated_at',
    ];
     protected $casts = [
        'work_date' => 'date',
        'check_in_at' => 'datetime',
        'check_out_at' => 'datetime',
    ];
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'attendance_qr_enabled' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'attendance_radius' => 'integer',
            'qr_last_regenerated_at' => 'datetime',
        ];
    }
    public function regenerateAttendanceQrToken(): string
    {
        $token = \Illuminate\Support\Str::random(64);

        $this->forceFill([
            'attendance_qr_token' => $token,
            'qr_last_regenerated_at' => now(),
        ])->save();

        return $token;
    }
    public function attendanceQrPayload(): string
    {
        return json_encode([
            'type' => 'bizhr_attendance',
            'version' => 1,
            'branch_id' => $this->getKey(),
            'token' => $this->attendance_qr_token,
        ], JSON_UNESCAPED_SLASHES);
    }
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
