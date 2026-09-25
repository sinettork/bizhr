<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;

beforeEach(function () {
    $this->seed([
        DatabaseSeeder::class,
        DemoDataSeeder::class,
    ]);

    $this->owner = User::query()
        ->where('email', 'demo.owner@bizhr.local')
        ->firstOrFail();

    $this->hrAdmin = User::query()
        ->where('email', 'sreypov@bizhr.local')
        ->firstOrFail();

    $this->manager = User::query()
        ->where('email', 'visal@bizhr.local')
        ->firstOrFail();
});

it('verifies Archetype E (Reference Workbench) on employment types page', function () {
    $response = $this->actingAs($this->owner)->get(route('employment-types.index'));

    $response->assertOk();
    $response->assertSee('Configured Types', false)
        ->assertSee('Assigned Staff', false)
        ->assertSee('Policy &amp; Statutory Role', false)
        ->assertSee('id="typeForm"', false)
        ->assertSee('offcanvas offcanvas-end', false)
        ->assertSee('Staff Coverage', false);
});

it('verifies Archetype D (Operational Monitor) on attendance index page', function () {
    $response = $this->actingAs($this->owner)->get(route('attendance.checkinout'));

    $response->assertOk();
    $response->assertSee('Daily Attendance', false)
        ->assertSee('Shift Monitor', false)
        ->assertSee('Total Logged', false)
        ->assertSee('Present on Site', false)
        ->assertSee('Late Punch-ins', false)
        ->assertSee('Currently on Duty', false)
        ->assertSee('Late Variance', false);
});

it('verifies Archetype A (Financial Triage) on expenses index page with explicit approve and reject controls', function () {
    $response = $this->actingAs($this->owner)->get(route('expenses.index'));

    $response->assertOk();
    $response->assertSee('Expense Claims', false)
        ->assertSee('Reimbursement Review', false)
        ->assertSee('Awaiting Review', false)
        ->assertSee('Ready to Disburse', false)
        ->assertSee('Page Claims Total', false)
        ->assertSee('Approve Claim', false)
        ->assertSee('Reject Claim', false);
});

it('verifies Archetype A (Triage & Clash Detection) on leave review page', function () {
    $response = $this->actingAs($this->hrAdmin)->get(route('leave.requests.review'));

    $response->assertOk();
    $response->assertSee('Action Required', false)
        ->assertSee('Approved Today', false)
        ->assertSee('Upcoming Outages', false)
        ->assertSee('Final approve', false);
});

it('verifies Archetype C (Hiring Kanban & Quick Advance) on recruitment pipeline page', function () {
    $response = $this->actingAs($this->owner)->get(route('recruitment.pipeline'));

    $response->assertOk();
    $response->assertSee('Active Vacancies', false)
        ->assertSee('Total in Pipeline', false)
        ->assertSee('Interviewing', false)
        ->assertSee('Offer &amp; Hired', false)
        ->assertSee('Candidate Pipeline Flow', false)
        ->assertSee('Advance', false);
});

it('verifies Archetype B (Stage-Gate Stepper) on payroll periods control center', function () {
    $response = $this->actingAs($this->owner)->get(route('payroll.periods.index'));

    $response->assertOk();
    $response->assertSee('Payroll Control Center', false)
        ->assertSee('Open Cycles', false)
        ->assertSee('Needs Approval', false)
        ->assertSee('Ready to Pay', false)
        ->assertSee('Exceptions Queue', false)
        ->assertSee('Stage-Gate Progress', false)
        ->assertSee('of 5', false);
});
