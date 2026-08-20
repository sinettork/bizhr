<?php

use App\Models\Employee;
use App\Models\EmploymentHistory;
use App\Models\User;
use App\Services\EmployeeIdCardVerificationService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;

beforeEach(function (): void {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $this->owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
    $this->employeeUser = User::query()->where('email', 'piseth@bizhr.local')->firstOrFail();
    $this->employee = Employee::query()->where('user_id', $this->employeeUser->id)->firstOrFail();
});

it('keeps the same public id card token until an explicit regeneration', function (): void {
    $this->actingAs($this->owner);
    $service = app(EmployeeIdCardVerificationService::class);

    $first = $service->tokenFor($this->employee);
    $second = $service->tokenFor($this->employee->refresh());

    expect($second)->toBe($first)
        ->and($service->isValid($this->employee->refresh(), $first))->toBeTrue();

    $service->revoke($this->employee->refresh(), $this->owner);
    expect($service->isValid($this->employee->refresh(), $first))->toBeFalse();

    $service->regenerate($this->employee->refresh(), $this->owner);
    $replacement = $service->tokenFor($this->employee->refresh());

    expect($replacement)->not->toBe($first)
        ->and($service->isValid($this->employee->refresh(), $first))->toBeFalse()
        ->and($service->isValid($this->employee->refresh(), $replacement))->toBeTrue();
});

it('does not let the rehire requester approve their own request', function (): void {
    $this->employee->update(['employment_status' => 'Terminated', 'is_active' => false]);

    $this->actingAs($this->owner)
        ->post(route('employees.rehire.request', $this->employee), [
            'effective_date' => today()->addWeek()->format('Y-m-d'),
            'reason' => 'Returning to fill an approved operational vacancy.',
        ])
        ->assertRedirect();

    $this->actingAs($this->owner)
        ->post(route('employees.rehire.approve', $this->employee))
        ->assertStatus(422);

    expect($this->employee->refresh()->employment_status)->toBe('Terminated');
});

it('reactivates an employee only after a separate approver approves rehire', function (): void {
    $hr = User::query()->where('email', 'hr@bizhr.local')->firstOrFail();
    $this->employee->update(['employment_status' => 'Resigned', 'is_active' => false]);

    $this->actingAs($hr)
        ->post(route('employees.rehire.request', $this->employee), [
            'effective_date' => today()->addDays(3)->format('Y-m-d'),
            'reason' => 'Returning after an approved rehire review and workforce need.',
        ])
        ->assertRedirect();

    $this->actingAs($this->owner)
        ->post(route('employees.rehire.approve', $this->employee))
        ->assertRedirect();

    $this->employee->refresh();

    expect($this->employee->employment_status)->toBe('Active')
        ->and($this->employee->is_active)->toBeTrue()
        ->and($this->employee->rehire_approved_by)->toBe($this->owner->id)
        ->and(EmploymentHistory::query()->where('employee_id', $this->employee->id)->where('event_type', 'rehire')->exists())->toBeTrue();
});
