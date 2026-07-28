<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Owner user (already exists from migration)
        $owner = User::firstOrCreate(
            ['email' => 'owner@example.com'],
            [
                'name' => 'Owner User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $owner->assignRole('owner');

        // HR Administrator
        $hrAdmin = User::firstOrCreate(
            ['email' => 'hr@example.com'],
            [
                'name' => 'HR Administrator',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $hrAdmin->assignRole('hr-administrator');

        // Manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $manager->assignRole('manager');

        // Accountant
        $accountant = User::firstOrCreate(
            ['email' => 'accountant@example.com'],
            [
                'name' => 'Accountant User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $accountant->assignRole('accountant');

        // Regular Employee
        $employee = User::firstOrCreate(
            ['email' => 'employee@example.com'],
            [
                'name' => 'Employee User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
        $employee->assignRole('employee');
    }
}
