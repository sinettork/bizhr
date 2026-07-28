<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('company_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('code', 50);

            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();

            $table->text('address')->nullable();
            $table->string('city')->nullable();

            $table->string('manager_name')->nullable();

            $table->boolean('is_head_office')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};