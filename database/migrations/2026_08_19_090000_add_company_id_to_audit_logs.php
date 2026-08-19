<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->foreignId('company_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->index(['company_id', 'created_at']);
        });

        DB::table('audit_logs')
            ->whereNull('company_id')
            ->whereNotNull('user_id')
            ->orderBy('id')
            ->chunkById(500, function ($logs): void {
                foreach ($logs as $log) {
                    $companyId = DB::table('employees')
                        ->where('user_id', $log->user_id)
                        ->value('company_id');

                    if ($companyId !== null) {
                        DB::table('audit_logs')->where('id', $log->id)->update(['company_id' => $companyId]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->dropForeign(['company_id']);
            $table->dropIndex(['company_id', 'created_at']);
            $table->dropColumn('company_id');
        });
    }
};
