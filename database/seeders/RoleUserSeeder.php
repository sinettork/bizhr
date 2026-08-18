<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleUserSeeder extends Seeder
{
    /**
     * Create or reset local development accounts for every supported role.
     *
     * These accounts are deliberately separate from normal staff accounts.
     */
    public function run(): void
    {
        $accounts = [
            ['Super Admin', 'superadmin@bizhr.local', 'Super Administrator'],
            ['Owner', 'owner@bizhr.local', 'Business Owner'],
            ['HR Administrator', 'hr@bizhr.local', 'HR Administrator'],
            ['Manager', 'manager@bizhr.local', 'Department Manager'],
            ['Accountant', 'accountant@bizhr.local', 'Accountant'],
            ['Employee', 'employee@bizhr.local', 'Employee'],
        ];

        foreach ($accounts as [$roleName, $email, $name]) {
            Role::findOrCreate($roleName, 'web');

            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('ChangeMe!2026'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                ],
            );

            $user->syncRoles([$roleName]);
        }
    }
}
