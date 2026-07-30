<?php

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;

test('sensitive audit logs are append only', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $employee = new Employee();
    $employee->id = 123;

    $log = AuditLog::record($employee, 'viewed_sensitive_profile', [], [
        'fields' => ['base_salary', 'bank_details'],
        'own_record' => true,
    ]);

    expect($log->action)->toBe('viewed_sensitive_profile')
        ->and($log->user_id)->toBe($user->id)
        ->and($log->new_values['fields'])->toContain('base_salary');

    expect(fn () => $log->update(['action' => 'changed']))
        ->toThrow(LogicException::class);
});

test('document audit metadata does not contain document contents', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $document = new EmployeeDocument();
    $document->id = 456;

    $log = AuditLog::record($document, 'downloaded', [], [
        'employee_id' => 10,
        'document_type' => 'contract',
        'original_name' => 'contract.pdf',
    ]);

    expect($log->new_values)->toHaveKeys([
        'employee_id',
        'document_type',
        'original_name',
    ])->not->toHaveKey('contents');
});
