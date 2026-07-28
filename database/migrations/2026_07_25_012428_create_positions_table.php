<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('branch_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('department_id')
                ->constrained()
                ->restrictOnDelete();

            $table->string('title');
            $table->string('code', 50);

            $table->text('description')->nullable();

            $table->decimal('minimum_salary', 12, 2)
                ->nullable();

            $table->decimal('maximum_salary', 12, 2)
                ->nullable();

            $table->boolean('is_manager_position')
                ->default(false);

            $table->boolean('is_active')
                ->default(true);

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->timestamps();

            $table->unique([
                'company_id',
                'branch_id',
                'department_id',
                'code',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};