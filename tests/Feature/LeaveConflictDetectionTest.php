<?php

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;

beforeEach(function (): void {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $this->user = User::query()->where('email', 'piseth@bizhr.local')->firstOrFail();
    $this->employee = $this->user->employee;
    $this->leaveType = LeaveType::query()->where('company_id', $this->employee->company_id)->first();
});

describe('Leave Conflict Detection', function (): void {
    it('prevents overlapping leave requests', function (): void {
        // Create first leave request
        $startDate = now()->addDays(5)->toDateString();
        $endDate = now()->addDays(10)->toDateString();

        $this->actingAs($this->user)->post(route('leave.requests.store'), [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'reason' => 'First leave request',
        ])->assertRedirect();

        // Try to create overlapping leave request
        $this->actingAs($this->user)
            ->post(route('leave.requests.store'), [
                'leave_type_id' => $this->leaveType->id,
                'start_date' => now()->addDays(7)->toDateString(), // Overlaps with first
                'end_date' => now()->addDays(12)->toDateString(),
                'reason' => 'Overlapping leave',
            ])
            ->assertSessionHasErrors('start_date');
    });

    it('allows non-overlapping leave requests', function (): void {
        $this->leaveType = $this->leaveType ?: LeaveType::factory()->create(['company_id' => $this->employee->company_id]);

        // Create first leave
        $this->actingAs($this->user)->post(route('leave.requests.store'), [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => now()->addDays(5)->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'reason' => 'First leave',
        ])->assertRedirect();

        // Create non-overlapping leave
        $this->actingAs($this->user)
            ->post(route('leave.requests.store'), [
                'leave_type_id' => $this->leaveType->id,
                'start_date' => now()->addDays(10)->toDateString(),
                'end_date' => now()->addDays(12)->toDateString(),
                'reason' => 'Second leave',
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();
    });

    it('blocks overlapping leave during pending approval', function (): void {
        // Create pending leave using factory
        $leaveRequest = LeaveRequest::factory()->pending()->create([
            'employee_id' => $this->employee->id,
            'leave_type_id' => $this->leaveType->id,
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(10),
        ]);

        // Try to create overlapping leave
        $this->actingAs($this->user)
            ->post(route('leave.requests.store'), [
                'leave_type_id' => $this->leaveType->id,
                'start_date' => now()->addDays(7)->toDateString(),
                'end_date' => now()->addDays(12)->toDateString(),
                'reason' => 'Overlapping',
            ])
            ->assertSessionHasErrors('start_date');
    });

    it('calculates leave requests correctly', function (): void {
        // Request leave for a period
        $monday = now()->next('Monday')->addWeeks(1);
        $fridayNext = $monday->copy()->addDays(4);

        $this->actingAs($this->user)->post(route('leave.requests.store'), [
            'leave_type_id' => $this->leaveType->id,
            'start_date' => $monday->toDateString(),
            'end_date' => $fridayNext->toDateString(),
            'reason' => 'Mon-Fri week',
        ])->assertRedirect();

        $leaveRequest = LeaveRequest::query()
            ->where('employee_id', $this->employee->id)
            ->latest()
            ->firstOrFail();

        // Should have total_days calculated
        expect($leaveRequest->total_days)->toBeGreaterThan(0);
    });

    it('validates insufficient leave balance', function (): void {
        // Create leave type with limited days
        $leaveType = LeaveType::factory()->create(['company_id' => $this->employee->company_id]);

        // Request leave for many days
        $this->actingAs($this->user)
            ->post(route('leave.requests.store'), [
                'leave_type_id' => $leaveType->id,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(40)->toDateString(), // 30+ days
                'reason' => 'Long leave',
            ])
            ->assertSessionHasErrors('start_date');
    });
});
