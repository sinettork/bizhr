<?php

namespace App\Services;

use App\Models\EmploymentContract;
use App\Models\User;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class EmploymentContractService
{
    public function submit(EmploymentContract $contract, User $actor): EmploymentContract
    {
        if (! in_array($contract->status, ['draft', 'rejected'], true)) {
            throw ValidationException::withMessages(['contract' => 'Only draft or rejected contracts can be submitted.']);
        }

        $this->validateLegalDates($contract);
        $contract->update([
            'status' => 'pending_approval',
            'submitted_by' => $actor->id,
            'submitted_at' => now(),
        ]);

        return $contract->refresh();
    }

    public function approve(EmploymentContract $contract, User $actor): EmploymentContract
    {
        return DB::transaction(function () use ($contract, $actor) {
            $contract = EmploymentContract::query()->lockForUpdate()->findOrFail($contract->id);

            if ($contract->status !== 'pending_approval') {
                throw ValidationException::withMessages(['contract' => 'Only pending contracts can be approved.']);
            }
            if ($contract->submitted_by === $actor->id && ! $actor->hasRole('Super Admin')) {
                throw ValidationException::withMessages(['contract' => 'The submitter cannot approve the same contract.']);
            }

            $this->validateLegalDates($contract);

            $hasActive = EmploymentContract::query()
                ->where('employee_id', $contract->employee_id)
                ->whereIn('status', ['active', 'expiring'])
                ->whereKeyNot($contract->id)
                ->exists();

            if ($hasActive && ! $contract->previous_contract_id) {
                throw ValidationException::withMessages(['employee_id' => 'Employee already has an active contract. Use renewal.']);
            }

            if ($contract->previous_contract_id) {
                EmploymentContract::query()
                    ->whereKey($contract->previous_contract_id)
                    ->whereIn('status', ['active', 'expiring'])
                    ->update(['status' => 'superseded']);
            }

            $contract->update([
                'status' => $this->statusForDates($contract),
                'approved_by' => $actor->id,
                'approved_at' => now(),
                'checksum' => $this->checksum($contract),
            ]);

            return $contract->refresh();
        });
    }

    public function terminate(
        EmploymentContract $contract,
        User $actor,
        string $date,
        string $reason,
    ): EmploymentContract {
        if (! in_array($contract->status, ['active', 'expiring'], true)) {
            throw ValidationException::withMessages(['contract' => 'Only an active contract can be terminated.']);
        }

        $terminationDate = CarbonImmutable::parse($date)->startOfDay();
        if ($terminationDate->lt($contract->start_date)) {
            throw ValidationException::withMessages(['termination_date' => 'Termination cannot be before the contract start date.']);
        }

        $contract->update([
            'status' => 'terminated',
            'termination_date' => $terminationDate,
            'termination_reason' => trim($reason),
            'terminated_by' => $actor->id,
            'terminated_at' => now(),
        ]);

        return $contract->refresh();
    }

    public function validateLegalDates(EmploymentContract $contract): void
    {
        $start = CarbonImmutable::parse($contract->start_date)->startOfDay();

        if ($contract->type === 'fdc') {
            try {
                $this->validateInitialFdcTerm(
                    $contract->start_date,
                    $contract->end_date,
                    filled($contract->document_path),
                );
            } catch (DomainException $exception) {
                throw ValidationException::withMessages([
                    'contract' => $exception->getMessage(),
                ]);
            }

            $end = CarbonImmutable::parse($contract->end_date)->startOfDay();

            $root = $this->rootContract($contract);
            if ($root->id !== $contract->id) {
                $rootStart = CarbonImmutable::parse($root->start_date);
                $rootEnd = CarbonImmutable::parse($root->end_date);
                $maximumEnd = $rootEnd->addYears(2);
                $expectedStart = CarbonImmutable::parse($contract->previousContract?->end_date)->addDay();
                if ($end->gt($maximumEnd) || ! $start->equalTo($expectedStart)) {
                    throw ValidationException::withMessages(['end_date' => 'Renewal must be continuous and the full FDC chain cannot exceed the legal renewal limit.']);
                }
            }
        }

        if ($contract->type === 'probation') {
            if (! $contract->probation_category || ! $contract->probation_end_date) {
                throw ValidationException::withMessages(['probation_end_date' => 'Probation category and end date are required.']);
            }
            $maximum = $this->maximumProbationEnd($start, $contract->probation_category);
            if (CarbonImmutable::parse($contract->probation_end_date)->gt($maximum)) {
                throw ValidationException::withMessages(['probation_end_date' => 'Probation exceeds the legal maximum for this worker category.']);
            }
        }
    }

    public function validateInitialFdcTerm(
        string|\DateTimeInterface $startDate,
        string|\DateTimeInterface|null $endDate,
        bool $hasWrittenDocument,
    ): void {
        if (! $endDate) {
            throw new DomainException(
                'A fixed-duration contract requires a precise end date.'
            );
        }

        if (! $hasWrittenDocument) {
            throw new DomainException(
                'A fixed-duration contract must have a written document.'
            );
        }

        $start = CarbonImmutable::parse($startDate)->startOfDay();
        $end = CarbonImmutable::parse($endDate)->startOfDay();

        if ($end->lte($start) || $end->gt($start->addYears(2))) {
            throw new DomainException(
                'The initial fixed-duration contract must be more than 0 and no more than 2 years.'
            );
        }
    }

    public function maximumProbationEnd(CarbonImmutable $start, string $category): CarbonImmutable
    {
        $months = ['regular' => 3, 'specialized' => 2, 'non_specialized' => 1][$category] ?? 0;
        if ($months === 0) {
            throw ValidationException::withMessages(['probation_category' => 'Invalid probation category.']);
        }

        return $start->addMonthsNoOverflow($months);
    }

    private function rootContract(EmploymentContract $contract): EmploymentContract
    {
        $seen = [];
        while ($contract->previous_contract_id) {
            if (isset($seen[$contract->id])) {
                throw ValidationException::withMessages(['previous_contract_id' => 'Contract renewal chain is invalid.']);
            }
            $seen[$contract->id] = true;
            $contract = $contract->previousContract()->firstOrFail();
        }

        return $contract;
    }

    private function statusForDates(EmploymentContract $contract): string
    {
        if (! $contract->end_date) {
            return 'active';
        }
        $days = now()->startOfDay()->diffInDays($contract->end_date, false);
        return $days < 0 ? 'expired' : ($days <= 30 ? 'expiring' : 'active');
    }

    private function checksum(EmploymentContract $contract): string
    {
        return hash('sha256', implode('|', [
            $contract->contract_number, $contract->employee_id, $contract->type,
            $contract->start_date?->toDateString(), $contract->end_date?->toDateString(),
            $contract->salary_amount, $contract->salary_currency,
        ]));
    }
}
