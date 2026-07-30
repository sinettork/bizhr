<?php

namespace App\Services;

use App\Models\ExpenseClaim;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class ExpenseWorkflowService
{
    public function managerReview(ExpenseClaim $claim, User $actor, bool $approved, string $note): ExpenseClaim
    {
        return DB::transaction(function () use ($claim, $actor, $approved, $note) {
            $claim = ExpenseClaim::query()->lockForUpdate()->findOrFail($claim->id);
            if ($claim->status !== 'pending_manager') throw new DomainException('Claim is not waiting for manager review.');
            if ($actor->employee?->id === $claim->employee_id) throw new DomainException('Employees cannot approve their own expense.');
            $claim->update([
                'status' => $approved ? 'pending_accounting' : 'rejected',
                'manager_id' => $actor->id, 'manager_reviewed_at' => now(),
                'review_note' => trim($note),
            ]);
            return $claim->refresh();
        });
    }

    public function accountingReview(ExpenseClaim $claim, User $actor, bool $approved, string $note): ExpenseClaim
    {
        return DB::transaction(function () use ($claim, $actor, $approved, $note) {
            $claim = ExpenseClaim::query()->lockForUpdate()->findOrFail($claim->id);
            if ($claim->status !== 'pending_accounting') throw new DomainException('Claim is not waiting for accounting.');
            if ($actor->employee?->id === $claim->employee_id) throw new DomainException('Employees cannot approve their own expense.');
            $claim->update([
                'status' => $approved ? 'approved' : 'rejected',
                'accountant_id' => $actor->id, 'accountant_reviewed_at' => now(),
                'review_note' => trim($note),
            ]);
            return $claim->refresh();
        });
    }

    public function markPaid(ExpenseClaim $claim, User $actor, string $reference): ExpenseClaim
    {
        if ($claim->status !== 'approved' || mb_strlen(trim($reference)) < 3) {
            throw new DomainException('Only an approved claim with a payment reference can be paid.');
        }
        $claim->update(['status' => 'paid', 'paid_at' => now(), 'accountant_id' => $actor->id, 'payment_reference' => trim($reference)]);
        return $claim->refresh();
    }
}
