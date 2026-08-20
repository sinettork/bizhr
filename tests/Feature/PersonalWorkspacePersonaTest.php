<?php

use App\Models\Employee;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;

beforeEach(function (): void {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
});

it('keeps My Workspace hidden and blocked for a pure Super Admin even when linked to an employee', function (): void {
    $employee = Employee::query()->where('is_active', true)->firstOrFail();
    $superAdmin = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $superAdmin->assignRole('Super Admin');
    $employee->update(['user_id' => $superAdmin->id]);

    $this->actingAs($superAdmin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('sidebar-my-workspace', false);

    $this->actingAs($superAdmin)
        ->get(route('tasks.mine'))
        ->assertForbidden();
});

it('shows My Workspace and allows self service for an active Employee account', function (): void {
    $employeeUser = User::query()->where('email', 'piseth@bizhr.local')->firstOrFail();

    $this->actingAs($employeeUser)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('sidebar-my-workspace', false);

    $this->actingAs($employeeUser)
        ->get(route('tasks.mine'))
        ->assertOk();
});

it('allows an intentional Super Admin and Employee dual-role account to use self service', function (): void {
    $employee = Employee::query()->where('is_active', true)->firstOrFail();
    $dualRole = User::factory()->create(['email_verified_at' => now(), 'is_active' => true]);
    $dualRole->syncRoles(['Super Admin', 'Employee']);
    $employee->update(['user_id' => $dualRole->id]);

    $this->actingAs($dualRole)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('sidebar-my-workspace', false);

    $this->actingAs($dualRole)
        ->get(route('tasks.mine'))
        ->assertOk();
});
