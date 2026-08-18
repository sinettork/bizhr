<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_corrections', function (Blueprint $table): void {
            $table->foreignId('reopened_by')->nullable()->after('reviewed_by')->constrained('users')->nullOnDelete();
            $table->timestamp('reopened_at')->nullable()->after('reviewed_at');
            $table->text('reopen_reason')->nullable()->after('review_note');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_corrections', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('reopened_by');
            $table->dropColumn(['reopened_at', 'reopen_reason']);
        });
    }
};
