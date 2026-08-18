<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        foreach ([
            'data_exports',
            'employee_documents',
            'employees',
            'employment_histories',
            'employment_contracts',
            'leave_requests',
        ] as $table) {
            DB::table($table)
                ->whereNull('public_id')
                ->orderBy('id')
                ->eachById(function (object $record) use ($table): void {
                    DB::table($table)
                        ->where('id', $record->id)
                        ->update(['public_id' => (string) Str::uuid()]);
                });
        }
    }

    public function down(): void
    {
        // Public IDs are permanent once exposed in application URLs.
    }
};
