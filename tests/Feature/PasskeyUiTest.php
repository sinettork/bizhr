<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;

it('offers passkey sign in on the active Bootstrap login page', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Sign in with passkey')
        ->assertSee('build/assets/passkeys-', false)
        ->assertSee('autocomplete="email webauthn"', false);
});

it('offers passkey registration and management on the active security page', function () {
    $this->seed(DatabaseSeeder::class);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['auth.password_confirmed_at' => time()])
        ->get(route('security.edit'))
        ->assertOk()
        ->assertSee('Add passkey')
        ->assertSee('No passkeys registered.')
        ->assertSee('build/assets/passkeys-', false);
});
