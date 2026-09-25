<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Seeder;

class MassEmployeesSeeder extends Seeder
{
    public function run(): void
    {
        // Use the first existing company, or create a demo company if missing.
        $company = Company::first() ?? Company::factory()->create(['name' => 'BizHR Demo Seeded']);

        // Ensure there are a few branches for assignment.
        $branches = Branch::query()->where('company_id', $company->id)->get();
        if ($branches->isEmpty()) {
            $branches = Branch::factory()->count(3)->for($company)->create();
        }

        // Ensure there are departments under the branches.
        $departments = Department::query()->where('company_id', $company->id)->get();
        if ($departments->isEmpty()) {
            $departments = collect();
            foreach ($branches as $branch) {
                $departments = $departments->concat(Department::factory()->count(2)->create([
                    'company_id' => $company->id,
                    'branch_id' => $branch->id,
                ]));
            }
        }

        // Create 50 employees distributed across branches/departments.
        $count = 50;

        Employee::factory()->count($count)->make()->each(function ($employee) use ($company, $branches, $departments) {
            // pick random branch and department within the company
            $branch = $branches->random();
            // Try to pick a department in the same branch, fallback to any department
            $dept = $departments->firstWhere('branch_id', $branch->id) ?? $departments->random();

            $employee->company_id = $company->id;
            $employee->branch_id = $branch->id;
            $employee->department_id = $dept->id;
            // Ensure unique email/work_email
            $employee->email = $employee->email ?? uniqid('emp').'@example.test';
            $employee->work_email = $employee->work_email ?? 'work+'.uniqid().'@example.test';
            $employee->save();
        });

        $this->command->info("Seeded {$count} employees for company {$company->id}.");
    }
}

