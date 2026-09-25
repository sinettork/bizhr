<?php

use App\Models\Asset;
use App\Models\Employee;
use App\Models\User;
use App\Services\AssetWorkflowService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;

beforeEach(function (): void {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $this->actor = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
    $this->employee = Employee::query()->where('email', 'piseth@bizhr.local')->firstOrFail();
});

it('does not assign company assets to inactive or separated employees', function (): void {
    $asset = Asset::query()->create([
        'company_id' => $this->employee->company_id,
        'asset_code' => 'OFFBOARD-GUARD-001',
        'name' => 'Offboarding guard laptop',
        'category' => 'Laptop',
        'currency' => 'USD',
        'condition' => 'good',
        'status' => 'available',
    ]);
    $this->employee->update([
        'employment_status' => 'Resigned',
        'is_active' => false,
    ]);

    expect(fn () => app(AssetWorkflowService::class)->assign(
        $asset,
        $this->employee->fresh(),
        $this->actor,
        'good',
        null,
    ))->toThrow(DomainException::class, 'Assets can be assigned only to an active employee.');

    expect($asset->fresh()->status)->toBe('available')
        ->and($asset->assignments()->count())->toBe(0);
});
