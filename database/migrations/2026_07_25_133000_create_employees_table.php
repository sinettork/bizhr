<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('company_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('branch_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('department_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('position_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('employment_type_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            // Employee identification
            $table->string('employee_code', 50)->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('full_name_km')->nullable();
            $table->string('full_name_en')->nullable();

            // Personal information
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();

            // Identification documents
            $table->string('national_id', 20)->nullable();
            $table->string('passport_number', 30)->nullable();

            // Address
            $table->string('address')->nullable();
            $table->string('city')->nullable();

            // Profile and documents
            $table->string('profile_photo')->nullable();

            // Employment dates
            $table->date('hire_date');
            $table->date('probation_end_date')->nullable();
            $table->date('contract_start_date')->nullable();
            $table->date('contract_end_date')->nullable();

            // Salary information (encrypted in production)
            $table->decimal('base_salary', 12, 2)->nullable();
            $table->string('salary_currency', 3)->default('USD');

            // Payment information
            $table->string('payment_method')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_name')->nullable();
            $table->string('bank_account_number')->nullable();

            // Emergency contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();

            // Status
            $table->enum('employment_status', [
                'Draft',
                'Active',
                'On probation',
                'On leave',
                'Suspended',
                'Resigned',
                'Terminated',
                'Retired',
            ])->default('Draft');

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            // Indexes for common queries
            $table->index('company_id');
            $table->index('branch_id');
            $table->index('department_id');
            $table->index('employment_status');
            $table->index('hire_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
