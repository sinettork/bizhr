<?php

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;

beforeEach(function (): void {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $this->user = User::query()->where('email', 'piseth@bizhr.local')->firstOrFail();
    $this->employee = Employee::query()->where('user_id', $this->user->id)->firstOrFail();
    $this->leaveType = LeaveType::query()->where('company_id', $this->employee->company_id)->firstOrFail();
});

it('lets an employee withdraw their pending leave request', function (): void {
    $leaveRequest = LeaveRequest::factory()->create([
        'employee_id' => $this->employee->id,
        'leave_type_id' => $this->leaveType->id,
        'status' => 'pending',
    ]);

    $this->actingAs($this->user)
        ->post(route('leave.requests.withdraw', $leaveRequest))
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($leaveRequest->fresh()->status)->toBe('withdrawn');
});

it('lets an employee withdraw before final HR approval', function (): void {
    $leaveRequest = LeaveRequest::factory()->create([
        'employee_id' => $this->employee->id,
        'leave_type_id' => $this->leaveType->id,
        'status' => 'manager_approved',
    ]);

    $this->actingAs($this->user)
        ->post(route('leave.requests.withdraw', $leaveRequest))
        ->assertRedirect();

    expect($leaveRequest->fresh()->status)->toBe('withdrawn');
});

it('does not let an employee withdraw a finally approved leave request', function (): void {
    $leaveRequest = LeaveRequest::factory()->create([
        'employee_id' => $this->employee->id,
        'leave_type_id' => $this->leaveType->id,
        'status' => 'approved',
    ]);

    $this->actingAs($this->user)
        ->post(route('leave.requests.withdraw', $leaveRequest))
        ->assertSessionHasErrors('status');

    expect($leaveRequest->fresh()->status)->toBe('approved');
});

it('does not let an employee withdraw another employees leave request', function (): void {
    $otherEmployee = Employee::factory()->create(['company_id' => $this->employee->company_id]);
    $leaveRequest = LeaveRequest::factory()->create([
        'employee_id' => $otherEmployee->id,
        'leave_type_id' => $this->leaveType->id,
        'status' => 'pending',
    ]);

    $this->actingAs($this->user)
        ->post(route('leave.requests.withdraw', $leaveRequest))
        ->assertNotFound();

    expect($leaveRequest->fresh()->status)->toBe('pending');
});
