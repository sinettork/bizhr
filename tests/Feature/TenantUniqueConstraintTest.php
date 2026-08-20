<?php

use Illuminate\Support\Facades\Schema;

it('scopes employee codes to a company at the database layer', function (): void {
    $indexes = collect(Schema::getIndexes('employees'));
    $index = $indexes->first(fn (array $index): bool => ($index['name'] ?? null) === 'employees_company_employee_code_unique');

    expect($index)->not->toBeNull()
        ->and($index['unique'] ?? false)->toBeTrue()
        ->and($index['columns'] ?? [])->toBe(['company_id', 'employee_code']);
});

it('scopes contract numbers to a company at the database layer', function (): void {
    $indexes = collect(Schema::getIndexes('employment_contracts'));
    $index = $indexes->first(fn (array $index): bool => ($index['name'] ?? null) === 'contracts_company_contract_number_unique');

    expect($index)->not->toBeNull()
        ->and($index['unique'] ?? false)->toBeTrue()
        ->and($index['columns'] ?? [])->toBe(['company_id', 'contract_number']);
});
