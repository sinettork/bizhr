<?php

use App\Models\User;

it('shows and updates the active HTTP profile page', function () {
    $user = User::factory()->create();
    $this->actingAs($user)->get(route('profile.edit'))->assertOk()->assertSee('Save profile');
    $this->actingAs($user)->put(route('profile.update'), ['name' => 'Updated User', 'email' => 'updated@example.test'])->assertRedirect();
    expect($user->fresh()->name)->toBe('Updated User')->and($user->fresh()->email_verified_at)->toBeNull();
});
