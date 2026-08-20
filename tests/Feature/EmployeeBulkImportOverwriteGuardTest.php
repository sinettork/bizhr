<?php

use App\Models\Employee;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Support\Str;

beforeEach(function (): void {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $this->owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
    $this->employee = Employee::query()->where('email', 'piseth@bizhr.local')->firstOrFail();
});

it('prevents generic employee import from overwriting an existing employee', function (): void {
    $token = Str::random(48);
    $salaryBefore = $this->employee->base_salary;

    $payload = [
        'type' => 'employees',
        'user_id' => $this->owner->id,
        'company_id' => $this->employee->company_id,
        'rows' => [[
            'line' => 2,
            'raw' => [],
            'data' => [
                'employee_code' => $this->employee->employee_code,
                'base_salary' => 999999,
            ],
            'errors' => [],
        ]],
        'created_at' => now()->timestamp,
        'expires_at' => now()->addMinutes(30)->timestamp,
    ];

    $this->actingAs($this->owner)
        ->withSession(["bulk_imports.{$token}" => $payload])
        ->post(route('imports.confirm', ['type' => 'employees']), ['token' => $token])
        ->assertRedirect(route('imports.index'))
        ->assertSessionHasErrors('file');

    expect($this->employee->fresh()->base_salary)->toEqual($salaryBefore)
        ->and(session()->has("bulk_imports.{$token}"))->toBeFalse();
});
