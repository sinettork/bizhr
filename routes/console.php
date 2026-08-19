<?php

use App\Models\DataExport;
use App\Services\HrReminderService;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Storage;

Schedule::command(
    'bizhr:backup --keep='.(int) config('bizhr.sqlite_backup.keep', 14),
)
    ->dailyAt('01:00')
    ->when(fn (): bool => (bool) config('bizhr.sqlite_backup.enabled', false))
    ->withoutOverlapping()
    ->onOneServer();

Schedule::call(function (): void {
    DataExport::query()
        ->whereNotNull('expires_at')
        ->where('expires_at', '<=', now())
        ->chunkById(100, function ($exports): void {
            foreach ($exports as $export) {
                if ($export->file_path) {
                    Storage::disk($export->disk)->delete($export->file_path);
                }

                $export->delete();
            }
        });
})
    ->dailyAt('02:00')
    ->name('cleanup-expired-data-exports')
    ->withoutOverlapping()
    ->onOneServer();

Schedule::call(fn (): int => app(HrReminderService::class)->run())
    ->dailyAt('08:00')
    ->name('send-hr-milestone-reminders')
    ->withoutOverlapping()
    ->onOneServer();
