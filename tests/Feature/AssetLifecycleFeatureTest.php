<?php

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;

it('supports assignment and return while preventing archive of an issued asset', function () {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    $owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();
    $employee = User::query()->whereHas('employee')->firstOrFail()->employee;
    $asset = Asset::query()->create(['company_id' => $employee->company_id, 'asset_code' => 'LAP-PROD-001', 'name' => 'Production laptop', 'category' => 'Computer', 'currency' => 'USD', 'condition' => 'good', 'status' => 'available']);

    $this->actingAs($owner)->post(route('assets.assign', $asset), ['employee_id' => $employee->id, 'condition_out' => 'good', 'expected_return_date' => today()->addYear()->toDateString()])->assertRedirect()->assertSessionHasNoErrors();
    $assignment = AssetAssignment::query()->where('asset_id', $asset->id)->firstOrFail();
    expect($asset->fresh()->status)->toBe('assigned');

    $this->actingAs($owner)->delete(route('assets.destroy', $asset))->assertRedirect()->assertSessionHasErrors('asset');
    expect($asset->fresh()->deleted_at)->toBeNull();

    $this->actingAs($owner)->post(route('assets.receive', $assignment), ['condition_in' => 'fair', 'notes' => 'Returned with documented cosmetic wear.'])->assertRedirect()->assertSessionHasNoErrors();
    expect($assignment->fresh()->status)->toBe('returned')->and($asset->fresh()->status)->toBe('available');

    $this->actingAs($owner)->delete(route('assets.destroy', $asset))->assertRedirect()->assertSessionHasNoErrors();
    expect($asset->fresh()->trashed())->toBeTrue()->and($assignment->fresh())->not->toBeNull();
});
