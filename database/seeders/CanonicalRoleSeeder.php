<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CanonicalRoleSeeder extends Seeder
{
    /** @var list<string> */
    private const ROLES = [
        'Super Admin',
        'Owner',
        'HR Administrator',
        'Manager',
        'Accountant',
        'Employee',
    ];

    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::ROLES as $name) {
            Role::findOrCreate($name, 'web');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
