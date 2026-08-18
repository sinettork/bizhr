<?php

use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;

it('allows task edits and reasoned cancellation while locking terminal tasks', function () {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
    $employee = User::query()->whereHas('employee')->firstOrFail()->employee;
    $task = Task::query()->create(['company_id' => $employee->company_id, 'assigned_by' => $owner->id, 'assigned_to' => $employee->id, 'title' => 'Prepare controls', 'description' => 'Initial scope', 'priority' => 'high', 'start_date' => today(), 'due_date' => today()->addWeek(), 'status' => 'not_started', 'progress' => 0]);

    $this->actingAs($owner)->put(route('tasks.update', $task), ['assigned_to' => $employee->id, 'title' => 'Prepare production controls', 'description' => 'Updated scope', 'priority' => 'urgent', 'start_date' => today()->toDateString(), 'due_date' => today()->addDays(10)->toDateString()])->assertRedirect()->assertSessionHasNoErrors();
    expect($task->fresh()->title)->toBe('Prepare production controls');

    $this->actingAs($owner)->post(route('tasks.cancel', $task), ['reason' => 'Business priority was formally withdrawn.'])->assertRedirect()->assertSessionHasNoErrors();
    expect($task->fresh()->status)->toBe('cancelled')->and($task->fresh()->cancellation_reason)->not->toBeEmpty();

    $this->actingAs($owner)->put(route('tasks.update', $task), ['assigned_to' => $employee->id, 'title' => 'Unsafe edit', 'priority' => 'low', 'start_date' => today()->toDateString(), 'due_date' => today()->addDay()->toDateString()])->assertStatus(422);
});
