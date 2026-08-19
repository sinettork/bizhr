<?php

use App\Models\Employee;
use App\Models\Notification;
use App\Models\User;
use App\Services\HrReminderService;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Support\Carbon;

beforeEach(function (): void {
    Carbon::setTestNow('2026-08-19 08:00:00');
    $this->seed([DatabaseSeeder::class, DemoDataSeeder::class]);
});

afterEach(function (): void {
    Carbon::setTestNow();
});

it('sends a probation milestone reminder once even when the scheduler retries', function (): void {
    $user = User::query()->where('email', 'piseth@bizhr.local')->firstOrFail();
    $employee = Employee::query()->where('user_id', $user->id)->firstOrFail();
    $employee->update([
        'employment_status' => 'On probation',
        'is_active' => true,
        'probation_end_date' => today()->addDays(7),
    ]);

    $service = app(HrReminderService::class);
    $service->run();
    $service->run();

    expect(Notification::query()
        ->where('user_id', $user->id)
        ->where('company_id', $employee->company_id)
        ->where('type', 'probation_expiry')
        ->whereDate('created_at', today())
        ->count())->toBe(1);
});
