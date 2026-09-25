<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CreateEmployeeUsersSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::query()->whereNull('user_id')->get();

        if ($employees->isEmpty()) {
            $this->command->info('No employees without user accounts were found.');
            return;
        }

        $outputPath = storage_path('app/seeded_employee_credentials.csv');
        $fh = fopen($outputPath, 'w');
        fputcsv($fh, ['employee_id', 'employee_name', 'employee_email', 'user_email', 'password']);

        $counter = 1;

        foreach ($employees as $employee) {
            // find a unique user email with pattern employeeNN@bizhr.local
            do {
                $emailLocal = sprintf('employee%02d', $counter);
                $userEmail = $emailLocal . '@bizhr.local';
                $counter++;
            } while (User::query()->where('email', $userEmail)->exists());

            $password = 'Password123!';

            $user = User::create([
                'name' => $employee->getFullName(),
                'email' => $userEmail,
                'is_active' => true,
                'email_verified_at' => Carbon::now(),
                'password' => $password,
            ]);

            // Link user to employee
            $employee->user_id = $user->id;
            $employee->save();

            fputcsv($fh, [
                $employee->id,
                $employee->getFullName(),
                $employee->email,
                $userEmail,
                $password,
            ]);

            $this->command->info("Created user {$userEmail} for employee {$employee->id}");
        }

        fclose($fh);

        $this->command->info('Wrote credentials to: ' . $outputPath);
    }
}
