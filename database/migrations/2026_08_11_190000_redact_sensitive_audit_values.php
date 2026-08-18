<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const REDACTED_KEYS = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'attendance_qr_token',
        'check_in_qr_token',
        'check_out_qr_token',
        'national_id',
        'passport_number',
        'bank_account_number',
        'nssf_number',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        DB::table('audit_logs')
            ->select([
                'id',
                'event_uuid',
                'user_id',
                'action',
                'module',
                'record_type',
                'record_id',
                'old_values',
                'new_values',
                'ip_address',
                'user_agent',
                'route',
                'request_id',
            ])
            ->orderBy('id')
            ->chunkById(200, function ($logs): void {
                foreach ($logs as $log) {
                    $oldValues = $this->redact($log->old_values);
                    $newValues = $this->redact($log->new_values);
                    $payload = [
                        'event_uuid' => $log->event_uuid,
                        'user_id' => $log->user_id,
                        'action' => $log->action,
                        'module' => $log->module,
                        'record_type' => $log->record_type,
                        'record_id' => $log->record_id,
                        'old_values' => $oldValues,
                        'new_values' => $newValues,
                        'ip_address' => $log->ip_address,
                        'user_agent' => $log->user_agent,
                        'route' => $log->route,
                        'request_id' => $log->request_id,
                    ];
                    $checksumPayload = json_encode(
                        $payload,
                        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
                    );

                    DB::table('audit_logs')
                        ->where('id', $log->id)
                        ->update([
                            'old_values' => $this->encode($oldValues),
                            'new_values' => $this->encode($newValues),
                            'checksum' => hash_hmac(
                                'sha256',
                                $checksumPayload ?: '',
                                (string) config('app.key'),
                            ),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // តម្លៃសម្ងាត់ដែលបានលុបមិនត្រូវបានស្ដារឡើងវិញទេ។
    }

    /** @return array<string, mixed>|null */
    private function redact(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        $values = is_string($value)
            ? json_decode($value, true)
            : (array) $value;

        if (! is_array($values)) {
            return [];
        }

        foreach (self::REDACTED_KEYS as $key) {
            if (array_key_exists($key, $values) && $values[$key] !== null) {
                $values[$key] = '[REDACTED]';
            }
        }

        return $values;
    }

    /** @param array<string, mixed>|null $values */
    private function encode(?array $values): ?string
    {
        return $values === null
            ? null
            : json_encode(
                $values,
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            );
    }
};
