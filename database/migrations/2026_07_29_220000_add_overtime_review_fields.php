<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->string('overtime_review_status')->default('pending')->index();
            $table->text('overtime_review_note')->nullable();
        });

        DB::table('attendances')
            ->where('overtime_approved', true)
            ->update(['overtime_review_status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropColumn(['overtime_review_status', 'overtime_review_note']);
        });
    }
};
