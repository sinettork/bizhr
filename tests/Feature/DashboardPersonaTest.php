<?php

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
    $this->company = Company::factory()->create();
    $this->branch = Branch::factory()->create(['company_id' => $this->company->id]);
    $this->department = Department::factory()->create([
        'company_id' => $this->company->id,
        'branch_id' => $this->branch->id,
    ]);
});

function dashboardUserFor(object $test, string $role): User
{
    $user = User::factory()->create([
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $user->assignRole($role);
    Employee::factory()->create([
        'company_id' => $test->company->id,
        'branch_id' => $test->branch->id,
        'department_id' => $test->department->id,
        'user_id' => $user->id,
        'is_active' => true,
        'employment_status' => 'Active',
    ]);

    return $user->refresh();
}

it('renders an employee self-service dashboard', function (): void {
    $user = dashboardUserFor($this, 'Employee');

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('My Dashboard')
        ->assertSee('Personal Workspace')
        ->assertDontSee('System Dashboard');
});

it('renders a manager team dashboard instead of the employee homepage', function (): void {
    $user = dashboardUserFor($this, 'Manager');

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Team Dashboard')
        ->assertSee('Team Operations')
        ->assertDontSee('My next work');
});

it('renders an HR people operations dashboard', function (): void {
    $user = dashboardUserFor($this, 'HR Administrator');

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('People Dashboard')
        ->assertSee('HR Operations')
        ->assertDontSee('My next work');
});

it('renders an accountant finance operations dashboard', function (): void {
    $user = dashboardUserFor($this, 'Accountant');

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Finance Dashboard')
        ->assertSee('Finance Operations')
        ->assertDontSee('My next work');
});

it('renders an owner executive dashboard', function (): void {
    $user = dashboardUserFor($this, 'Owner');

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Executive Dashboard')
        ->assertSee('Business Overview')
        ->assertDontSee('My next work');
});

it('renders a pure Super Admin system dashboard even when an employee record is linked', function (): void {
    $user = dashboardUserFor($this, 'Super Admin');

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertSee('System Dashboard')
        ->assertSee('System Oversight')
        ->assertDontSee('My next work')
        ->assertDontSee('My workspace');
});

it('renders the Super Admin system dashboard without a company-linked employee account', function (): void {
    Company::factory()->count(2)->create();
    $user = User::factory()->create([
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $user->assignRole('Super Admin');

    $this->actingAs($user)->get(route('dashboard'))
        ->assertOk()
        ->assertViewHas('dashboardPersona', 'super-admin');
});

it('lets Super Admin select a company and use its company-scoped features', function (): void {
    $firstCompany = Company::factory()->create(['name' => 'First Workspace']);
    $firstBranch = Branch::factory()->create(['company_id' => $firstCompany->id, 'name' => 'First Branch']);
    $secondCompany = Company::factory()->create(['name' => 'Second Workspace']);
    Branch::factory()->create(['company_id' => $secondCompany->id, 'name' => 'Second Branch']);
    $user = User::factory()->create([
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $user->assignRole('Super Admin');

    $this->actingAs($user)
        ->post(route('company-context.update'), ['company_id' => $firstCompany->id])
        ->assertRedirect(route('dashboard'))
        ->assertSessionHas('active_company_id', $firstCompany->id);

    $this->get(route('branches.index'))
        ->assertOk()
        ->assertSee($firstBranch->name)
        ->assertDontSee('Second Branch');
});

it('prevents non-Super Admin users from changing the active company', function (): void {
    $company = Company::factory()->create();
    $user = User::factory()->create([
        'is_active' => true,
        'email_verified_at' => now(),
    ]);
    $user->assignRole('Employee');

    $this->actingAs($user)
        ->post(route('company-context.update'), ['company_id' => $company->id])
        ->assertForbidden();
});
