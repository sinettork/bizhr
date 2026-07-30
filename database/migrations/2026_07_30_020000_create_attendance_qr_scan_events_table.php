<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_qr_scan_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('attendance_qr_session_id')
                ->constrained('attendance_qr_sessions')
                ->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('action', 20);
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy_meters', 8, 2)->nullable();
            $table->unsignedInteger('distance_meters');
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->unique(
                ['attendance_qr_session_id', 'employee_id'],
                'attendance_qr_session_employee_unique',
            );
            $table->index(['employee_id', 'recorded_at']);
            $table->index(['branch_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_qr_scan_events');
    }
};
