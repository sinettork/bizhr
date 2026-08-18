<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\Process\Process;

class UploadedFileSecurityService
{
    private const EICAR_SIGNATURE = 'X5O!P%@AP[4\\PZX54(P^)7CC)7}$EICAR-STANDARD-ANTIVIRUS-TEST-FILE!$H+H*';

    private const TEST_SIGNATURE = 'BIZHR-MALWARE-SCANNER-TEST-SIGNATURE';

    public function assertSafe(UploadedFile $file, string $field = 'file'): void
    {
        $path = $file->getPathname();
        $contents = $file->get();
        if ($contents === false) {
            throw ValidationException::withMessages([$field => 'The uploaded file could not be inspected.']);
        }
        $fingerprint = hash('sha256', $contents);
        if (str_contains($contents, self::EICAR_SIGNATURE) || (app()->runningUnitTests() && str_contains($contents, self::TEST_SIGNATURE))) {
            $this->reject($field, $fingerprint, (int) $file->getSize(), 'signature');
        }

        $driver = (string) config('bizhr.upload_security.driver', 'disabled');
        if ($driver === 'disabled') {
            if (app()->isProduction()) {
                throw new RuntimeException('BIZHR_UPLOAD_SCANNER must be configured in production.');
            }

            return;
        }
        if ($driver !== 'clamscan') {
            throw new RuntimeException("Unsupported upload security driver [{$driver}].");
        }

        $process = new Process([(string) config('bizhr.upload_security.clamscan_binary', 'clamscan'), '--no-summary', $path]);
        $process->setTimeout(max(1, (int) config('bizhr.upload_security.timeout_seconds', 30)))->run();

        if ($process->getExitCode() === 1 && str_contains($process->getOutput().$process->getErrorOutput(), 'FOUND')) {
            $this->reject($field, $fingerprint, (int) $file->getSize(), 'clamscan');
        }
        if (! $process->isSuccessful()) {
            Log::error('upload.scan_unavailable', ['sha256' => $fingerprint, 'size' => (int) $file->getSize(), 'exit_code' => $process->getExitCode()]);
            throw ValidationException::withMessages([$field => 'File security scanning is temporarily unavailable. Try again later.']);
        }
    }

    private function reject(string $field, string $fingerprint, int $size, string $scanner): never
    {
        Log::warning('upload.malware_rejected', ['sha256' => $fingerprint, 'size' => $size, 'scanner' => $scanner]);
        throw ValidationException::withMessages([$field => 'The uploaded file failed the security scan.']);
    }
}
