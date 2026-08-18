<?php

use App\Models\Announcement;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;

it('shows targeted published announcements and records acknowledgement once', function () {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $user = User::query()->whereHas('employee')->whereNotNull('email_verified_at')->firstOrFail();
    $announcement = Announcement::query()->create(['company_id' => $user->employee->company_id, 'created_by' => $user->id, 'title' => 'Required policy notice', 'content' => 'Please read and acknowledge this required policy notice.', 'audience_type' => 'all', 'published_at' => now()->subMinute(), 'requires_acknowledgement' => true]);

    $this->actingAs($user)->get(route('announcements.feed'))->assertOk()->assertSee('Required policy notice');
    $this->actingAs($user)->post(route('announcements.acknowledge', $announcement))->assertRedirect();
    $this->actingAs($user)->post(route('announcements.acknowledge', $announcement))->assertRedirect();
    expect($announcement->acknowledgements()->where('users.id', $user->id)->count())->toBe(1);
});

it('prevents acknowledgement outside the intended audience', function () {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $user = User::query()->whereHas('employee')->firstOrFail();
    $otherDepartment = Department::query()->where('company_id', $user->employee->company_id)
        ->whereKeyNot($user->employee->department_id)->firstOrFail();
    $announcement = Announcement::query()->create([
        'company_id' => $user->employee->company_id,
        'created_by' => $user->id,
        'title' => 'Other department notice',
        'content' => 'This notice belongs to another department.',
        'audience_type' => 'department',
        'department_id' => $otherDepartment->id,
        'published_at' => now()->subMinute(),
        'requires_acknowledgement' => true,
    ]);

    $this->actingAs($user)->post(route('announcements.acknowledge', $announcement))->assertNotFound();
    expect($announcement->acknowledgements()->count())->toBe(0);
});
