<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create permissions and roles
        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            TestUsersSeeder::class,
        ]);
    }
}
