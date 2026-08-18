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
        Schema::create('work_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name'); // Morning Shift, Afternoon Shift, Night Shift, etc.
            $table->string('code')->unique();
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('break_minutes')->default(60); // Break duration in minutes
            $table->unsignedSmallInteger('late_grace_minutes')->default(5); // Grace period for lateness
            $table->unsignedSmallInteger('early_leave_grace_minutes')->default(5); // Grace period for early leave
            $table->boolean('is_night_shift')->default(false); // Shift crosses midnight
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_shifts');
    }
};
