<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('legal_name')->nullable();

            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('website')->nullable();

            $table->string('registration_number')->nullable();
            $table->string('tax_id')->nullable();

            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('Cambodia');

            $table->char('currency', 3)->default('USD');
            $table->string('timezone')->default('Asia/Phnom_Penh');
            $table->string('date_format')->default('d/m/Y');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};