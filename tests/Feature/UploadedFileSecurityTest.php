<?php

use App\Services\UploadedFileSecurityService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

it('rejects the standard antivirus test signature before storage', function () {
    config(['bizhr.upload_security.driver' => 'disabled']);
    Log::spy();
    $file = UploadedFile::fake()->createWithContent('unsafe.txt', 'BIZHR-MALWARE-SCANNER-TEST-SIGNATURE');

    expect(fn () => app(UploadedFileSecurityService::class)->assertSafe($file))
        ->toThrow(ValidationException::class, 'failed the security scan');
    Log::shouldHaveReceived('warning')->once();
});

it('allows a clean file in local development without an external scanner', function () {
    config(['bizhr.upload_security.driver' => 'disabled']);
    $file = UploadedFile::fake()->createWithContent('clean.txt', 'clean business document');

    app(UploadedFileSecurityService::class)->assertSafe($file);
    expect(true)->toBeTrue();
});

it('fails closed when the configured scanner is unavailable', function () {
    config(['bizhr.upload_security.driver' => 'clamscan', 'bizhr.upload_security.clamscan_binary' => 'definitely-missing-bizhr-scanner']);
    $file = UploadedFile::fake()->createWithContent('clean.txt', 'clean business document');

    expect(fn () => app(UploadedFileSecurityService::class)->assertSafe($file))
        ->toThrow(ValidationException::class, 'temporarily unavailable');
});
