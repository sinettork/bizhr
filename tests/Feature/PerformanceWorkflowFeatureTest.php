<?php

use App\Models\Employee;
use App\Models\EmployeeGoal;
use App\Models\PerformanceReview;
use App\Models\User;
use App\Services\PerformanceReviewService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;

it('creates a weighted review and enforces maker checker approval', function () {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
    $manager = User::query()->whereHas('employee')->where('id', '!=', $owner->id)->firstOrFail();
    $employee = Employee::query()->where('company_id', $manager->employee->company_id)->where('id', '!=', $manager->employee->id)->firstOrFail();
    EmployeeGoal::query()->where('employee_id', $employee->id)->delete();
    EmployeeGoal::query()->create(['company_id' => $employee->company_id, 'employee_id' => $employee->id, 'title' => 'Quality', 'measurement_unit' => '%', 'target_value' => 100, 'current_value' => 90, 'weight' => 100, 'scoring_direction' => 'higher_is_better', 'start_date' => today()->startOfMonth(), 'due_date' => today()->endOfMonth(), 'status' => 'active', 'assigned_by' => $manager->id, 'activated_at' => now()]);

    $review = app(PerformanceReviewService::class)->create($employee, $manager, today()->startOfMonth()->toDateString(), today()->endOfMonth()->toDateString());
    $criterion = $review->scores->firstOrFail();
    $review = app(PerformanceReviewService::class)->submit($review, $manager, [$criterion->id => 4], [$criterion->id => 'Strong and consistent delivery.'], ['strengths' => 'Quality', 'areas_for_improvement' => 'Speed', 'manager_comment' => 'Solid period.']);
    expect($review->status)->toBe('manager_submitted')->and((float) $review->overall_score)->toBe(4.0);

    expect(fn () => app(PerformanceReviewService::class)->approve($review, $manager))->toThrow(DomainException::class);
    $approved = app(PerformanceReviewService::class)->approve($review, $owner);
    expect($approved->status)->toBe('hr_approved')->and($approved->snapshot_checksum)->toHaveLength(64);
});

it('allows an employee to acknowledge only their own approved review', function () {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $employeeUser = User::query()->whereHas('employee')->firstOrFail();
    $review = PerformanceReview::query()->where('employee_id', $employeeUser->employee->id)->firstOrFail();
    $review->update(['status' => 'hr_approved']);
    $this->actingAs($employeeUser)->post(route('performance.my-reviews.acknowledge', $review), ['comment' => 'Reviewed and acknowledged.'])->assertRedirect();
    expect($review->fresh()->status)->toBe('employee_acknowledged');
});

it('lets the assigned reviewer score and submit a draft through the production route', function () {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $manager = User::query()->whereHas('employee')->firstOrFail();
    $manager->givePermissionTo('performance.review');
    $employee = Employee::query()->where('company_id', $manager->employee->company_id)->whereKeyNot($manager->employee->id)->firstOrFail();
    EmployeeGoal::query()->where('employee_id', $employee->id)->delete();
    EmployeeGoal::query()->create(['company_id' => $employee->company_id, 'employee_id' => $employee->id, 'title' => 'Delivery', 'measurement_unit' => '%', 'target_value' => 100, 'current_value' => 95, 'weight' => 100, 'scoring_direction' => 'higher_is_better', 'start_date' => today()->startOfMonth(), 'due_date' => today()->endOfMonth(), 'status' => 'active', 'assigned_by' => $manager->id, 'activated_at' => now()]);
    $review = app(PerformanceReviewService::class)->create($employee, $manager, today()->startOfMonth()->toDateString(), today()->endOfMonth()->toDateString());
    $criterion = $review->scores->firstOrFail();

    $this->actingAs($manager)->post(route('performance.reviews.submit', $review), [
        'scores' => [$criterion->id => 4],
        'comments' => [$criterion->id => 'Reliable delivery against the agreed target.'],
        'strengths' => 'Execution', 'areas_for_improvement' => 'Documentation',
        'manager_comment' => 'Ready for HR review.',
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($review->fresh()->status)->toBe('manager_submitted')->and((float) $review->fresh()->overall_score)->toBe(4.0);
});
