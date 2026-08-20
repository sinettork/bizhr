<?php

use App\Models\Employee;
use App\Models\Task;
use App\Models\User;
use App\Services\EmployeeLifecycleService;
use App\Services\EmployeeOffboardingReadinessService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use DomainException;

beforeEach(function (): void {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $this->employeeUser = User::query()->where('email', 'piseth@bizhr.local')->firstOrFail();
    $this->employee = Employee::query()->where('user_id', $this->employeeUser->id)->firstOrFail();
});

it('reports the full offboarding readiness checklist', function (): void {
    $readiness = app(EmployeeOffboardingReadinessService::class)->forEmployee($this->employee);

    expect($readiness)->toHaveKeys(['ready', 'outstanding', 'items'])
        ->and(collect($readiness['items'])->pluck('key')->all())
        ->toBe(['assets', 'tasks', 'expenses', 'contracts', 'leave', 'payroll']);
});

it('flags newly assigned open work as an offboarding concern', function (): void {
    $service = app(EmployeeOffboardingReadinessService::class);
    $before = collect($service->forEmployee($this->employee)['items'])->keyBy('key')['tasks']['count'];

    Task::query()->create([
        'company_id' => $this->employee->company_id,
        'assigned_by' => User::query()->where('email', 'manager@bizhr.local')->value('id') ?? $this->employeeUser->id,
        'assigned_to' => $this->employee->id,
        'title' => 'Handover outstanding customer file',
        'description' => 'Complete or transfer this work before offboarding.',
        'priority' => 'high',
        'start_date' => today(),
        'due_date' => today()->addDay(),
        'status' => 'in_progress',
        'progress' => 40,
    ]);

    $after = $service->forEmployee($this->employee);
    $tasks = collect($after['items'])->keyBy('key')['tasks'];

    expect($tasks['count'])->toBe($before + 1)
        ->and($tasks['blocking'])->toBeTrue()
        ->and($after['outstanding'])->toBeGreaterThanOrEqual(1);
});

it('shows the readiness checklist on employee edit pages', function (): void {
    $owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();

    $this->actingAs($owner)
        ->get(route('employees.edit', $this->employee))
        ->assertOk()
        ->assertSee('Offboarding readiness')
        ->assertSee('Assigned assets')
        ->assertSee('Unsettled expenses')
        ->assertSee('Unpaid payroll items');
});

it('blocks final archive while offboarding still has outstanding work', function (): void {
    Task::query()->create([
        'company_id' => $this->employee->company_id,
        'assigned_by' => User::query()->where('email', 'manager@bizhr.local')->value('id') ?? $this->employeeUser->id,
        'assigned_to' => $this->employee->id,
        'title' => 'Final handover task',
        'priority' => 'high',
        'start_date' => today(),
        'due_date' => today()->addDay(),
        'status' => 'in_progress',
        'progress' => 10,
    ]);
    $this->employee->update(['employment_status' => 'Resigned', 'is_active' => false]);

    expect(fn () => app(EmployeeLifecycleService::class)->completeSeparation($this->employee->fresh()))
        ->toThrow(DomainException::class, 'Employee offboarding still has');

    expect(Employee::withTrashed()->find($this->employee->id)?->trashed())->toBeFalse();
});
