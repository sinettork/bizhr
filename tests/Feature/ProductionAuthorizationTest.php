<?php

use App\Models\User;
use Illuminate\Support\Facades\Route;

it('requires authentication for production administration pages', function (string $routeName) {
    $this->get(route($routeName))->assertRedirect(route('login'));
})->with([
    'payroll periods' => 'payroll.periods.index',
    'payroll overtime review' => 'payroll.review',
    'payroll settings' => 'payroll.settings',
    'statutory employee profiles' => 'payroll.statutory-profiles',
    'payroll reports' => 'payroll.reports',
    'audit logs' => 'audit-logs.index',
    'employment contracts' => 'contracts.index',
    'KPI templates' => 'performance.kpi-templates',
    'employee goals' => 'performance.goals',
    'performance reviews' => 'performance.reviews',
    'tasks' => 'tasks.index',
    'recruitment' => 'recruitment.pipeline',
    'training' => 'training.index',
    'assets' => 'assets.index',
    'expenses' => 'expenses.index',
    'announcements' => 'announcements.index',
]);

it('denies production administration pages without the required permission', function (string $routeName) {
    $this->actingAs(User::factory()->create())
        ->get(route($routeName))
        ->assertForbidden();
})->with([
    'payroll periods' => 'payroll.periods.index',
    'payroll overtime review' => 'payroll.review',
    'payroll settings' => 'payroll.settings',
    'statutory employee profiles' => 'payroll.statutory-profiles',
    'payroll reports' => 'payroll.reports',
    'audit logs' => 'audit-logs.index',
    'employment contracts' => 'contracts.index',
    'KPI templates' => 'performance.kpi-templates',
    'employee goals' => 'performance.goals',
    'performance reviews' => 'performance.reviews',
    'tasks' => 'tasks.index',
    'recruitment' => 'recruitment.pipeline',
    'training' => 'training.index',
    'assets' => 'assets.index',
    'expenses' => 'expenses.index',
    'announcements' => 'announcements.index',
]);

it('keeps sensitive routes behind their intended permission middleware', function (
    string $routeName,
    string $permission,
) {
    $middleware = Route::getRoutes()->getByName($routeName)?->gatherMiddleware() ?? [];

    expect($middleware)
        ->toContain('auth')
        ->toContain('verified')
        ->toContain("permission:{$permission}");
})->with([
    ['payroll.periods.index', 'payroll.view'],
    ['payroll.review', 'payroll.approve'],
    ['payroll.settings', 'payroll.view'],
    ['payroll.statutory-profiles', 'payroll.approve'],
    ['payroll.reports', 'payroll.report'],
    ['audit-logs.index', 'audit.view'],
    ['contracts.index', 'contract.view'],
    ['contracts.create', 'contract.create'],
    ['contracts.renew', 'contract.create'],
    ['contracts.approve', 'contract.approve'],
    ['contracts.terminate', 'contract.terminate'],
    ['contracts.download', 'contract.view|contract.view-own'],
    ['contracts.mine', 'contract.view-own'],
    ['performance.kpi-templates', 'performance.manage-goals'],
    ['performance.goals', 'performance.view'],
    ['performance.my-goals', 'performance.view-own'],
    ['performance.reviews', 'performance.view'],
    ['performance.my-reviews', 'performance.view-own'],
    ['tasks.index', 'task.view'],
    ['tasks.mine', 'task.view-own'],
    ['recruitment.pipeline', 'recruitment.view'],
    ['training.index', 'training.view'],
    ['training.mine', 'training.view-own'],
    ['assets.index', 'asset.view'],
    ['assets.mine', 'asset.view-own'],
    ['expenses.index', 'expense.view'],
    ['expenses.mine', 'expense.view-own'],
    ['announcements.index', 'announcement.manage'],
    ['announcements.feed', 'announcement.view'],
    ['employees.documents.index', 'employee.view-sensitive|employee.view-own'],
    ['employees.history.index', 'employee.view-sensitive|employee.view-own'],
]);
