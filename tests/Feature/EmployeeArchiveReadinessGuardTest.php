<?php

use App\Models\Employee;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;

beforeEach(function (): void {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $this->owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
    $this->employee = Employee::query()->where('email', 'piseth@bizhr.local')->firstOrFail();
});

it('does not archive an employee before separation status is recorded', function (): void {
    $this->actingAs($this->owner)
        ->delete(route('employees.destroy', $this->employee))
        ->assertSessionHasErrors('employee');

    expect(Employee::withTrashed()->find($this->employee->id)?->trashed())->toBeFalse();
});

it('does not archive a separated employee with outstanding handover work', function (): void {
    $this->employee->update(['employment_status' => 'Resigned', 'is_active' => false]);
    Task::query()->create([
        'company_id' => $this->employee->company_id,
        'assigned_by' => $this->owner->id,
        'assigned_to' => $this->employee->id,
        'title' => 'Outstanding archive guard handover',
        'priority' => 'high',
        'start_date' => today(),
        'due_date' => today()->addDay(),
        'status' => 'in_progress',
        'progress' => 20,
    ]);

    $this->actingAs($this->owner)
        ->delete(route('employees.destroy', $this->employee))
        ->assertSessionHasErrors('employee');

    expect(Employee::withTrashed()->find($this->employee->id)?->trashed())->toBeFalse();
});
