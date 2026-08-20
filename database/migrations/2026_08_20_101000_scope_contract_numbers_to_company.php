<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employment_contracts', function (Blueprint $table): void {
            $table->dropUnique('employment_contracts_contract_number_unique');
            $table->unique(['company_id', 'contract_number'], 'contracts_company_contract_number_unique');
        });
    }

    public function down(): void
    {
        Schema::table('employment_contracts', function (Blueprint $table): void {
            $table->dropUnique('contracts_company_contract_number_unique');
            $table->unique('contract_number', 'employment_contracts_contract_number_unique');
        });
    }
};
