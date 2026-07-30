<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use LogicException;

class AuditLog extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Audit logs are append-only.'));
        static::deleting(fn () => throw new LogicException('Audit logs cannot be deleted.'));
    }

    public static function record(Model $record, string $action, array $oldValues, array $newValues): self
    {
        $eventUuid = (string) Str::uuid();
        $payload = [
            'event_uuid' => $eventUuid,
            'user_id' => auth()->id(),
            'action' => $action,
            'module' => Str::snake(Str::pluralStudly(class_basename($record))),
            'record_type' => $record::class,
            'record_id' => (string) $record->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'route' => request()?->route()?->getName(),
            'request_id' => request()?->headers->get('X-Request-ID'),
        ];

        $checksumPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $payload['checksum'] = hash_hmac('sha256', $checksumPayload ?: '', (string) config('app.key'));

        return static::query()->create($payload);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
