<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    public function definition(): array
    {
        $company = Company::factory();
        $branch = Branch::factory(['company_id' => $company]);
        $department = Department::factory(['company_id' => $company, 'branch_id' => $branch]);

        return [
            'company_id' => $company,
            'branch_id' => $branch,
            'department_id' => $department,
            'employee_code' => $this->faker->unique()->bothify('EMP-###??'),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'full_name_en' => $this->faker->name(),
            'full_name_km' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'date_of_birth' => $this->faker->dateTimeBetween('-65 years', '-18 years'),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'employment_status' => 'Active',
            'hire_date' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'base_salary' => $this->faker->randomFloat(2, 200, 5000),
            'salary_currency' => 'USD',
        ];
    }
}
