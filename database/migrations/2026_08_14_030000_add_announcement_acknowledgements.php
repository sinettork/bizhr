<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table): void {
            $table->boolean('is_urgent')->default(false)->after('is_pinned');
            $table->boolean('requires_acknowledgement')->default(false)->after('is_urgent');
        });
        Schema::create('announcement_acknowledgements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('announcement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('acknowledged_at');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->unique(['announcement_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_acknowledgements');
        Schema::table('announcements', fn (Blueprint $table) => $table->dropColumn(['is_urgent', 'requires_acknowledgement']));
    }
};
