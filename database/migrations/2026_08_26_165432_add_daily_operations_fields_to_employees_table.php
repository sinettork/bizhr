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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('marital_status')->nullable();
            $table->string('spouse_name')->nullable();
            $table->string('spouse_work_status')->nullable();
            $table->string('tin_number')->nullable();
            
            $table->foreignId('reports_to_id')->nullable()->constrained('employees')->nullOnDelete();
            
            $table->date('termination_date')->nullable();
            $table->text('termination_reason')->nullable();
            $table->string('turnover_type')->nullable();
            
            $table->string('work_email')->nullable();
            $table->string('current_address')->nullable();
            
            $table->foreignId('default_shift_id')->nullable()->constrained('work_shifts')->nullOnDelete();
            $table->date('seniority_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['reports_to_id']);
            $table->dropForeign(['default_shift_id']);
            
            $table->dropColumn([
                'marital_status',
                'spouse_name',
                'spouse_work_status',
                'tin_number',
                'reports_to_id',
                'termination_date',
                'termination_reason',
                'turnover_type',
                'work_email',
                'current_address',
                'default_shift_id',
                'seniority_date',
            ]);
        });
    }
};
