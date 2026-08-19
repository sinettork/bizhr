<?php

namespace App\Services;

use App\Models\PayrollPayment;
use App\Models\PayrollPeriod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PayrollWorkflowService
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function approve(PayrollPeriod $period, User $approver): PayrollPeriod
    {
        return DB::transaction(function () use ($period, $approver): PayrollPeriod {
            $period = PayrollPeriod::query()->lockForUpdate()->findOrFail($period->id);
            $this->assertSameCompany($period, $approver);

            if ($period->status !== 'awaiting_approval') {
                throw ValidationException::withMessages(['status' => 'វគ្គប្រាក់ខែនេះមិនស្ថិតនៅស្ថានភាពរង់ចាំអនុម័តទេ។']);
            }

            if ($period->processed_by === $approver->id && ! $approver->hasRole('Super Admin')) {
                throw ValidationException::withMessages(['status' => 'អ្នកគណនាប្រាក់ខែមិនអាចអនុម័តការងាររបស់ខ្លួនឯងបានទេ។']);
            }

            if (! $period->items()->exists()) {
                throw ValidationException::withMessages(['status' => 'វគ្គនេះមិនមានបញ្ជីប្រាក់ខែសម្រាប់អនុម័តទេ។']);
            }

            if ($period->items()->where('exception_count', '>', 0)->exists()) {
                throw ValidationException::withMessages(['status' => 'សូមដោះស្រាយបញ្ហាគណនាទាំងអស់មុនអនុម័ត។']);
            }

            $period->update([
                'status' => 'approved',
                'approved_by' => $approver->id,
                'approved_at' => now(),
            ]);

            return $period->fresh();
        });
    }

    /** @param array<string, mixed> $data */
    public function recordPayment(PayrollPeriod $period, User $recorder, array $data): PayrollPayment
    {
        return DB::transaction(function () use ($period, $recorder, $data): PayrollPayment {
            $period = PayrollPeriod::query()->lockForUpdate()->findOrFail($period->id);
            $this->assertSameCompany($period, $recorder);

            if ($period->status !== 'approved' || $period->payment()->exists()) {
                throw ValidationException::withMessages(['status' => 'វគ្គនេះបានបើកប្រាក់រួច ឬមិនទាន់បានអនុម័ត។']);
            }

            $items = $period->items()
                ->with('employee:id,company_id,user_id')
                ->lockForUpdate()
                ->get();
            if ($items->isEmpty() || $items->contains(fn ($item) => $item->payment_status === 'paid')) {
                throw ValidationException::withMessages(['status' => 'ទិន្នន័យបើកប្រាក់មិនត្រឹមត្រូវ ឬបានកត់ត្រារួច។']);
            }

            $totalUsd = round((float) $items->where('currency', 'USD')->sum('net_salary'), 2);
            $totalKhr = round((float) $items->where('currency', 'KHR')->sum('net_salary'), 2);
            $paidAt = Carbon::parse($data['paid_at']);
            $checksumPayload = implode('|', [
                $period->id,
                $items->count(),
                number_format($totalUsd, 2, '.', ''),
                number_format($totalKhr, 2, '.', ''),
                $data['payment_method'],
                $data['reference_number'] ?? '',
                $paidAt->toIso8601String(),
            ]);

            $payment = PayrollPayment::query()->create([
                'payroll_period_id' => $period->id,
                'payment_method' => $data['payment_method'],
                'reference_number' => filled($data['reference_number'] ?? null) ? $data['reference_number'] : null,
                'paid_at' => $paidAt,
                'item_count' => $items->count(),
                'total_usd' => $totalUsd,
                'total_khr' => $totalKhr,
                'recorded_by' => $recorder->id,
                'notes' => filled($data['notes'] ?? null) ? $data['notes'] : null,
                'checksum' => hash('sha256', $checksumPayload),
            ]);

            $period->items()->update(['payment_status' => 'paid', 'paid_at' => $paidAt]);
            $period->update(['status' => 'paid']);

            foreach ($items as $item) {
                $employee = $item->employee;
                if ($employee === null || $employee->user_id === null || (int) $employee->company_id !== (int) $period->company_id) {
                    continue;
                }

                $this->notifications->notify(
                    (int) $employee->user_id,
                    'payroll_paid',
                    'Payroll paid',
                    "Your payroll for {$period->name} has been marked paid. Net pay: ".number_format((float) $item->net_salary, 2).' '.$item->currency.'.',
                    '/my-payroll',
                    'money-check-dollar',
                    'success',
                    ['payroll_period_id' => (string) $period->getRouteKey()],
                    (int) $period->company_id,
                );
            }

            return $payment;
        });
    }

    public function close(PayrollPeriod $period, User $actor, string $reason): PayrollPeriod
    {
        return DB::transaction(function () use ($period, $actor, $reason): PayrollPeriod {
            $period = PayrollPeriod::query()->lockForUpdate()->findOrFail($period->id);
            $this->assertSameCompany($period, $actor);

            if (! $actor->hasAnyRole(['Payroll Officer', 'HR Administrator', 'Owner', 'Super Admin'])) {
                abort(403);
            }

            if ($period->status !== 'paid' || ! $period->payment()->exists()) {
                throw ValidationException::withMessages([
                    'status' => 'Only a paid payroll period with a recorded payment can be closed.',
                ]);
            }

            $period->update([
                'status' => 'closed',
                'closed_by' => $actor->id,
                'closed_at' => now(),
                'close_reason' => trim($reason),
                'reopened_by' => null,
                'reopened_at' => null,
                'reopen_reason' => null,
            ]);

            return $period->fresh();
        });
    }

    public function reopen(PayrollPeriod $period, User $actor, string $reason): PayrollPeriod
    {
        return DB::transaction(function () use ($period, $actor, $reason): PayrollPeriod {
            $period = PayrollPeriod::query()->lockForUpdate()->findOrFail($period->id);
            $this->assertSameCompany($period, $actor);

            if (! $actor->hasAnyRole(['Owner', 'Super Admin'])) {
                abort(403);
            }

            if ($period->status !== 'closed' || ! $period->payment()->exists()) {
                throw ValidationException::withMessages([
                    'status' => 'Only a closed payroll period with its original payment record can be reopened.',
                ]);
            }

            $period->update([
                'status' => 'paid',
                'reopened_by' => $actor->id,
                'reopened_at' => now(),
                'reopen_reason' => trim($reason),
            ]);

            return $period->fresh();
        });
    }

    private function assertSameCompany(PayrollPeriod $period, User $actor): void
    {
        if ($actor->companyId() !== (int) $period->company_id) {
            throw ValidationException::withMessages(['status' => 'The payroll period does not belong to the actor company.']);
        }
    }
}
