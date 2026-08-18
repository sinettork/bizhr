<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveType>
 */
class LeaveTypeFactory extends Factory
{
    protected $model = LeaveType::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => $this->faker->word(),
            'code' => strtoupper($this->faker->lexify('LT-???')),
            'days_per_year' => $this->faker->numberBetween(5, 30),
            'is_paid' => $this->faker->boolean(),
            'is_active' => true,
        ];
    }
}
