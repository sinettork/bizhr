<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('updates a password through the active security page', function () {
    $user = User::factory()->create(['password' => Hash::make('current-password')]);
    $session = ['auth.password_confirmed_at' => time()];
    $this->actingAs($user)->withSession($session)->get(route('security.edit'))->assertOk()->assertSee('Update password');
    $this->actingAs($user)->withSession($session)->put(route('security.password.update'), ['current_password' => 'current-password', 'password' => 'new-secure-password', 'password_confirmation' => 'new-secure-password'])->assertRedirect();
    expect(Hash::check('new-secure-password', $user->fresh()->password))->toBeTrue();
});
