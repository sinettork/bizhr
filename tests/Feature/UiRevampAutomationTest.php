<?php

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;

it('verifies that all domain pages render with core BizHR UI design DNA', function () {
    $this->seed([
        DatabaseSeeder::class,
        DemoDataSeeder::class,
    ]);

    $owner = User::query()
        ->where('email', 'demo.owner@bizhr.local')
        ->firstOrFail();

    $employeeUser = User::query()
        ->where('email', 'piseth@bizhr.local')
        ->firstOrFail();

    $adminPages = [
        'announcements.feed' => ['workspace-command-bar', 'card'],
        'recruitment.pipeline' => ['workspace-command-bar', 'reference-list'],
        'tasks.index' => ['workspace-command-bar', 'reference-list'],
        'training.index' => ['workspace-command-bar', 'reference-list'],
        'assets.index' => ['workspace-command-bar', 'reference-list'],
        'expenses.index' => ['workspace-command-bar', 'reference-list'],
        'leave.requests.review' => ['workspace-command-bar', 'reference-list'],
        'leave.balances.index' => ['workspace-command-bar', 'reference-list'],
        'attendance.checkinout' => ['workspace-command-bar'],
        'payroll.periods.index' => ['workspace-command-bar', 'reference-list'],
        'payroll.review' => ['workspace-command-bar', 'reference-list'],
        'performance.goals' => ['workspace-command-bar', 'reference-list'],
        'performance.reviews' => ['workspace-command-bar', 'reference-list'],
    ];

    $employeePages = [
        'leave.requests.index' => ['workspace-command-bar', 'reference-list'],
        'tasks.mine' => ['workspace-command-bar', 'reference-list'],
        'training.mine' => ['workspace-command-bar', 'reference-list'],
        'assets.mine' => ['workspace-command-bar', 'reference-list'],
        'expenses.mine' => ['workspace-command-bar', 'reference-list'],
        'performance.my-goals' => ['workspace-command-bar', 'reference-list'],
        'performance.my-reviews' => ['workspace-command-bar', 'reference-list'],
    ];

    foreach ($adminPages as $routeName => $expectedElements) {
        $response = $this->actingAs($owner)->get(route($routeName));

        $this->assertSame(
            200,
            $response->getStatusCode(),
            "Admin page '{$routeName}' failed with status {$response->getStatusCode()}.",
        );

        foreach ($expectedElements as $expectedElement) {
            $this->assertTrue(
                str_contains($response->getContent(), $expectedElement),
                "Admin page '{$routeName}' does not contain expected UI element: '{$expectedElement}'",
            );
        }
    }

    foreach ($employeePages as $routeName => $expectedElements) {
        $response = $this->actingAs($employeeUser)->get(route($routeName));

        $this->assertSame(
            200,
            $response->getStatusCode(),
            "Employee page '{$routeName}' failed with status {$response->getStatusCode()}.",
        );

        foreach ($expectedElements as $expectedElement) {
            $this->assertTrue(
                str_contains($response->getContent(), $expectedElement),
                "Employee page '{$routeName}' does not contain expected UI element: '{$expectedElement}'",
            );
        }
    }
});
