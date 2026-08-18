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
            $table->foreignId('transferred_to_employee_id')->nullable()->constrained('employees')->nullOnDelete()->after('received_by');
            $table->date('transferred_at')->nullable()->after('transferred_to_employee_id');
            $table->foreignId('transferred_by')->nullable()->constrained('users')->nullOnDelete()->after('transferred_at');
            $table->boolean('is_lost')->default(false)->after('transferred_by');
            $table->date('lost_at')->nullable()->after('is_lost');
            $table->text('lost_reason')->nullable()->after('lost_at');
            $table->foreignId('lost_reported_by')->nullable()->constrained('users')->nullOnDelete()->after('lost_reason');
            $table->boolean('is_retired')->default(false)->after('lost_reported_by');
            $table->date('retired_at')->nullable()->after('is_retired');
            $table->text('retirement_reason')->nullable()->after('retired_at');
            $table->foreignId('retired_by')->nullable()->constrained('users')->nullOnDelete()->after('retirement_reason');
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
