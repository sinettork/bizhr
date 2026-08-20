<?php

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmploymentHistory;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Support\Facades\DB;

it('lets an owner create, update, separate, and archive an employee', function () {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);

    $owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
    $branch = Branch::query()->where('is_active', true)->firstOrFail();
    $department = Department::query()->where('branch_id', $branch->id)->where('is_active', true)->firstOrFail();

    $payload = [
        'employee_code' => 'E-CRUD-001',
        'branch_id' => $branch->id,
        'department_id' => $department->id,
        'first_name' => 'CRUD',
        'last_name' => 'Employee',
        'full_name_en' => 'CRUD Employee',
        'hire_date' => now()->toDateString(),
        'employment_status' => 'Active',
        'salary_currency' => 'USD',
        'is_active' => '1',
    ];

    $this->actingAs($owner)->post(route('employees.store'), $payload)
        ->assertRedirect();

    $employee = Employee::query()->where('employee_code', 'E-CRUD-001')->firstOrFail();
    expect($employee->full_name_en)->toBe('CRUD Employee');

    $this->actingAs($owner)->get(route('employees.show', $employee))
        ->assertOk()
        ->assertSee('CRUD Employee');

    $this->actingAs($owner)->put(route('employees.update', $employee), [
        ...$payload,
        'full_name_en' => 'Updated Employee',
        'employment_status' => 'On probation',
        'effective_date' => now()->toDateString(),
        'change_reason' => 'Employee entered the approved probation period.',
    ])->assertRedirect(route('employees.show', $employee));

    expect($employee->fresh()->employment_status)->toBe('On probation')
        ->and(EmploymentHistory::query()->where('employee_id', $employee->id)->where('event_type', 'employment_change')->exists())->toBeTrue();

    $this->actingAs($owner)->put(route('employees.update', $employee), [
        ...$payload,
        'full_name_en' => 'Updated Employee',
        'employment_status' => 'Terminated',
        'is_active' => '0',
        'effective_date' => now()->toDateString(),
        'change_reason' => 'Employee completed the approved separation workflow before archive.',
    ])->assertRedirect(route('employees.show', $employee));

    expect($employee->fresh()->employment_status)->toBe('Terminated');

    $this->actingAs($owner)->delete(route('employees.destroy', $employee))
        ->assertRedirect(route('employees.index'));

    expect(Employee::withTrashed()->find($employee->id)?->trashed())->toBeTrue();
});

it('auto-generates unique employee codes and rejects duplicates', function () {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);

    $owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
    $branch = Branch::query()->where('is_active', true)->firstOrFail();
    $department = Department::query()->where('branch_id', $branch->id)->where('is_active', true)->firstOrFail();

    $this->actingAs($owner)->post(route('employees.store'), [
        'employee_code' => '',
        'branch_id' => $branch->id,
        'department_id' => $department->id,
        'first_name' => 'Generated',
        'last_name' => 'Code',
        'hire_date' => now()->toDateString(),
        'employment_status' => 'Draft',
        'salary_currency' => 'USD',
        'is_active' => '1',
    ])->assertRedirect();

    $generated = Employee::query()->where('first_name', 'Generated')->where('last_name', 'Code')->firstOrFail();
    expect($generated->employee_code)->not->toBeEmpty()
        ->and($generated->employee_code)->toMatch('/^[A-Z0-9._-]+$/');

    $this->actingAs($owner)->post(route('employees.store'), [
        'employee_code' => $generated->employee_code,
        'branch_id' => $branch->id,
        'department_id' => $department->id,
        'first_name' => 'Duplicate',
        'last_name' => 'Code',
        'hire_date' => now()->toDateString(),
        'employment_status' => 'Draft',
        'salary_currency' => 'USD',
        'is_active' => '1',
    ])->assertSessionHasErrors('employee_code');
});

it('deprovisions linked access when employment is terminated', function () {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
    $employee = Employee::query()->whereNotNull('user_id')->where('is_active', true)->firstOrFail();
    $user = $employee->user()->firstOrFail();
    DB::table('sessions')->insert(['id' => 'employee-active-session', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'test', 'payload' => 'payload', 'last_activity' => now()->timestamp]);

    $payload = $employee->only(['employee_code', 'branch_id', 'department_id', 'position_id', 'employment_type_id', 'first_name', 'last_name', 'full_name_en', 'hire_date', 'salary_currency']);
    $this->actingAs($owner)->put(route('employees.update', $employee), [...$payload, 'employment_status' => 'Terminated', 'effective_date' => today()->toDateString(), 'change_reason' => 'Employment terminated through the approved separation workflow.'])->assertRedirect();

    expect($employee->fresh()->employment_status)->toBe('Terminated')
        ->and($user->fresh()->is_active)->toBeFalse()
        ->and(DB::table('sessions')->where('user_id', $user->id)->exists())->toBeFalse();
});

it('requires a separate approver before a separated employee is rehired', function () {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
    $employee = Employee::query()->where('is_active', true)->firstOrFail();
    $employee->update(['employment_status' => 'Terminated', 'is_active' => false]);
    $requester = User::factory()->create();
    Employee::factory()->create([
        'company_id' => $employee->company_id,
        'branch_id' => $employee->branch_id,
        'department_id' => $employee->department_id,
        'user_id' => $requester->id,
    ]);
    $requester->givePermissionTo(['employee.edit', 'employee.view-sensitive', 'employee.approve']);

    $this->actingAs($requester)
        ->post(route('employees.rehire.request', $employee), ['effective_date' => today()->toDateString(), 'reason' => 'The employee passed the documented rehire eligibility review.'])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $this->actingAs($requester)->post(route('employees.rehire.approve', $employee))->assertStatus(422);
    $this->actingAs($owner)->post(route('employees.rehire.approve', $employee))->assertRedirect();

    expect($employee->fresh()->employment_status)->toBe('Active')
        ->and($employee->fresh()->rehire_approved_by)->toBe($owner->id)
        ->and(EmploymentHistory::query()->where('employee_id', $employee->id)->where('event_type', 'rehire')->exists())->toBeTrue();
});
