<?php

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AttendanceQrSession;
use App\Models\Branch;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;

beforeEach(function (): void {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
});

it('lets an employee request a correction and an authorized reviewer apply it', function () {
    $employeeUser = User::query()->where('email', 'piseth@bizhr.local')->firstOrFail();
    $attendance = Attendance::query()->where('employee_id', $employeeUser->employee->id)->latest('work_date')->firstOrFail();
    $requestedIn = $attendance->check_in_at->copy()->addMinutes(5);

    $this->actingAs($employeeUser)->post(route('attendance.corrections.store'), ['attendance_id' => $attendance->id, 'requested_check_in' => $requestedIn->format('Y-m-d\TH:i'), 'reason' => 'Time clock was unavailable.'])->assertRedirect()->assertSessionHasNoErrors();
    $correction = AttendanceCorrection::query()->where('attendance_id', $attendance->id)->firstOrFail();

    $owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
    $this->actingAs($owner)->post(route('attendance.corrections.approve', $correction))->assertRedirect();

    expect($correction->fresh()->status)->toBe('approved')
        ->and($attendance->fresh()->check_in_at->format('Y-m-d H:i'))->toBe($requestedIn->format('Y-m-d H:i'));
});

it('lets the requesting employee reopen a rejected correction with an auditable reason', function () {
    $employeeUser = User::query()->where('email', 'piseth@bizhr.local')->firstOrFail();
    $attendance = Attendance::query()->where('employee_id', $employeeUser->employee->id)->latest('work_date')->firstOrFail();
    $correction = AttendanceCorrection::query()->create([
        'attendance_id' => $attendance->id,
        'employee_id' => $employeeUser->employee->id,
        'requested_check_in' => $attendance->check_in_at->copy()->addMinutes(5),
        'reason' => 'The attendance device was unavailable.',
        'status' => 'rejected',
        'reviewed_by' => User::query()->where('email', 'demo.owner@bizhr.local')->value('id'),
        'reviewed_at' => now(),
        'review_note' => 'Please provide more information.',
    ]);

    $this->actingAs($employeeUser)
        ->post(route('attendance.corrections.reopen', $correction), ['reason' => 'The branch device outage was confirmed by the manager.'])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($correction->fresh())
        ->status->toBe('pending')
        ->reopened_by->toBe($employeeUser->id)
        ->reopen_reason->toBe('The branch device outage was confirmed by the manager.')
        ->reviewed_by->toBeNull();
});

it('records a branch QR check-in once per short-lived session', function () {
    $employeeUser = User::query()->where('email', 'piseth@bizhr.local')->firstOrFail();
    $branch = Branch::query()->findOrFail($employeeUser->employee->branch_id);
    $branch->update(['attendance_qr_enabled' => true, 'latitude' => '11.5564000', 'longitude' => '104.9282000', 'attendance_radius' => 200]);
    $branch->regenerateAttendanceQrToken();
    $token = str_repeat('A', 80);
    AttendanceQrSession::query()->create(['branch_id' => $branch->id, 'token_hash' => hash('sha256', $token), 'expires_at' => now()->addMinute(), 'created_by' => $employeeUser->id]);

    $this->actingAs($employeeUser)->postJson(route('attendance.qr.record', $token), ['latitude' => 11.5564, 'longitude' => 104.9282, 'accuracy' => 10])->assertOk()->assertJsonPath('action', 'check_in');
    $this->actingAs($employeeUser)->postJson(route('attendance.qr.record', $token), ['latitude' => 11.5564, 'longitude' => 104.9282, 'accuracy' => 10])->assertStatus(422);
});
