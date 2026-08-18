<?php

it('uses self-hosted production UI assets', function () {
    $html = $this->get('/login')->assertOk()->getContent();

    expect($html)
        ->toContain('vendor/bootstrap/css/bootstrap.min.css')
        ->toContain('vendor/bootstrap/js/bootstrap.bundle.min.js')
        ->toContain('vendor/fontawesome/css/all.min.css')
        ->not->toContain('cdn.jsdelivr.net')
        ->not->toContain('cdnjs.cloudflare.com')
        ->not->toContain('fonts.googleapis.com');

    expect(public_path('vendor/bootstrap/css/bootstrap.min.css'))->toBeFile()
        ->and(public_path('vendor/bootstrap/js/bootstrap.bundle.min.js'))->toBeFile()
        ->and(public_path('vendor/fontawesome/css/all.min.css'))->toBeFile()
        ->and(public_path('vendor/htmx/htmx.min.js'))->toBeFile()
        ->and(public_path('build/manifest.json'))->toBeFile();
});
