<?php

namespace App\Models;

use Database\Factories\BranchFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property string $code
 * @property bool $is_active
 * @property bool $attendance_qr_enabled
 * @property string|null $attendance_qr_token
 */
class Branch extends Model
{
    /** @use HasFactory<BranchFactory> */
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'code',
        'manager_name',
        'address',
        'city',
        'phone',
        'email',
        'is_head_office',
        'is_active',

        'attendance_qr_token',
        'attendance_qr_enabled',
        'latitude',
        'longitude',
        'attendance_radius',
        'qr_last_regenerated_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_head_office' => 'boolean',
            'attendance_qr_enabled' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'attendance_radius' => 'integer',
            'qr_last_regenerated_at' => 'datetime',
        ];
    }

    public function regenerateAttendanceQrToken(): string
    {
        $token = Str::random(64);

        $this->forceFill([
            'attendance_qr_token' => $token,
            'qr_last_regenerated_at' => now(),
        ])->save();

        return $token;
    }

    public function attendanceQrPayload(): string
    {
        $payload = json_encode([
            'type' => 'bizhr_attendance',
            'version' => 1,
            'branch_id' => $this->getKey(),
            'token' => $this->attendance_qr_token,
        ], JSON_UNESCAPED_SLASHES);

        if ($payload === false) {
            throw new \RuntimeException('Unable to encode the attendance QR payload.');
        }

        return $payload;
    }

    /** @return BelongsTo<Company, $this> */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** @return HasMany<Department, $this> */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    /** @return HasMany<Position, $this> */
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    /** @return HasMany<Employee, $this> */
    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
