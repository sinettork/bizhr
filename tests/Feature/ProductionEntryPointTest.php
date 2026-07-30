<?php

use App\Models\User;

test('root redirects guests to the branded login page', function () {
    $this->get('/')->assertRedirect(route('login'));
});

test('root redirects authenticated users to the dashboard', function () {
    $this->actingAs(User::factory()->create())
        ->get('/')
        ->assertRedirect(route('dashboard'));
});
