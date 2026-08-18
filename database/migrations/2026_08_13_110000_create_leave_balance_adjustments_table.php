<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_balance_adjustments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('leave_balance_id')->constrained()->cascadeOnDelete();
            $table->decimal('previous_days', 5, 2);
            $table->decimal('adjustment_days', 5, 2);
            $table->text('reason');
            $table->foreignId('adjusted_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->index(['leave_balance_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_balance_adjustments');
    }
};
