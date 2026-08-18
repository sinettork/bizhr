<?php

use App\Models\Employee;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $this->owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
});

it('uploads and removes the signed in user avatar', function () {
    Storage::fake('local');

    $this->actingAs($this->owner)->put(route('profile.update'), [
        'name' => $this->owner->name,
        'email' => $this->owner->email,
        'avatar' => UploadedFile::fake()->image('avatar.jpg', 400, 400),
    ])->assertRedirect()->assertSessionHasNoErrors();

    $path = $this->owner->fresh()->avatar_path;
    expect($path)->not->toBeNull();
    Storage::disk('local')->assertExists($path);
    expect($path)->toStartWith('private/users/');
    $avatarResponse = $this->actingAs($this->owner)->get(route('profile.avatar'))->assertOk();
    expect($avatarResponse->headers->get('Cache-Control'))->toContain('private')->toContain('max-age=300')->toContain('no-transform');

    $this->actingAs($this->owner)->put(route('profile.update'), [
        'name' => $this->owner->name,
        'email' => $this->owner->email,
        'remove_avatar' => '1',
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($this->owner->fresh()->avatar_path)->toBeNull();
    Storage::disk('local')->assertMissing($path);
});

it('updates employee photos and renders a printable id card', function () {
    Storage::fake('local');
    Storage::fake('public');
    $employee = Employee::query()->where('is_active', true)->firstOrFail();
    $company = $employee->company;
    $company->update(['logo_path' => 'company/logos/logo.png']);
    Storage::disk('public')->put($company->logo_path, 'logo');
    $employee->update([
        'id_card_expiry_date' => now()->addYear()->toDateString(),
        'emergency_contact_name' => 'Jane Doe',
        'emergency_contact_phone' => '+855 12 345 678',
    ]);

    $payload = [
        'employee_code' => $employee->employee_code,
        'branch_id' => $employee->branch_id,
        'department_id' => $employee->department_id,
        'position_id' => $employee->position_id,
        'employment_type_id' => $employee->employment_type_id,
        'first_name' => $employee->first_name,
        'last_name' => $employee->last_name,
        'hire_date' => $employee->hire_date->toDateString(),
        'employment_status' => $employee->employment_status,
        'salary_currency' => $employee->salary_currency,
        'is_active' => '1',
        'id_card_expiry_date' => $employee->id_card_expiry_date->toDateString(),
    ];

    $this->actingAs($this->owner)->put(route('employees.update', $employee), [
        ...$payload,
        'profile_photo' => UploadedFile::fake()->image('employee.jpg', 500, 500),
    ])->assertRedirect(route('employees.show', $employee))->assertSessionHasNoErrors();

    $path = $employee->fresh()->profile_photo;
    Storage::disk('local')->assertExists($path);
    expect($path)->toStartWith('private/companies/');
    $photoResponse = $this->actingAs($this->owner)->get(route('employees.photo', $employee))->assertOk();
    expect($photoResponse->headers->get('Cache-Control'))->toContain('private')->toContain('max-age=300')->toContain('no-transform');

    $this->actingAs($this->owner)->get(route('employees.id-card', $employee))
        ->assertOk()
        ->assertSee('Employee ID card')
        ->assertSee($employee->employee_code)
        ->assertSee('Expiry')
        ->assertSee('Emergency contact');

    $this->actingAs($this->owner)->get(route('employees.id-card.qr', $employee))
        ->assertOk()
        ->assertHeader('content-type', 'image/png');

    $this->actingAs($this->owner)->put(route('employees.update', $employee), [
        ...$payload,
        'remove_profile_photo' => '1',
    ])->assertRedirect(route('employees.show', $employee))->assertSessionHasNoErrors();

    expect($employee->fresh()->profile_photo)->toBeNull();
    Storage::disk('local')->assertMissing($path);
});

it('does not expose an employee photo to an unrelated account', function () {
    Storage::fake('local');
    $employee = Employee::query()->where('is_active', true)->firstOrFail();
    $employee->update(['profile_photo' => "private/companies/{$employee->company_id}/employees/{$employee->id}/profile-photos/private.jpg"]);
    Storage::disk('local')->put($employee->profile_photo, 'private image');
    $unrelated = User::factory()->create();

    $this->actingAs($unrelated)->get(route('employees.photo', $employee))->assertForbidden();
});

it('verifies a current ID card publicly without exposing employee PII', function () {
    $employee = Employee::query()->where('is_active', true)->firstOrFail();
    $token = str_repeat('V', 64);
    $employee->update([
        'id_card_verification_token_hash' => hash('sha256', $token),
        'id_card_verification_expires_at' => now()->addDay(),
        'id_card_verification_revoked_at' => null,
    ]);

    $this->get(route('employees.id-card.verify', [$employee, $token]))
        ->assertOk()
        ->assertJsonPath('valid', true)
        ->assertJsonPath('employee_code', $employee->employee_code)
        ->assertJsonMissing(['name' => $employee->getFullName(), 'employee_id' => $employee->id]);

    $employee->update(['id_card_verification_revoked_at' => now()]);

    $this->get(route('employees.id-card.verify', [$employee, $token]))->assertNotFound();
});
