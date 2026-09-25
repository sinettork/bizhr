<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->string('local_name')->nullable()->after('name');
            $table->unsignedInteger('headcount_capacity')->nullable()->after('manager_name');
            $table->string('phone_extension', 30)->nullable()->after('phone');
            $table->string('telegram_username', 100)->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropColumn(['local_name', 'headcount_capacity', 'phone_extension', 'telegram_username']);
        });
    }
};
