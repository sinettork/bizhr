<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->foreignId('reviewer_id')->constrained('users')->restrictOnDelete();
            $table->date('period_start');
            $table->date('period_end');
            $table->string('status', 30)->default('draft');
            $table->decimal('overall_score', 5, 2)->nullable();
            $table->text('strengths')->nullable();
            $table->text('areas_for_improvement')->nullable();
            $table->text('manager_comment')->nullable();
            $table->text('employee_comment')->nullable();
            $table->timestamp('manager_submitted_at')->nullable();
            $table->foreignId('hr_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hr_approved_at')->nullable();
            $table->timestamp('employee_acknowledged_at')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->foreignId('reopened_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reopened_at')->nullable();
            $table->text('reopen_reason')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->char('snapshot_checksum', 64)->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['employee_id', 'period_start', 'period_end', 'version'], 'performance_review_period_version_unique');
            $table->index(['company_id', 'status']);
        });

        Schema::create('performance_review_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performance_review_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_goal_id')->nullable()->constrained()->nullOnDelete();
            $table->string('criterion_name');
            $table->text('criterion_description')->nullable();
            $table->string('measurement_unit', 30);
            $table->decimal('target_value', 14, 2);
            $table->decimal('actual_value', 14, 2);
            $table->decimal('weight', 5, 2);
            $table->string('scoring_direction', 20);
            $table->unsignedTinyInteger('manager_score')->nullable();
            $table->decimal('weighted_score', 6, 3)->nullable();
            $table->text('manager_comment')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_review_scores');
        Schema::dropIfExists('performance_reviews');
    }
};
