<?php

use App\Models\Employee;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Support\Facades\Storage;

it('moves legacy public profile images to private storage idempotently', function () {
    Storage::fake('public');
    Storage::fake('local');
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $user = User::query()->firstOrFail();
    $employee = Employee::query()->firstOrFail();
    $user->update(['avatar_path' => 'users/avatars/legacy.jpg']);
    $employee->update(['profile_photo' => 'employees/profile-photos/legacy.png']);
    Storage::disk('public')->put($user->avatar_path, 'avatar');
    Storage::disk('public')->put($employee->profile_photo, 'photo');

    $this->artisan('bizhr:privatize-profile-images')->assertSuccessful();
    $userPath = $user->fresh()->avatar_path;
    $employeePath = $employee->fresh()->profile_photo;
    expect($userPath)->toStartWith('private/users/')
        ->and($employeePath)->toStartWith('private/companies/');
    Storage::disk('local')->assertExists($userPath);
    Storage::disk('local')->assertExists($employeePath);
    Storage::disk('public')->assertMissing('users/avatars/legacy.jpg');
    Storage::disk('public')->assertMissing('employees/profile-photos/legacy.png');

    $this->artisan('bizhr:privatize-profile-images')->assertSuccessful();
});
