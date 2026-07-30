<?php

namespace App\Services;

use App\Models\EmployeeGoal;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class EmployeeGoalService
{
    public function submitProgress(EmployeeGoal $goal, User $actor, float $value, ?string $note): EmployeeGoal
    {
        if ($actor->employee?->id !== $goal->employee_id) {
            throw new DomainException('You can submit progress only for your own goal.');
        }
        if (! in_array($goal->status, ['active', 'returned'], true)) {
            throw new DomainException('Only an active or returned goal can be submitted.');
        }
        if ($value < 0) {
            throw new DomainException('Reported progress cannot be negative.');
        }

        $goal->update([
            'employee_reported_value' => $value,
            'employee_note' => trim((string) $note) ?: null,
            'status' => 'pending_review',
            'submitted_at' => now(),
        ]);

        return $goal->refresh();
    }

    public function review(EmployeeGoal $goal, User $reviewer, bool $approved, ?string $note): EmployeeGoal
    {
        return DB::transaction(function () use ($goal, $reviewer, $approved, $note) {
            $goal = EmployeeGoal::query()->lockForUpdate()->findOrFail($goal->id);
            if ($goal->status !== 'pending_review') {
                throw new DomainException('Only submitted progress can be reviewed.');
            }
            if ($reviewer->employee?->id === $goal->employee_id && ! $reviewer->hasRole('Super Admin')) {
                throw new DomainException('An employee cannot review their own progress.');
            }

            $status = $approved
                ? ($this->progressPercent(
                    (float) $goal->employee_reported_value,
                    (float) $goal->target_value,
                    $goal->scoring_direction,
                ) >= 100 ? 'completed' : 'active')
                : 'returned';

            $goal->update([
                'current_value' => $approved ? $goal->employee_reported_value : $goal->current_value,
                'employee_reported_value' => $approved ? null : $goal->employee_reported_value,
                'status' => $status,
                'manager_note' => trim((string) $note) ?: null,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
                'completed_at' => $status === 'completed' ? now() : null,
            ]);

            return $goal->refresh();
        });
    }

    public function progressPercent(float $actual, float $target, string $direction): float
    {
        if ($target <= 0) {
            return $actual <= 0 ? 100.0 : 0.0;
        }

        $score = match ($direction) {
            'higher_is_better' => ($actual / $target) * 100,
            'lower_is_better' => $actual <= 0 ? 100 : ($target / $actual) * 100,
            'target_is_best' => max(0, 100 - (abs($actual - $target) / $target * 100)),
            default => throw new DomainException('Invalid scoring direction.'),
        };

        return round(min(100, max(0, $score)), 2);
    }
}
