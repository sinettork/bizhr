<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_goals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->foreignId('kpi_template_item_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('measurement_unit', 30);
            $table->decimal('target_value', 14, 2);
            $table->decimal('current_value', 14, 2)->default(0);
            $table->decimal('employee_reported_value', 14, 2)->nullable();
            $table->decimal('weight', 5, 2)->default(100);
            $table->string('scoring_direction', 20)->default('higher_is_better');
            $table->date('start_date');
            $table->date('due_date');
            $table->string('status', 30)->default('draft');
            $table->text('employee_note')->nullable();
            $table->text('manager_note')->nullable();
            $table->foreignId('assigned_by')->constrained('users')->restrictOnDelete();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['employee_id', 'status', 'due_date']);
            $table->index(['company_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_goals');
    }
};
