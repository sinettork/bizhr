<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PayrollPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ([
            'payroll.view-own',
            'payroll.view',
            'payroll.edit',
            'payroll.approve',
            'payroll.process',
            'payroll.report',
        ] as $name) {
            Permission::findOrCreate($name, 'web');
        }

        $this->configure('Employee', ['payroll.view-own']);
        $this->configure('Manager', ['payroll.view-own']);
        $this->configure('HR Administrator', [
            'payroll.view-own',
            'payroll.view',
            'payroll.approve',
            'payroll.report',
        ]);
        $this->configure('Accountant', [
            'payroll.view-own',
            'payroll.view',
            'payroll.edit',
            'payroll.process',
            'payroll.report',
        ]);
        $this->configure('Owner', [
            'payroll.view-own',
            'payroll.view',
            'payroll.edit',
            'payroll.approve',
            'payroll.process',
            'payroll.report',
        ]);
        $this->configure('Super Admin', [
            'payroll.view-own',
            'payroll.view',
            'payroll.edit',
            'payroll.approve',
            'payroll.process',
            'payroll.report',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function configure(string $roleName, array $permissions): void
    {
        $role = Role::findByName($roleName, 'web');
        $payrollPermissions = Permission::query()
            ->where('guard_name', 'web')
            ->where('name', 'like', 'payroll.%')
            ->get();

        $role->revokePermissionTo($payrollPermissions);
        $role->givePermissionTo($permissions);
    }
}
