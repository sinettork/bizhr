<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => $this->faker->city(),
            'code' => strtoupper($this->faker->unique()->bothify('BR-###')),
            'is_active' => true,
            'attendance_qr_enabled' => false,
        ];
    }
}
