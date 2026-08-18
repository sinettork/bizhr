<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $employee = Employee::factory();
        $leaveType = LeaveType::factory();
        $startDate = $this->faker->dateTimeBetween('+1 week', '+4 weeks');
        $endDate = (clone $startDate)->modify('+'.$this->faker->numberBetween(1, 10).' days');

        return [
            'employee_id' => $employee,
            'leave_type_id' => $leaveType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $this->faker->numberBetween(1, 10),
            'reason' => $this->faker->sentence(),
            'status' => $this->faker->randomElement(['pending', 'manager_reviewed', 'hr_reviewed', 'approved', 'rejected']),
            'manager_reviewed_at' => $this->faker->optional()->dateTime(),
            'hr_reviewed_at' => $this->faker->optional()->dateTime(),
        ];
    }

    public function pending(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'manager_reviewed_at' => null,
            'hr_reviewed_at' => null,
        ]);
    }

    public function approved(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'approved',
            'manager_reviewed_at' => now(),
            'hr_reviewed_at' => now(),
        ]);
    }
}
