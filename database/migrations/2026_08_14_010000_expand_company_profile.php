<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->string('local_name')->nullable();
            $table->string('logo_path')->nullable();
            $table->unsignedTinyInteger('fiscal_year_start_month')->default(1);
            $table->unsignedTinyInteger('week_start_day')->default(1);
            $table->string('number_format', 30)->default('1,234.56');
            $table->string('locale', 10)->default('en');
        });
    }

    public function down(): void
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn(['local_name', 'logo_path', 'fiscal_year_start_month', 'week_start_day', 'number_format', 'locale']);
        });
    }
};
