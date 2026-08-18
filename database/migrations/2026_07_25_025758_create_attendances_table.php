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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
            $table->date('work_date');
            $table->time('scheduled_start')->nullable(); // Expected check-in time from shift
            $table->time('scheduled_end')->nullable();   // Expected check-out time from shift
            $table->dateTime('check_in_at')->nullable();  // Actual check-in time
            $table->dateTime('check_out_at')->nullable(); // Actual check-out time
            $table->string('check_in_method')->default('web'); // web, mobile, qr, gps, fingerprint, face, manual
            $table->string('check_out_method')->nullable();
            $table->string('check_in_location')->nullable(); // GPS coordinates or location name
            $table->string('check_out_location')->nullable();
            $table->unsignedSmallInteger('late_minutes')->default(0); // Minutes late
            $table->unsignedSmallInteger('early_leave_minutes')->default(0); // Minutes left early
            $table->unsignedInteger('worked_minutes')->default(0); // Total minutes worked
            $table->unsignedInteger('overtime_minutes')->default(0); // Overtime minutes
            $table->string('status')->default('present'); // present, late, absent, on_leave, half_day, holiday, rest_day, remote_work, business_trip
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_id', 'work_date']);
            $table->index(['branch_id', 'work_date']);
            $table->index('status');
            $table->unique(['employee_id', 'work_date']); // One attendance record per employee per day
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
