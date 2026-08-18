<?php

use App\Models\User;

it('redirects guests and renders the Bootstrap dashboard for authenticated users', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
    $this->actingAs(User::factory()->create())->get(route('dashboard'))->assertOk()->assertSee('Dashboard')->assertSee('app-navbar', false);
});
