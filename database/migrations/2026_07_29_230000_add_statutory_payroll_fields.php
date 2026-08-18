<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->boolean('is_tax_resident')->default(true);
            $table->unsignedTinyInteger('tax_dependents')->default(0);
            $table->string('nssf_number', 50)->nullable()->index();
            $table->boolean('nssf_enrolled')->default(false);
        });

        Schema::table('payroll_settings', function (Blueprint $table): void {
            $table->boolean('salary_tax_enabled')->default(true);
            $table->unsignedInteger('dependent_relief_khr')->default(150000);
            $table->decimal('nssf_employee_health_rate', 5, 2)->default(1.30);
            $table->decimal('nssf_employer_health_rate', 5, 2)->default(1.30);
            $table->decimal('nssf_employer_risk_rate', 5, 2)->default(0.80);
        });

        Schema::table('payroll_items', function (Blueprint $table): void {
            $table->decimal('taxable_salary_khr', 16, 2)->default(0);
            $table->decimal('nssf_employee_amount', 14, 2)->default(0);
            $table->decimal('nssf_employer_amount', 14, 2)->default(0);
            $table->decimal('employer_total_cost', 14, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('payroll_items', function (Blueprint $table): void {
            $table->dropColumn([
                'taxable_salary_khr',
                'nssf_employee_amount',
                'nssf_employer_amount',
                'employer_total_cost',
            ]);
        });

        Schema::table('payroll_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'salary_tax_enabled',
                'dependent_relief_khr',
                'nssf_employee_health_rate',
                'nssf_employer_health_rate',
                'nssf_employer_risk_rate',
            ]);
        });

        Schema::table('employees', function (Blueprint $table): void {
            $table->dropColumn([
                'is_tax_resident',
                'tax_dependents',
                'nssf_number',
                'nssf_enrolled',
            ]);
        });
    }
};
