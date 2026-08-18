<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUsersSeeder extends Seeder
{
    public function run(): void
    {
        $this->user('owner@example.com', 'Owner User', 'Owner');
        $this->user('hr@example.com', 'HR Administrator', 'HR Administrator');
        $this->user('manager@example.com', 'Manager User', 'Manager');
        $this->user('accountant@example.com', 'Accountant User', 'Accountant');
        $this->user('employee@example.com', 'Employee User', 'Employee');
    }

    private function user(string $email, string $name, string $role): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => $email],
            ['name' => $name, 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );

        $user->syncRoles([$role]);
    }
}
