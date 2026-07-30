<?php

test('web responses include the required browser security headers', function () {
    $response = $this->get('/login');

    $response->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader(
            'Permissions-Policy',
            'camera=(self), geolocation=(self), microphone=()',
        )
        ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
});
