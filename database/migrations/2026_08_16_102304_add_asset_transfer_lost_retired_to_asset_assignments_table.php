<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->foreignId('transferred_to_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->date('transferred_at')->nullable();
            $table->foreignId('transferred_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_lost')->default(false);
            $table->date('lost_at')->nullable();
            $table->text('lost_reason')->nullable();
            $table->foreignId('lost_reported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_retired')->default(false);
            $table->date('retired_at')->nullable();
            $table->text('retirement_reason')->nullable();
            $table->foreignId('retired_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->dropForeign(['transferred_to_employee_id']);
            $table->dropForeign(['transferred_by']);
            $table->dropForeign(['lost_reported_by']);
            $table->dropForeign(['retired_by']);
            $table->dropColumn([
                'transferred_to_employee_id',
                'transferred_at',
                'transferred_by',
                'is_lost',
                'lost_at',
                'lost_reason',
                'lost_reported_by',
                'is_retired',
                'retired_at',
                'retirement_reason',
                'retired_by',
            ]);
        });
    }
};
