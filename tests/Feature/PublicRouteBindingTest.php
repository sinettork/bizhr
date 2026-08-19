<?php

use App\Models\Employee;

it('resolves exposed records only by public id', function () {
    $employee = Employee::factory()->create();

    expect($employee->getRouteKeyName())->toBe('public_id');
    expect($employee->resolveRouteBinding((string) $employee->getKey()))->toBeNull();

    $resolved = $employee->resolveRouteBinding($employee->public_id);

    expect($resolved)->not->toBeNull();
    expect($resolved?->is($employee))->toBeTrue();
});
