<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('leave_types')) {
            return;
        }

        DB::table('leave_types')
            ->whereIn(DB::raw('upper(code)'), ['ANNUAL', 'ANNUAL_LEAVE'])
            ->where('days_per_year', '>=', 18)
            ->update(['is_statutory_annual_leave' => true, 'updated_at' => now()]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('leave_types')) {
            return;
        }

        DB::table('leave_types')
            ->whereIn(DB::raw('upper(code)'), ['ANNUAL', 'ANNUAL_LEAVE'])
            ->update(['is_statutory_annual_leave' => false, 'updated_at' => now()]);
    }
};
