<?php

use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $this->owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
    $this->employee = Employee::query()->firstOrFail();
    Storage::fake('local');
});

it('versions verifies and revokes employee documents without losing history', function () {
    foreach (['first.pdf', 'replacement.pdf'] as $file) {
        $this->actingAs($this->owner)->post(route('employees.documents.store', $this->employee), [
            'document_type' => 'National ID',
            'document_number' => 'ID-001',
            'document' => UploadedFile::fake()->create($file, 100, 'application/pdf'),
        ])->assertRedirect()->assertSessionHasNoErrors();
    }

    $documents = EmployeeDocument::query()->where('employee_id', $this->employee->id)->orderBy('version')->get();
    expect($documents->pluck('version')->all())->toBe([1, 2]);

    $document = $documents->last();
    $this->actingAs($this->owner)->post(route('employees.documents.verify', [$this->employee, $document]))->assertRedirect();
    expect($document->fresh()->status)->toBe('verified')->and($document->fresh()->verified_by)->toBe($this->owner->id);

    $this->actingAs($this->owner)->delete(route('employees.documents.destroy', [$this->employee, $document]))->assertRedirect()->assertSessionHasErrors('document');
    $this->actingAs($this->owner)->post(route('employees.documents.revoke', [$this->employee, $document]), ['reason' => 'Replaced by a newly issued identity document.'])->assertRedirect()->assertSessionHasNoErrors();
    expect($document->fresh()->status)->toBe('revoked')->and(Storage::disk('local')->exists($document->file_path))->toBeTrue();
});
