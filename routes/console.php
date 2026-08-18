<?php

use App\Models\DataExport;
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
