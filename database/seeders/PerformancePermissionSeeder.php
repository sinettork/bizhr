<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PerformancePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'performance.view-own', 'performance.view', 'performance.create',
            'performance.edit', 'performance.review', 'performance.approve',
            'performance.reopen', 'performance.manage-goals',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        Role::findOrCreate('Employee', 'web')->givePermissionTo('performance.view-own');
        Role::findOrCreate('Accountant', 'web')->givePermissionTo('performance.view-own');
        Role::findOrCreate('Manager', 'web')->givePermissionTo([
            'performance.view-own', 'performance.view', 'performance.create',
            'performance.edit', 'performance.review', 'performance.manage-goals',
        ]);

        foreach (['HR Administrator', 'Owner', 'Super Admin'] as $role) {
            Role::findOrCreate($role, 'web')->givePermissionTo($permissions);
        }
    }
}
