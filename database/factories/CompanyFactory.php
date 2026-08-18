<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'legal_name' => $this->faker->company(),
            'local_name' => $this->faker->company(),
            'logo_path' => null,
            'locale' => 'en',
            'timezone' => 'UTC',
            'currency' => 'USD',
        ];
    }
}
