<?php

namespace App\Services;

use App\Models\Employee;
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
            $this->assertSameCompany($claim, $actor);
            if ($claim->status !== 'pending_manager') {
                throw new DomainException('Claim is not waiting for manager review.');
            }
            if ($this->actorEmployeeId($actor) === (int) $claim->employee_id) {
                throw new DomainException('Employees cannot approve their own expense.');
            }
            $claim->update([
                'status' => $approved ? 'pending_accounting' : 'rejected',
                'manager_id' => $actor->id,
                'manager_reviewed_at' => now(),
                'review_note' => trim($note),
            ]);

            return $claim->refresh();
        });
    }

    public function accountingReview(ExpenseClaim $claim, User $actor, bool $approved, string $note): ExpenseClaim
    {
        return DB::transaction(function () use ($claim, $actor, $approved, $note) {
            $claim = ExpenseClaim::query()->lockForUpdate()->findOrFail($claim->id);
            $this->assertSameCompany($claim, $actor);
            if ($claim->status !== 'pending_accounting') {
                throw new DomainException('Claim is not waiting for accounting.');
            }
            if ($this->actorEmployeeId($actor) === (int) $claim->employee_id) {
                throw new DomainException('Employees cannot approve their own expense.');
            }
            $claim->update([
                'status' => $approved ? 'approved' : 'rejected',
                'accountant_id' => $actor->id,
                'accountant_reviewed_at' => now(),
                'review_note' => trim($note),
            ]);

            return $claim->refresh();
        });
    }

    public function markPaid(ExpenseClaim $claim, User $actor, string $reference): ExpenseClaim
    {
        if (mb_strlen(trim($reference)) < 3) {
            throw new DomainException('Only an approved claim with a payment reference can be paid.');
        }

        return DB::transaction(function () use ($claim, $actor, $reference) {
            $claim = ExpenseClaim::query()->lockForUpdate()->findOrFail($claim->id);
            $this->assertSameCompany($claim, $actor);
            if ($claim->status !== 'approved') {
                throw new DomainException('Only an approved claim with a payment reference can be paid.');
            }
            $claim->update([
                'status' => 'paid',
                'paid_at' => now(),
                'accountant_id' => $actor->id,
                'payment_reference' => trim($reference),
            ]);

            return $claim->refresh();
        });
    }

    private function assertSameCompany(ExpenseClaim $claim, User $actor): void
    {
        $actorCompanyId = $actor->companyId();
        $employeeCompanyId = Employee::query()->whereKey($claim->employee_id)->value('company_id');

        if (
            $actorCompanyId === null
            || (int) $claim->company_id !== $actorCompanyId
            || $employeeCompanyId === null
            || (int) $employeeCompanyId !== (int) $claim->company_id
        ) {
            throw new DomainException('The expense claim does not belong to the actor company.');
        }
    }

    private function actorEmployeeId(User $actor): ?int
    {
        $companyId = $actor->companyId();
        if ($companyId === null) {
            return null;
        }

        $employeeId = Employee::query()
            ->where('company_id', $companyId)
            ->where('user_id', $actor->id)
            ->value('id');

        return $employeeId === null ? null : (int) $employeeId;
    }
}
