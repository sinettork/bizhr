<?php

use App\Models\Employee;
use App\Models\TrainingCourse;
use App\Models\TrainingEnrollment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;

it('supports the training lifecycle while retaining enrollment history', function () {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
    $employeeUser = User::query()->whereHas('employee')->firstOrFail();
    $employeeUser->givePermissionTo('training.view-own');

    $this->actingAs($owner)->post(route('training.store'), [
        'title' => 'Production privacy controls', 'description' => 'Required privacy and secure handling course.',
        'duration_minutes' => 90, 'is_mandatory' => 1,
    ])->assertRedirect()->assertSessionHasNoErrors();
    $course = TrainingCourse::query()->where('title', 'Production privacy controls')->firstOrFail();

    $this->actingAs($owner)->post(route('training.enroll', $course), [
        'employee_id' => $employeeUser->employee->id, 'due_date' => today()->addWeek()->toDateString(),
    ])->assertRedirect()->assertSessionHasNoErrors();
    $enrollment = TrainingEnrollment::query()->where('training_course_id', $course->id)->where('employee_id', $employeeUser->employee->id)->firstOrFail();

    $this->actingAs($owner)->delete(route('training.destroy', $course))->assertRedirect()->assertSessionHasErrors('course');
    expect($course->fresh()->deleted_at)->toBeNull();

    $this->actingAs($employeeUser)->post(route('training.progress', $enrollment), ['progress' => 100, 'score' => 88.5])->assertRedirect()->assertSessionHasNoErrors();
    expect($enrollment->fresh()->status)->toBe('completed')->and((float) $enrollment->fresh()->score)->toBe(88.5);

    $this->actingAs($owner)->delete(route('training.destroy', $course))->assertRedirect()->assertSessionHasNoErrors();
    expect($course->fresh()->trashed())->toBeTrue()->and($enrollment->fresh())->not->toBeNull();
});

it('offers every active company employee for training assignment', function () {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
    $activeEmployee = Employee::query()->where('is_active', true)->firstOrFail();

    $this->actingAs($owner)->get(route('training.index'))->assertOk()->assertSee($activeEmployee->getFullName());
});
