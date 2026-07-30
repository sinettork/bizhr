<?php

namespace App\Services;

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
        if ($actor->employee?->id !== $task->assigned_to) {
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
    }

    public function verify(Task $task, User $actor, bool $approved, ?string $note): Task
    {
        return DB::transaction(function () use ($task, $actor, $approved, $note) {
            $task = Task::query()->lockForUpdate()->findOrFail($task->id);
            if ($task->status !== 'waiting_verification') {
                throw new DomainException('Only a submitted task can be verified.');
            }
            if ($actor->employee?->id === $task->assigned_to && ! $actor->hasRole('Super Admin')) {
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
        if (in_array($task->status, ['verified', 'cancelled'], true)) {
            throw new DomainException('This task cannot be cancelled.');
        }
        if (mb_strlen(trim($reason)) < 10) {
            throw new DomainException('A cancellation reason of at least 10 characters is required.');
        }
        $task->update([
            'status' => 'cancelled', 'cancelled_at' => now(),
            'cancellation_reason' => trim($reason), 'verified_by' => $actor->id,
        ]);
        return $task->refresh();
    }
}
