<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Task;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

class TaskWorkflowService
{
    public function updateProgress(Task $task, User $actor, int $progress, ?string $note): Task
    {
        if ($progress < 0 || $progress > 100) {
            throw new DomainException('Progress must be between 0 and 100.');
        }

        return DB::transaction(function () use ($task, $actor, $progress, $note): Task {
            $task = Task::query()->lockForUpdate()->findOrFail($task->id);
            $this->assertSameCompany($task, $actor);
            if ($this->actorEmployeeId($actor) !== (int) $task->assigned_to) {
                throw new DomainException('You can update only your own assigned task.');
            }
            if (in_array($task->status, ['verified', 'cancelled'], true)) {
                throw new DomainException('A verified or cancelled task cannot be changed.');
            }

            $task->update([
                'progress' => $progress,
                'employee_note' => trim((string) $note) ?: null,
                'status' => $progress === 100 ? 'waiting_verification' : ($progress > 0 ? 'in_progress' : 'not_started'),
                'submitted_at' => $progress === 100 ? now() : null,
            ]);

            return $task->refresh();
        });
    }

    public function verify(Task $task, User $actor, bool $approved, ?string $note): Task
    {
        return DB::transaction(function () use ($task, $actor, $approved, $note): Task {
            $task = Task::query()->lockForUpdate()->findOrFail($task->id);
            $this->assertSameCompany($task, $actor);
            if ($task->status !== 'waiting_verification') {
                throw new DomainException('Only a submitted task can be verified.');
            }
            if ($this->actorEmployeeId($actor) === (int) $task->assigned_to && ! $actor->hasRole('Super Admin')) {
                throw new DomainException('An employee cannot verify their own task.');
            }

            $task->update([
                'status' => $approved ? 'verified' : 'in_progress',
                'progress' => $approved ? 100 : min(99, $task->progress),
                'manager_note' => trim((string) $note) ?: null,
                'completed_at' => $approved ? now() : null,
                'verified_by' => $actor->id,
                'verified_at' => now(),
            ]);

            return $task->refresh();
        });
    }

    public function cancel(Task $task, User $actor, string $reason): Task
    {
        if (mb_strlen(trim($reason)) < 10) {
            throw new DomainException('A cancellation reason of at least 10 characters is required.');
        }

        return DB::transaction(function () use ($task, $actor, $reason): Task {
            $task = Task::query()->lockForUpdate()->findOrFail($task->id);
            $this->assertSameCompany($task, $actor);
            if (in_array($task->status, ['verified', 'cancelled'], true)) {
                throw new DomainException('This task cannot be cancelled.');
            }

            $task->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => trim($reason),
                'verified_by' => $actor->id,
            ]);

            return $task->refresh();
        });
    }

    private function assertSameCompany(Task $task, User $actor): void
    {
        if ($actor->companyId() !== (int) $task->company_id) {
            throw new DomainException('The task does not belong to the actor company.');
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
