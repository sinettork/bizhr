<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeGoal;
use App\Models\PerformanceReview;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class PerformanceReviewService
{
    public function create(Employee $employee, User $reviewer, string $start, string $end): PerformanceReview
    {
        if ($reviewer->employee?->id === $employee->id && ! $reviewer->hasRole('Super Admin')) {
            throw new DomainException('A reviewer cannot create their own performance review.');
        }

        $goals = EmployeeGoal::query()
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['active', 'completed'])
            ->whereDate('start_date', '<=', $end)
            ->whereDate('due_date', '>=', $start)
            ->orderBy('id')->get();

        if ($goals->isEmpty()) {
            throw new DomainException('The employee has no eligible goals for this review period.');
        }
        if (abs((float) $goals->sum('weight') - 100) > 0.001) {
            throw new DomainException('Eligible employee goal weights must total exactly 100%.');
        }

        return DB::transaction(function () use ($employee, $reviewer, $start, $end, $goals) {
            $exists = PerformanceReview::query()->where('employee_id', $employee->id)
                ->whereDate('period_start', $start)->whereDate('period_end', $end)
                ->whereNotIn('status', ['cancelled'])->exists();
            if ($exists) {
                throw new DomainException('A review already exists for this employee and period.');
            }

            $review = PerformanceReview::create([
                'company_id' => $employee->company_id, 'employee_id' => $employee->id,
                'reviewer_id' => $reviewer->id, 'period_start' => $start,
                'period_end' => $end, 'status' => 'draft',
            ]);

            foreach ($goals as $index => $goal) {
                $review->scores()->create([
                    'employee_goal_id' => $goal->id, 'criterion_name' => $goal->title,
                    'criterion_description' => $goal->description,
                    'measurement_unit' => $goal->measurement_unit,
                    'target_value' => $goal->target_value, 'actual_value' => $goal->current_value,
                    'weight' => $goal->weight, 'scoring_direction' => $goal->scoring_direction,
                    'sort_order' => $index + 1,
                ]);
            }

            $review->update(['snapshot_checksum' => $this->checksum($review->load('scores'))]);
            return $review->refresh()->load('scores');
        });
    }

    public function submit(PerformanceReview $review, User $actor, array $scores, array $comments, array $summary): PerformanceReview
    {
        return DB::transaction(function () use ($review, $actor, $scores, $comments, $summary) {
            $review = PerformanceReview::query()->lockForUpdate()->with('scores')->findOrFail($review->id);
            if ($review->status !== 'draft' || $review->reviewer_id !== $actor->id) {
                throw new DomainException('Only the assigned reviewer can submit a draft review.');
            }

            $weightedTotal = 0.0;
            foreach ($review->scores as $criterion) {
                $score = (int) ($scores[$criterion->id] ?? 0);
                $comment = trim((string) ($comments[$criterion->id] ?? ''));
                if ($score < 1 || $score > 5) {
                    throw new DomainException('Every criterion requires a score from 1 to 5.');
                }
                if ($score <= 2 && mb_strlen($comment) < 10) {
                    throw new DomainException('Scores of 1 or 2 require a meaningful manager comment.');
                }
                $weighted = $score * (float) $criterion->weight / 100;
                $criterion->update(['manager_score' => $score, 'weighted_score' => $weighted, 'manager_comment' => $comment ?: null]);
                $weightedTotal += $weighted;
            }

            $review->update([
                'overall_score' => round($weightedTotal, 2),
                'strengths' => trim((string) ($summary['strengths'] ?? '')) ?: null,
                'areas_for_improvement' => trim((string) ($summary['areas_for_improvement'] ?? '')) ?: null,
                'manager_comment' => trim((string) ($summary['manager_comment'] ?? '')) ?: null,
                'status' => 'manager_submitted', 'manager_submitted_at' => now(),
            ]);
            return $review->refresh()->load('scores');
        });
    }

    public function approve(PerformanceReview $review, User $actor): PerformanceReview
    {
        if ($review->status !== 'manager_submitted') throw new DomainException('Only a manager-submitted review can be approved.');
        if ($review->reviewer_id === $actor->id && ! $actor->hasRole('Super Admin')) throw new DomainException('The reviewer cannot provide HR approval for the same review.');
        $review->update(['status' => 'hr_approved', 'hr_approved_by' => $actor->id, 'hr_approved_at' => now()]);
        return $review->refresh();
    }

    public function acknowledge(PerformanceReview $review, User $actor, ?string $comment): PerformanceReview
    {
        if ($actor->employee?->id !== $review->employee_id) throw new DomainException('You can acknowledge only your own review.');
        if ($review->status !== 'hr_approved') throw new DomainException('Only an HR-approved review can be acknowledged.');
        $review->update(['status' => 'employee_acknowledged', 'employee_comment' => trim((string) $comment) ?: null, 'employee_acknowledged_at' => now()]);
        return $review->refresh();
    }

    public function close(PerformanceReview $review, User $actor): PerformanceReview
    {
        if ($review->status !== 'employee_acknowledged') throw new DomainException('The employee must acknowledge the review before closure.');
        $review->update(['status' => 'closed', 'closed_by' => $actor->id, 'closed_at' => now()]);
        return $review->refresh();
    }

    public function reopen(PerformanceReview $review, User $actor, string $reason): PerformanceReview
    {
        if (! in_array($review->status, ['manager_submitted', 'hr_approved', 'employee_acknowledged', 'closed'], true)) throw new DomainException('This review cannot be reopened.');
        if (mb_strlen(trim($reason)) < 15) throw new DomainException('A detailed reopening reason of at least 15 characters is required.');
        $review->update(['status' => 'draft', 'reopened_by' => $actor->id, 'reopened_at' => now(), 'reopen_reason' => trim($reason), 'version' => $review->version + 1]);
        return $review->refresh();
    }

    public function calculateWeightedScore(array $criteria): float
    {
        $weight = array_sum(array_column($criteria, 'weight'));
        if (abs($weight - 100) > 0.001) throw new DomainException('Review weights must total exactly 100%.');
        $total = 0.0;
        foreach ($criteria as $criterion) {
            $score = (float) $criterion['score'];
            if ($score < 1 || $score > 5) throw new DomainException('Scores must be between 1 and 5.');
            $total += $score * (float) $criterion['weight'] / 100;
        }
        return round($total, 2);
    }

    private function checksum(PerformanceReview $review): string
    {
        return hash('sha256', json_encode([
            $review->employee_id, $review->period_start?->toDateString(),
            $review->period_end?->toDateString(),
            $review->scores->map->only(['criterion_name','target_value','actual_value','weight','scoring_direction'])->values()->all(),
        ], JSON_THROW_ON_ERROR));
    }
}
