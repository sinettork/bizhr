<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;

it('renders every parameterless application page without a server error', function () {
    $this->seed([
        DatabaseSeeder::class,
        DemoDataSeeder::class,
    ]);

    $user = User::query()
        ->where('email', 'demo.owner@bizhr.local')
        ->firstOrFail();

    $routes = [
        'dashboard',
        'company.settings',
        'branches.index',
        'branches.create',
        'departments.index',
        'departments.create',
        'positions.index',
        'positions.create',
        'employment-types.index',
        'work-shifts.index',
        'schedules.index',
        'attendance.checkinout',
        'attendance.corrections.request',
        'attendance.corrections.review',
        'attendance.reports.index',
        'attendance.scan',
        'attendance.qr.display',
        'leave.types.index',
        'leave.balances.index',
        'leave.requests.index',
        'leave.requests.review',
        'employees.index',
        'employees.create',
        'contracts.index',
        'contracts.create',
        'contracts.mine',
        'roles.index',
        'users.index',
        'audit-logs.index',
        'payroll.periods.index',
        'payroll.settings',
        'payroll.review',
        'payroll.statutory-profiles',
        'payroll.reports',
        'payroll.my-payslips',
        'performance.kpi-templates',
        'performance.goals',
        'performance.my-goals',
        'performance.reviews',
        'performance.my-reviews',
        'tasks.index',
        'tasks.mine',
        'recruitment.pipeline',
        'training.index',
        'training.mine',
        'assets.index',
        'assets.mine',
        'expenses.index',
        'expenses.mine',
        'announcements.index',
        'announcements.feed',
        'exports.index',
        'profile.edit',
        'appearance.edit',
    ];

    foreach ($routes as $routeName) {
        $response = $this->actingAs($user)->get(route($routeName));

        $this->assertNotSame(
            404,
            $response->getStatusCode(),
            "{$routeName} was not found.",
        );
        $this->assertLessThan(
            500,
            $response->getStatusCode(),
            "{$routeName} returned a server error: "
                .($response->exception?->getMessage() ?? 'unknown error'),
        );
    }
});
