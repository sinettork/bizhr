<?php

return [
    'allow_demo_data' => (bool) env('ALLOW_DEMO_DATA', false),
    /*
    |--------------------------------------------------------------------------
    | Sensitive storage
    |--------------------------------------------------------------------------
    |
    | Employee documents, candidate CVs, and generated exports contain
    | sensitive HR data and must stay on private disks. Supported production
    | choices are the private local disk and private S3-compatible storage.
    |
    */
    'documents_disk' => env('BIZHR_DOCUMENTS_DISK', env('FILESYSTEM_DISK', 'local')),
    'recruitment_disk' => env('BIZHR_RECRUITMENT_DISK', env('BIZHR_DOCUMENTS_DISK', env('FILESYSTEM_DISK', 'local'))),
    'exports_disk' => env('BIZHR_EXPORTS_DISK', 'local'),

    'upload_security' => [
        'driver' => env('BIZHR_UPLOAD_SCANNER', env('APP_ENV') === 'production' ? 'clamscan' : 'disabled'),
        'clamscan_binary' => env('BIZHR_CLAMSCAN_BINARY', 'clamscan'),
        'timeout_seconds' => max(1, (int) env('BIZHR_UPLOAD_SCAN_TIMEOUT', 30)),
    ],

    /*
    |--------------------------------------------------------------------------
    | SQLite backups
    |--------------------------------------------------------------------------
    |
    | Enable the built-in verified backup only for single-node SQLite
    | deployments. Managed MySQL/PostgreSQL deployments should use provider
    | snapshots and point-in-time recovery instead.
    |
    */
    'sqlite_backup' => [
        'enabled' => (bool) env(
            'BIZHR_SQLITE_BACKUP_ENABLED',
            env('DB_CONNECTION', 'sqlite') === 'sqlite',
        ),
        'keep' => max(1, (int) env('BIZHR_SQLITE_BACKUP_KEEP', 14)),
    ],

    /*
    |--------------------------------------------------------------------------
    | ផ្ទាំងគ្រប់គ្រង
    |--------------------------------------------------------------------------
    |
    | រក្សាទុកស្ថិតិរយៈពេលខ្លី ដើម្បីកាត់បន្ថយការភ្ជាប់ទៅមូលដ្ឋានទិន្នន័យ
    | Supabase ពីចម្ងាយ ខណៈពេលទិន្នន័យនៅតែទាន់សម័យសម្រាប់ការងារ HR ប្រចាំថ្ងៃ។
    |
    */
    'dashboard' => [
        'cache_seconds' => max(30, (int) env('BIZHR_DASHBOARD_CACHE_SECONDS', 90)),
        'poll_seconds' => max(30, (int) env('BIZHR_DASHBOARD_POLL_SECONDS', 60)),
    ],
];
