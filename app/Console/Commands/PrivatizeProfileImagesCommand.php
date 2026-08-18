<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PrivatizeProfileImagesCommand extends Command
{
    protected $signature = 'bizhr:privatize-profile-images {--dry-run : Report public images without moving them}';

    protected $description = 'Move legacy user avatars and employee profile photos from public storage to authorized private storage.';

    public function handle(): int
    {
        $moved = 0;
        $missing = 0;

        User::query()->whereNotNull('avatar_path')->orderBy('id')->each(function (User $user) use (&$moved, &$missing): void {
            if ($user->avatar_path === null || str_starts_with($user->avatar_path, 'private/')) {
                return;
            }
            $destination = "private/users/{$user->id}/avatars/".basename($user->avatar_path);
            $this->move($user->avatar_path, $destination, function () use ($user, $destination): void {
                $user->forceFill(['avatar_path' => $destination])->save();
            }, $moved, $missing);
        });

        Employee::query()->whereNotNull('profile_photo')->orderBy('id')->each(function (Employee $employee) use (&$moved, &$missing): void {
            if ($employee->profile_photo === null || str_starts_with($employee->profile_photo, 'private/')) {
                return;
            }
            $destination = "private/companies/{$employee->company_id}/employees/{$employee->id}/profile-photos/".basename($employee->profile_photo);
            $this->move($employee->profile_photo, $destination, function () use ($employee, $destination): void {
                $employee->forceFill(['profile_photo' => $destination])->save();
            }, $moved, $missing);
        });

        $this->info("Profile image privacy migration complete: {$moved} moved; {$missing} missing source files.");

        return $missing === 0 ? self::SUCCESS : self::FAILURE;
    }

    /** @param callable(): void $persist */
    private function move(string $source, string $destination, callable $persist, int &$moved, int &$missing): void
    {
        if (! Storage::disk('public')->exists($source)) {
            $this->error("Missing public image: {$source}");
            $missing++;

            return;
        }
        if ($this->option('dry-run')) {
            $this->line("Would move {$source} to {$destination}");

            return;
        }

        $stream = Storage::disk('public')->readStream($source);
        if (! is_resource($stream)) {
            throw new RuntimeException("Unable to read legacy profile image {$source}.");
        }
        try {
            if (! Storage::disk('local')->writeStream($destination, $stream)) {
                throw new RuntimeException("Unable to write private profile image {$destination}.");
            }
        } finally {
            fclose($stream);
        }

        $persist();
        Storage::disk('public')->delete($source);
        $moved++;
        $this->line("Moved {$source} to private storage.");
    }
}
