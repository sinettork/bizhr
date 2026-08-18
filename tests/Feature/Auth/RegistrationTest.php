<?php

use App\Models\User;

test('public registration page is disabled', function () {
    $this->get('/register')->assertNotFound();
});

test('guests cannot create accounts through the registration endpoint', function () {
    $this->post('/register', [
        'name' => 'Unapproved User',
        'email' => 'unapproved@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();

    expect(User::query()->where('email', 'unapproved@example.com')->exists())->toBeFalse();
});
