<?php

use App\Models\Company;
use App\Models\JobApplicant;
use App\Models\JobVacancy;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('creates a vacancy and advances a candidate only through valid stages', function () {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    Storage::fake('local');
    $owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();

    $this->actingAs($owner)->post(route('recruitment.vacancies.store'), ['title' => 'Production HR Analyst', 'description' => 'Own HR controls and production readiness evidence.', 'openings' => 1, 'open_date' => today()->toDateString(), 'close_date' => today()->addMonth()->toDateString()])->assertRedirect()->assertSessionHasNoErrors();
    $vacancy = JobVacancy::query()->where('title', 'Production HR Analyst')->firstOrFail();
    $this->actingAs($owner)->post(route('recruitment.applicants.store', $vacancy), ['full_name' => 'Candidate One', 'email' => 'candidate.one@example.test', 'phone' => '012345678', 'cv' => UploadedFile::fake()->create('candidate.pdf', 100, 'application/pdf')])->assertRedirect()->assertSessionHasNoErrors();
    $applicant = JobApplicant::query()->where('email', 'candidate.one@example.test')->firstOrFail();
    expect($applicant->status)->toBe('applied')->and(Storage::disk('local')->exists($applicant->cv_path))->toBeTrue();

    $this->actingAs($owner)->post(route('recruitment.applicants.transition', $applicant), ['status' => 'hired'])->assertRedirect()->assertSessionHasErrors('status');
    expect($applicant->fresh()->status)->toBe('applied');
    $this->actingAs($owner)->post(route('recruitment.applicants.transition', $applicant), ['status' => 'screening', 'note' => 'Minimum requirements confirmed.'])->assertRedirect();
    expect($applicant->fresh()->status)->toBe('screening');
});

it('hides recruitment records from a different company context', function () {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
    $otherCompany = Company::query()->create(['name' => 'Other Legal Entity']);
    $vacancy = JobVacancy::query()->create(['company_id' => $otherCompany->id, 'title' => 'Private vacancy', 'description' => 'Not visible in the current company.', 'openings' => 1, 'open_date' => today(), 'status' => 'open', 'created_by' => $owner->id]);

    $this->actingAs($owner)->post(route('recruitment.applicants.store', $vacancy), ['full_name' => 'Cross Tenant', 'email' => 'cross@example.test', 'phone' => '012345678'])->assertNotFound();
});
