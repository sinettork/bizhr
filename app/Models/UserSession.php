<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'device_type',
        'platform',
        'browser',
        'last_activity_at',
        'is_revoked',
        'revoked_at',
        'revoked_by',
        'revocation_reason',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'last_activity_at' => 'datetime',
            'is_revoked' => 'boolean',
            'revoked_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<User, $this> */
    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    /**
     * Revoke this session
     */
    public function revoke(?int $revokedBy = null, ?string $reason = null): void
    {
        $this->update([
            'is_revoked' => true,
            'revoked_at' => now(),
            'revoked_by' => $revokedBy,
            'revocation_reason' => $reason,
        ]);
    }

    /**
     * Check if session is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if session is active (not revoked and not expired)
     */
    public function isActive(): bool
    {
        return ! $this->is_revoked && ! $this->isExpired();
    }
}
