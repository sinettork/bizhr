<?php

use App\Models\ExpenseClaim;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('runs an expense from employee submission through payment', function () {
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
    Storage::fake('local');
    $employeeUser = User::query()->whereNotNull('email_verified_at')->whereHas('employee')->firstOrFail();
    $employeeUser->givePermissionTo('expense.view-own');
    $owner = User::query()->where('email', 'demo.owner@bizhr.local')->firstOrFail();

    $this->actingAs($employeeUser)->post(route('expenses.store'), [
        'expense_date' => today()->toDateString(),
        'category' => 'Transport',
        'amount' => 12.50,
        'currency' => 'USD',
        'business_purpose' => 'Client meeting transport reimbursement.',
        'receipt' => UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf'),
    ])->assertRedirect()->assertSessionHasNoErrors();

    $claim = ExpenseClaim::query()->latest('id')->firstOrFail();
    expect($claim->status)->toBe('pending_manager');
    $this->actingAs($owner)->post(route('expenses.review', [$claim, 'manager', 'approve']), ['note' => 'Manager verified business purpose.'])->assertRedirect();
    expect($claim->fresh()->status)->toBe('pending_accounting');
    $this->actingAs($owner)->post(route('expenses.review', [$claim, 'accounting', 'approve']), ['note' => 'Receipt and policy validated.'])->assertRedirect();
    $this->actingAs($owner)->post(route('expenses.pay', $claim), ['reference' => 'PAY-EXP-001'])->assertRedirect();
    expect($claim->fresh()->status)->toBe('paid')->and($claim->fresh()->payment_reference)->toBe('PAY-EXP-001');
});
