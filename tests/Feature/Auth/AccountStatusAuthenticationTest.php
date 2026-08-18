<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('a normal account without an employee profile can sign in', function () {
    $user = User::factory()->create([
        'email' => 'administrator@bizhr.local',
        'password' => Hash::make('password'),
        'is_active' => true,
    ]);

    $this->post('/login', [
        'email' => 'ADMINISTRATOR@BIZHR.LOCAL',
        'password' => 'password',
    ])->assertRedirect('/dashboard');

    $this->assertAuthenticatedAs($user);
});

test('an inactive account cannot sign in', function () {
    $user = User::factory()->create([
        'email' => 'inactive@bizhr.local',
        'password' => Hash::make('password'),
        'is_active' => false,
    ]);

    $this->post('/login', [
        'email' => 'inactive@bizhr.local',
        'password' => 'password',
    ])->assertSessionHasErrors('email');

    $this->assertGuest();
});
