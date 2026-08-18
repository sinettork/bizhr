<?php

use App\Models\User;

it('invalidates an existing browser session as soon as the account is inactive', function () {
    $user = User::factory()->create(['is_active' => false, 'email_verified_at' => now()]);
    $this->actingAs($user);
    $oldSessionId = session()->getId();

    $this->get(route('dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('inactive_account');

    $this->assertGuest();
    expect(session()->getId())->not->toBe($oldSessionId);
});

it('returns forbidden for an inactive authenticated JSON request and logs it out', function () {
    $user = User::factory()->create(['is_active' => false, 'email_verified_at' => now()]);

    $this->actingAs($user)->getJson(route('dashboard'))->assertForbidden();
    $this->assertGuest();
});

it('keeps protected routes behind active-account enforcement', function () {
    $middleware = app('router')->getRoutes()->getByName('dashboard')?->gatherMiddleware() ?? [];

    expect($middleware)->toContain('auth')->toContain('active')->toContain('verified');
});
