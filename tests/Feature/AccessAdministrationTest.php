<?php

use App\Models\AuditLog;
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
});

it('provisions a user with a role and sends secure password setup instructions', function () {
    Notification::fake();

    $this->actingAs($this->owner)->post(route('users.store'), [
        'name' => 'New Employee User',
        'email' => 'new.user@bizhr.local',
        'roles' => ['Employee'],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $user = User::query()->where('email', 'new.user@bizhr.local')->firstOrFail();
    expect($user->is_active)->toBeTrue()
        ->and($user->hasRole('Employee'))->toBeTrue()
        ->and(AuditLog::query()->where('record_type', User::class)->where('record_id', (string) $user->id)->where('action', 'provisioned')->exists())->toBeTrue();
    Notification::assertSentTo($user, ResetPassword::class);
});

it('revokes sessions when access changes or the user is deactivated', function () {
    $user = User::factory()->create(['is_active' => true]);
    $user->assignRole('Employee');
    DB::table('sessions')->insert(['id' => 'test-access-session', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'test', 'payload' => 'payload', 'last_activity' => now()->timestamp]);

    $this->actingAs($this->owner)->put(route('users.update', $user), ['name' => $user->name, 'email' => $user->email, 'roles' => ['Manager']])->assertRedirect()->assertSessionHasNoErrors();
    expect($user->fresh()->hasRole('Manager'))->toBeTrue()->and(DB::table('sessions')->where('user_id', $user->id)->exists())->toBeFalse();

    DB::table('sessions')->insert(['id' => 'test-deactivate-session', 'user_id' => $user->id, 'ip_address' => '127.0.0.1', 'user_agent' => 'test', 'payload' => 'payload', 'last_activity' => now()->timestamp]);
    $this->actingAs($this->owner)->put(route('users.status', $user), ['is_active' => '0'])->assertRedirect();
    expect($user->fresh()->is_active)->toBeFalse()->and(DB::table('sessions')->where('user_id', $user->id)->exists())->toBeFalse();
});

it('protects system roles from modification and deletion', function () {
    $ownerRole = Role::findByName('Owner', 'web');
    $this->actingAs($this->owner)->put(route('roles.update', $ownerRole), ['name' => 'Changed Owner', 'permissions' => ['company.view']])->assertStatus(422);
    $this->actingAs($this->owner)->delete(route('roles.destroy', $ownerRole))->assertStatus(422);
    expect(Role::findByName('Owner', 'web')->name)->toBe('Owner');
});
