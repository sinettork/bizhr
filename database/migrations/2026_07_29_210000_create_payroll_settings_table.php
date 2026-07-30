<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('khr_per_usd', 12, 2)->default(4000);
            $table->unsignedTinyInteger('working_days_per_month')->default(26);
            $table->decimal('hours_per_day', 4, 2)->default(8);
            $table->decimal('default_overtime_multiplier', 5, 2)->default(1.5);
            $table->boolean('require_overtime_approval')->default(true);
            $table->boolean('deduct_unpaid_absence')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
    }
};
