<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('payroll_period_id')->unique()->constrained()->restrictOnDelete();
            $table->string('payment_method', 40);
            $table->string('reference_number', 100)->nullable()->unique();
            $table->timestamp('paid_at');
            $table->unsignedInteger('item_count');
            $table->decimal('total_usd', 16, 2)->default(0);
            $table->decimal('total_khr', 18, 2)->default(0);
            $table->foreignId('recorded_by')->constrained('users')->restrictOnDelete();
            $table->text('notes')->nullable();
            $table->string('checksum', 64);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_payments');
    }
};
