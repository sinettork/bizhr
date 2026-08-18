<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->string('id_card_verification_token_hash', 64)->nullable()->unique()->after('id_card_expiry_date');
            $table->timestamp('id_card_verification_expires_at')->nullable()->after('id_card_verification_token_hash');
            $table->timestamp('id_card_verification_revoked_at')->nullable()->after('id_card_verification_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->dropUnique(['id_card_verification_token_hash']);
            $table->dropColumn(['id_card_verification_token_hash', 'id_card_verification_expires_at', 'id_card_verification_revoked_at']);
        });
    }
};
