<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_vacancies', function (Blueprint $table) {
            $table->id(); $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->restrictOnDelete();
            $table->string('title'); $table->text('description'); $table->unsignedInteger('openings')->default(1);
            $table->date('open_date'); $table->date('close_date')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->softDeletes(); $table->timestamps();
        });
        Schema::create('job_applicants', function (Blueprint $table) {
            $table->id(); $table->foreignId('job_vacancy_id')->constrained()->restrictOnDelete();
            $table->string('full_name'); $table->string('email'); $table->string('phone');
            $table->string('cv_path')->nullable(); $table->string('cv_original_name')->nullable();
            $table->string('status', 30)->default('applied'); $table->text('hr_note')->nullable();
            $table->foreignId('hired_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('applied_at')->useCurrent(); $table->softDeletes(); $table->timestamps();
            $table->unique(['job_vacancy_id','email']);
        });
        Schema::create('job_interviews', function (Blueprint $table) {
            $table->id(); $table->foreignId('job_applicant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('interviewer_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('scheduled_at'); $table->string('location')->nullable();
            $table->unsignedTinyInteger('score')->nullable(); $table->text('feedback')->nullable();
            $table->string('status',20)->default('scheduled'); $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('job_offers', function (Blueprint $table) {
            $table->id(); $table->foreignId('job_applicant_id')->constrained()->restrictOnDelete();
            $table->decimal('salary_amount',14,2); $table->char('salary_currency',3);
            $table->date('proposed_start_date'); $table->date('expires_at');
            $table->string('status',20)->default('draft'); $table->text('terms')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable(); $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });
        Schema::create('training_courses', function (Blueprint $table) {
            $table->id(); $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('title'); $table->text('description')->nullable(); $table->unsignedInteger('duration_minutes')->default(0);
            $table->boolean('is_mandatory')->default(false); $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete(); $table->softDeletes(); $table->timestamps();
        });
        Schema::create('training_enrollments', function (Blueprint $table) {
            $table->id(); $table->foreignId('training_course_id')->constrained()->restrictOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->string('status',20)->default('assigned'); $table->unsignedTinyInteger('progress')->default(0);
            $table->decimal('score',5,2)->nullable(); $table->date('due_date')->nullable();
            $table->timestamp('started_at')->nullable(); $table->timestamp('completed_at')->nullable();
            $table->foreignId('assigned_by')->constrained('users')->restrictOnDelete(); $table->timestamps();
            $table->unique(['training_course_id','employee_id']);
        });
        Schema::create('assets', function (Blueprint $table) {
            $table->id(); $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('asset_code'); $table->string('name'); $table->string('category');
            $table->string('serial_number')->nullable(); $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost',14,2)->nullable(); $table->char('currency',3)->default('USD');
            $table->string('condition',20)->default('good'); $table->string('status',20)->default('available');
            $table->text('notes')->nullable(); $table->softDeletes(); $table->timestamps();
            $table->unique(['company_id','asset_code']);
        });
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id(); $table->foreignId('asset_id')->constrained()->restrictOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete();
            $table->date('assigned_date'); $table->date('expected_return_date')->nullable();
            $table->date('returned_date')->nullable(); $table->string('condition_out',20);
            $table->string('condition_in',20)->nullable(); $table->string('status',20)->default('assigned');
            $table->text('notes')->nullable(); $table->foreignId('assigned_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamps();
        });
        Schema::create('expense_claims', function (Blueprint $table) {
            $table->id(); $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->foreignId('employee_id')->constrained()->restrictOnDelete(); $table->date('expense_date');
            $table->string('category'); $table->decimal('amount',14,2); $table->char('currency',3);
            $table->text('business_purpose'); $table->string('receipt_path')->nullable();
            $table->string('receipt_original_name')->nullable(); $table->string('status',30)->default('pending_manager');
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete(); $table->timestamp('manager_reviewed_at')->nullable();
            $table->foreignId('accountant_id')->nullable()->constrained('users')->nullOnDelete(); $table->timestamp('accountant_reviewed_at')->nullable();
            $table->text('review_note')->nullable(); $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference')->nullable(); $table->softDeletes(); $table->timestamps();
        });
        Schema::create('announcements', function (Blueprint $table) {
            $table->id(); $table->foreignId('company_id')->constrained()->restrictOnDelete();
            $table->string('title'); $table->text('content'); $table->string('audience_type',20)->default('all');
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('published_at')->nullable(); $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->boolean('is_pinned')->default(false); $table->softDeletes(); $table->timestamps();
        });
    }
    public function down(): void
    {
        foreach (['announcements','expense_claims','asset_assignments','assets','training_enrollments','training_courses','job_offers','job_interviews','job_applicants','job_vacancies'] as $table) Schema::dropIfExists($table);
    }
};
