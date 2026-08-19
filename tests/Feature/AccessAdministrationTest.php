<?php

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $this->owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
    $this->company = Company::query()->firstOrFail();
    $this->branch = Branch::query()->where('company_id', $this->company->id)->firstOrFail();
    $this->department = Department::query()->where('company_id', $this->company->id)->where('branch_id', $this->branch->id)->firstOrFail();
});

function accessEmployeeFixture($test, string $code, ?int $userId = null): Employee
{
    return Employee::query()->create([
        'company_id' => $test->company->id,
        'branch_id' => $test->branch->id,
        'department_id' => $test->department->id,
        'user_id' => $userId,
        'employee_code' => $code,
        'first_name' => 'Access',
        'last_name' => 'User',
        'hire_date' => today(),
        'employment_status' => 'Active',
        'salary_currency' => 'USD',
        'is_active' => true,
    ]);
}

it('provisions a company-linked user with a role and sends secure password setup instructions', function () {
    Notification::fake();
    $employee = accessEmployeeFixture($this, 'ACCESS-NEW');

    $this->actingAs($this->owner)->post(route('users.store'), [
        'name' => 'New Employee User',
        'email' => 'new.user@bizhr.local',
        'employee_id' => $employee->id,
        'roles' => ['Employee'],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $user = User::query()->where('email', 'new.user@bizhr.local')->firstOrFail();
    expect($user->is_active)->toBeTrue()
        ->and($user->hasRole('Employee'))->toBeTrue()
        ->and($employee->fresh()->user_id)->toBe($user->id)
        ->and(AuditLog::query()->where('record_type', User::class)->where('record_id', (string) $user->id)->where('action', 'provisioned')->exists())->toBeTrue();
    Notification::assertSentTo($user, ResetPassword::class);
});

it('revokes sessions when company-linked access changes or the user is deactivated', function () {
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('Employee');
    $employee = accessEmployeeFixture($this, 'ACCESS-UPDATE', $user->id);
    DB::table('sessions')->insert(['id' => 'test-access-session', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'test', 'payload' => 'payload', 'last_activity' => now()->timestamp]);

    $this->actingAs($this->owner)->put(route('users.update', $user), [
        'name' => $user->name,
        'email' => $user->email,
        'employee_id' => $employee->id,
        'roles' => ['Manager'],
    ])->assertRedirect()->assertSessionHasNoErrors();
    expect($user->fresh()->hasRole('Manager'))->toBeTrue()->and(DB::table('sessions')->where('user_id', $user->id)->exists())->toBeFalse();

    DB::table('sessions')->insert(['id' => 'test-deactivate-session', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'test', 'payload' => 'payload', 'last_activity' => now()->timestamp]);
    $this->actingAs($this->owner)->put(route('users.status', $user), ['is_active' => '0'])->assertRedirect();
    expect($user->fresh()->is_active)->toBeFalse()->and(DB::table('sessions')->where('user_id', $user->id)->exists())->toBeFalse();
});

it('allows only super admins to mutate global role definitions', function () {
    $ownerRole = Role::findByName('Owner', 'web');
    $this->actingAs($this->owner)->put(route('roles.update', $ownerRole), ['name' => 'Changed Owner', 'permissions' => ['company.view']])->assertForbidden();
    $this->actingAs($this->owner)->delete(route('roles.destroy', $ownerRole))->assertForbidden();
    expect(Role::findByName('Owner', 'web')->name)->toBe('Owner');
});
