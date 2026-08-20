<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EmployeeIdCardVerificationService
{
    public function tokenFor(Employee $employee): string
    {
        $generation = max(1, (int) ($employee->id_card_verification_generation ?: 1));
        $token = $this->deriveToken($employee, $generation);
        $expiresAt = $employee->id_card_expiry_date?->copy()->endOfDay() ?? now()->addYear();
        $expectedHash = hash('sha256', $token);

        if (
            $employee->id_card_verification_token_hash !== $expectedHash
            || (int) $employee->id_card_verification_generation !== $generation
            || $employee->id_card_verification_expires_at?->ne($expiresAt)
        ) {
            $employee->forceFill([
                'id_card_verification_generation' => $generation,
                'id_card_verification_token_hash' => $expectedHash,
                'id_card_verification_expires_at' => $expiresAt,
            ])->save();
        }

        return $token;
    }

    public function isValid(Employee $employee, string $token): bool
    {
        if (
            ! $employee->is_active
            || $employee->id_card_verification_revoked_at !== null
            || ! $employee->id_card_verification_expires_at?->isFuture()
        ) {
            return false;
        }

        $generation = max(1, (int) ($employee->id_card_verification_generation ?: 1));
        $expectedToken = $this->deriveToken($employee, $generation);
        $expectedHash = hash('sha256', $expectedToken);

        return hash_equals($expectedToken, $token)
            && is_string($employee->id_card_verification_token_hash)
            && hash_equals($expectedHash, $employee->id_card_verification_token_hash);
    }

    public function revoke(Employee $employee, User $actor): Employee
    {
        return DB::transaction(function () use ($employee, $actor): Employee {
            $locked = Employee::query()->lockForUpdate()->findOrFail($employee->id);
            $this->assertActorCompany($locked, $actor);

            if ($locked->id_card_verification_revoked_at === null) {
                $locked->update(['id_card_verification_revoked_at' => now()]);
                AuditLog::record($locked, 'id_card_verification_revoked', [], [
                    'employee_id' => $locked->id,
                    'generation' => (int) $locked->id_card_verification_generation,
                    'actor_id' => $actor->id,
                ]);
            }

            return $locked->refresh();
        });
    }

    public function regenerate(Employee $employee, User $actor): Employee
    {
        return DB::transaction(function () use ($employee, $actor): Employee {
            $locked = Employee::query()->lockForUpdate()->findOrFail($employee->id);
            $this->assertActorCompany($locked, $actor);

            $generation = max(1, (int) ($locked->id_card_verification_generation ?: 1)) + 1;
            $token = $this->deriveToken($locked, $generation);
            $expiresAt = $locked->id_card_expiry_date?->copy()->endOfDay() ?? now()->addYear();

            $locked->update([
                'id_card_verification_generation' => $generation,
                'id_card_verification_token_hash' => hash('sha256', $token),
                'id_card_verification_expires_at' => $expiresAt,
                'id_card_verification_revoked_at' => null,
            ]);

            AuditLog::record($locked, 'id_card_verification_regenerated', [], [
                'employee_id' => $locked->id,
                'generation' => $generation,
                'actor_id' => $actor->id,
            ]);

            return $locked->refresh();
        });
    }

    private function deriveToken(Employee $employee, int $generation): string
    {
        $key = (string) config('app.key');
        if ($key === '') {
            throw new RuntimeException('APP_KEY is required for employee ID-card verification.');
        }

        return hash_hmac('sha256', $employee->public_id.'|'.$generation, $key);
    }

    private function assertActorCompany(Employee $employee, User $actor): void
    {
        if ($actor->companyId() !== (int) $employee->company_id) {
            throw new RuntimeException('The employee does not belong to the actor company.');
        }
    }
}
