<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained()->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('total_days', 5, 2);
            $table->text('reason')->nullable();
            $table->string('attachment')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('manager_reviewed_at')->nullable();
            $table->text('manager_note')->nullable();
            $table->foreignId('hr_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('hr_reviewed_at')->nullable();
            $table->text('hr_note')->nullable();
            $table->softDeletes();
            $table->timestamps();
            $table->index(['employee_id', 'start_date', 'end_date']);
            $table->index(['status', 'start_date']);
        });
    }

    public function down(): void { Schema::dropIfExists('leave_requests'); }
};
