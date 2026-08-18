<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->timestamp('rehire_requested_at')->nullable();
            $table->foreignId('rehire_requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('rehire_effective_date')->nullable();
            $table->text('rehire_reason')->nullable();
            $table->timestamp('rehire_approved_at')->nullable();
            $table->foreignId('rehire_approved_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('rehire_requested_by');
            $table->dropConstrainedForeignId('rehire_approved_by');
            $table->dropColumn(['rehire_requested_at', 'rehire_effective_date', 'rehire_reason', 'rehire_approved_at']);
        });
    }
};
