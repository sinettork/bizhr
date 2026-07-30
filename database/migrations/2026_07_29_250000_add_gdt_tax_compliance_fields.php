<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_periods', function (Blueprint $table): void {
            $table->decimal('tax_exchange_rate_khr', 12, 2)->nullable();
            $table->date('tax_rate_date')->nullable();
            $table->string('tax_rate_source')->nullable();
            $table->timestamp('tax_rate_locked_at')->nullable();
        });

        Schema::table('payroll_adjustments', function (Blueprint $table): void {
            $table->boolean('is_fringe_benefit')->default(false)->index();
            $table->decimal('fringe_benefit_tax_rate', 5, 2)->default(20);
        });

        Schema::table('payroll_items', function (Blueprint $table): void {
            $table->decimal('salary_tax_exchange_rate', 12, 2)->nullable();
            $table->decimal('fringe_benefit_amount', 14, 2)->default(0);
            $table->decimal('fringe_benefit_tax_amount', 14, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('payroll_items', function (Blueprint $table): void {
            $table->dropColumn([
                'salary_tax_exchange_rate',
                'fringe_benefit_amount',
                'fringe_benefit_tax_amount',
            ]);
        });

        Schema::table('payroll_adjustments', function (Blueprint $table): void {
            $table->dropColumn(['is_fringe_benefit', 'fringe_benefit_tax_rate']);
        });

        Schema::table('payroll_periods', function (Blueprint $table): void {
            $table->dropColumn([
                'tax_exchange_rate_khr',
                'tax_rate_date',
                'tax_rate_source',
                'tax_rate_locked_at',
            ]);
        });
    }
};
