<?php

use Database\Seeders\DatabaseSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function (): void {
    $this->seed(DatabaseSeeder::class);
});

it('gives owner and super admin every registered permission', function (): void {
    $allPermissions = Permission::query()
        ->where('guard_name', 'web')
        ->pluck('name')
        ->sort()
        ->values()
        ->all();

    foreach (['Owner', 'Super Admin'] as $roleName) {
        $rolePermissions = Role::findByName($roleName, 'web')
            ->permissions
            ->pluck('name')
            ->sort()
            ->values()
            ->all();

        expect($rolePermissions)->toBe($allPermissions);
    }
});

it('keeps employee access limited to self service permissions', function (): void {
    $employee = Role::findByName('Employee', 'web');

    expect($employee->hasPermissionTo('employee.view-own'))->toBeTrue()
        ->and($employee->hasPermissionTo('payroll.view-own'))->toBeTrue()
        ->and($employee->hasPermissionTo('expense.view-own'))->toBeTrue()
        ->and($employee->hasPermissionTo('employee.view'))->toBeFalse()
        ->and($employee->hasPermissionTo('payroll.view'))->toBeFalse()
        ->and($employee->hasPermissionTo('payroll.process'))->toBeFalse()
        ->and($employee->hasPermissionTo('role.manage'))->toBeFalse();
});

it('separates manager hr and accounting payroll duties', function (): void {
    $manager = Role::findByName('Manager', 'web');
    $hr = Role::findByName('HR Administrator', 'web');
    $accountant = Role::findByName('Accountant', 'web');

    expect($manager->hasPermissionTo('attendance.approve'))->toBeTrue()
        ->and($manager->hasPermissionTo('leave.approve'))->toBeTrue()
        ->and($manager->hasPermissionTo('payroll.process'))->toBeFalse()
        ->and($manager->hasPermissionTo('payroll.approve'))->toBeFalse()
        ->and($hr->hasPermissionTo('payroll.approve'))->toBeTrue()
        ->and($hr->hasPermissionTo('payroll.process'))->toBeFalse()
        ->and($accountant->hasPermissionTo('payroll.process'))->toBeTrue()
        ->and($accountant->hasPermissionTo('payroll.approve'))->toBeFalse();
});

it('assigns module responsibilities according to business role', function (): void {
    $hr = Role::findByName('HR Administrator', 'web');
    $manager = Role::findByName('Manager', 'web');
    $accountant = Role::findByName('Accountant', 'web');

    expect($hr->hasPermissionTo('contract.terminate'))->toBeTrue()
        ->and($hr->hasPermissionTo('recruitment.manage'))->toBeTrue()
        ->and($hr->hasPermissionTo('training.manage'))->toBeTrue()
        ->and($hr->hasPermissionTo('asset.manage'))->toBeTrue()
        ->and($hr->hasPermissionTo('expense.pay'))->toBeFalse()
        ->and($manager->hasPermissionTo('expense.approve-manager'))->toBeTrue()
        ->and($manager->hasPermissionTo('expense.pay'))->toBeFalse()
        ->and($accountant->hasPermissionTo('expense.approve-accounting'))->toBeTrue()
        ->and($accountant->hasPermissionTo('expense.pay'))->toBeTrue();
});
