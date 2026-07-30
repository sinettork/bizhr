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
    public function approve(PayrollPeriod $period, User $approver): PayrollPeriod
    {
        return DB::transaction(function () use ($period, $approver): PayrollPeriod {
            $period = PayrollPeriod::query()->lockForUpdate()->findOrFail($period->id);

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

    public function recordPayment(PayrollPeriod $period, User $recorder, array $data): PayrollPayment
    {
        return DB::transaction(function () use ($period, $recorder, $data): PayrollPayment {
            $period = PayrollPeriod::query()->lockForUpdate()->findOrFail($period->id);

            if ($period->status !== 'approved' || $period->payment()->exists()) {
                throw ValidationException::withMessages(['status' => 'វគ្គនេះបានបើកប្រាក់រួច ឬមិនទាន់បានអនុម័ត។']);
            }

            $items = $period->items()->lockForUpdate()->get();
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
                'reference_number' => $data['reference_number'] ?: null,
                'paid_at' => $paidAt,
                'item_count' => $items->count(),
                'total_usd' => $totalUsd,
                'total_khr' => $totalKhr,
                'recorded_by' => $recorder->id,
                'notes' => $data['notes'] ?: null,
                'checksum' => hash('sha256', $checksumPayload),
            ]);

            $period->items()->update(['payment_status' => 'paid', 'paid_at' => $paidAt]);
            $period->update(['status' => 'paid']);

            return $payment;
        });
    }
}
