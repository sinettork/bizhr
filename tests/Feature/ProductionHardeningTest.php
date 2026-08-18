<?php

use App\Http\Middleware\SecurityHeaders;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

it('adds browser security headers to web responses', function () {
    $response = $this->get('/login')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Cross-Origin-Opener-Policy', 'same-origin')
        ->assertHeader('Cross-Origin-Resource-Policy', 'same-site')
        ->assertHeaderMissing('Strict-Transport-Security');

    $policy = (string) $response->headers->get('Content-Security-Policy');

    expect($policy)
        ->toContain("script-src 'self' 'nonce-")
        ->not->toContain("script-src 'self' 'unsafe-inline'");

    preg_match("/'nonce-([^']+)'/", $policy, $matches);

    expect($matches[1] ?? null)->not->toBeNull()
        ->and($response->getContent())->toContain('nonce="'.$matches[1].'"');
});

it('enables HSTS for secure production requests', function () {
    $this->app->detectEnvironment(fn (): string => 'production');

    $request = Request::create('/', server: ['HTTPS' => 'on']);
    $response = (new SecurityHeaders)->handle(
        $request,
        fn (): Response => new Response,
    );

    expect($response->headers->get('Strict-Transport-Security'))
        ->toBe('max-age=31536000; includeSubDomains');
});

it('defaults sensitive exports to private storage', function () {
    expect(config('bizhr.exports_disk'))->toBe('local')
        ->and(config('filesystems.disks.local.root'))
        ->toBe(storage_path('app/private'))
        ->and(config('bizhr.exports_disk'))->not->toBe('public');
});

it('reports healthy only when the database is reachable', function () {
    $this->getJson('/up')
        ->assertOk()
        ->assertJson(['status' => 'up']);
});
