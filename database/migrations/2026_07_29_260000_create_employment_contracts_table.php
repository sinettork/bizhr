<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employment_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->foreignId('previous_contract_id')->nullable()
                ->constrained('employment_contracts')->nullOnDelete();
            $table->string('contract_number')->unique();
            $table->string('type', 30);
            $table->string('status', 30)->default('draft');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('signed_at')->nullable();
            $table->string('probation_category', 30)->nullable();
            $table->date('probation_end_date')->nullable();
            $table->string('position_title')->nullable();
            $table->string('department_name')->nullable();
            $table->string('branch_name')->nullable();
            $table->decimal('salary_amount', 14, 2);
            $table->char('salary_currency', 3)->default('USD');
            $table->string('pay_type', 20)->default('monthly');
            $table->decimal('work_hours_per_day', 4, 2)->default(8);
            $table->decimal('work_days_per_week', 3, 1)->default(6);
            $table->string('document_path')->nullable();
            $table->string('original_name')->nullable();
            $table->date('renewal_notice_date')->nullable();
            $table->foreignId('submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('terminated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('terminated_at')->nullable();
            $table->date('termination_date')->nullable();
            $table->text('termination_reason')->nullable();
            $table->json('terms')->nullable();
            $table->char('checksum', 64)->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['company_id', 'end_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employment_contracts');
    }
};
