<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class EmploymentContractPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'contract.view-own',
            'contract.view',
            'contract.create',
            'contract.edit',
            'contract.approve',
            'contract.terminate',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        Role::findOrCreate('Employee', 'web')->givePermissionTo('contract.view-own');
        Role::findOrCreate('Manager', 'web')->givePermissionTo('contract.view-own');
        Role::findOrCreate('Accountant', 'web')->givePermissionTo('contract.view-own');

        foreach (['HR Administrator', 'Owner', 'Super Admin'] as $role) {
            Role::findOrCreate($role, 'web')->givePermissionTo($permissions);
        }
    }
}
