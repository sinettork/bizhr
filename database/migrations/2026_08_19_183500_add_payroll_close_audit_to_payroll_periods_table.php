<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table): void {
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->string('close_reason', 1000)->nullable();
            $table->foreignId('reopened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reopened_at')->nullable();
            $table->string('reopen_reason', 1000)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('closed_by');
            $table->dropConstrainedForeignId('reopened_by');
            $table->dropColumn(['closed_at', 'close_reason', 'reopened_at', 'reopen_reason']);
        });
    }
};
