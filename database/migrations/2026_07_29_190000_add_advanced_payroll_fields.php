<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->string('pay_type')->default('monthly');
            $table->decimal('daily_rate', 14, 2)->nullable();
            $table->decimal('hourly_rate', 14, 4)->nullable();
            $table->decimal('overtime_multiplier', 5, 2)->default(1.50);
        });

        Schema::table('attendances', function (Blueprint $table): void {
            $table->boolean('overtime_approved')->default(false)->index();
            $table->foreignId('overtime_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('overtime_approved_at')->nullable();
        });

        Schema::table('payroll_items', function (Blueprint $table): void {
            $table->string('pay_type')->default('monthly');
            $table->unsignedInteger('scheduled_minutes')->default(0);
            $table->unsignedInteger('worked_minutes')->default(0);
            $table->unsignedInteger('paid_leave_minutes')->default(0);
            $table->unsignedInteger('unpaid_leave_minutes')->default(0);
            $table->unsignedInteger('holiday_minutes')->default(0);
            $table->unsignedInteger('absent_minutes')->default(0);
            $table->unsignedInteger('late_minutes')->default(0);
            $table->unsignedInteger('early_leave_minutes')->default(0);
            $table->unsignedInteger('approved_overtime_minutes')->default(0);
            $table->decimal('payable_base_amount', 14, 2)->default(0);
            $table->unsignedInteger('exception_count')->default(0);
            $table->json('calculation_details')->nullable();
        });

        Schema::create('public_holidays', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('holiday_date');
            $table->boolean('is_paid')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'holiday_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('public_holidays');

        Schema::table('payroll_items', function (Blueprint $table): void {
            $table->dropColumn([
                'pay_type',
                'scheduled_minutes',
                'worked_minutes',
                'paid_leave_minutes',
                'unpaid_leave_minutes',
                'holiday_minutes',
                'absent_minutes',
                'late_minutes',
                'early_leave_minutes',
                'approved_overtime_minutes',
                'payable_base_amount',
                'exception_count',
                'calculation_details',
            ]);
        });

        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropForeign(['overtime_approved_by']);
            $table->dropColumn(['overtime_approved', 'overtime_approved_by', 'overtime_approved_at']);
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->dropColumn(['pay_type', 'daily_rate', 'hourly_rate', 'overtime_multiplier']);
        });
    }
};
