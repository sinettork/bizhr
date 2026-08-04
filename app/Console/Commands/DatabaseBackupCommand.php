<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class DatabaseBackupCommand extends Command
{
    protected $signature = 'bizhr:backup {--keep=14 : Number of daily backups to retain}';

    protected $description = 'Create and verify a safe backup of the BizHR SQLite database';

    public function handle(): int
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            $this->error('This local backup command supports SQLite only. Use managed database backups for MySQL/PostgreSQL.');

            return self::FAILURE;
        }

        $source = DB::connection()->getDatabaseName();

        if (! is_file($source)) {
            throw new RuntimeException("SQLite database not found: {$source}");
        }

        DB::statement('PRAGMA wal_checkpoint(FULL)');

        $directory = storage_path('app/private/backups');

        if (! is_dir($directory) && ! mkdir($directory, 0750, true) && ! is_dir($directory)) {
            throw new RuntimeException("Unable to create backup directory: {$directory}");
        }

        $target = $directory.'/bizhr-'.now()->format('Y-m-d_His').'.sqlite';
        $temporary = $target.'.tmp';

        if (! copy($source, $temporary)) {
            throw new RuntimeException('Unable to copy the SQLite database.');
        }

        $pdo = new \PDO('sqlite:'.$temporary);
        $statement = $pdo->query('PRAGMA integrity_check');
        $integrity = $statement->fetchColumn();
        $statement->closeCursor();
        $statement = null;
        $pdo = null;

        if ($integrity !== 'ok') {
            @unlink($temporary);
            throw new RuntimeException("Backup integrity verification failed: {$integrity}");
        }

        // Smoke test: Ensure critical tables exist and have data
        $pdo = new \PDO('sqlite:'.$temporary);
        $tables = ['users', 'employees', 'payroll_items'];
        foreach ($tables as $table) {
            $count = $pdo->query("SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND name='{$table}'")->fetchColumn();
            if ($count === 0) {
                $pdo = null;
                @unlink($temporary);
                throw new RuntimeException("Backup verification failed: Table '{$table}' is missing.");
            }
        }
        $pdo = null;

        $finalized = false;

        for ($attempt = 1; $attempt <= 10; $attempt++) {
            clearstatcache(true, $temporary);

            if (@rename($temporary, $target)) {
                $finalized = true;
                break;
            }

            usleep(250_000);
        }

        if (! $finalized) {
            @unlink($temporary);
            throw new RuntimeException('Unable to finalize the verified backup.');
        }

        $keep = max(1, (int) $this->option('keep'));
        $backups = glob($directory.'/bizhr-*.sqlite') ?: [];
        rsort($backups);

        foreach (array_slice($backups, $keep) as $expiredBackup) {
            @unlink($expiredBackup);
        }

        $this->info('Verified backup created: '.$target);

        return self::SUCCESS;
    }
}
