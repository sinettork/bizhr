<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendance>
 */
class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        $employee = Employee::factory();
        $workDate = $this->faker->dateTimeBetween('-30 days', 'now');
        $shiftStart = (new \DateTime($workDate->format('Y-m-d 08:00:00')));
        $shiftEnd = (new \DateTime($workDate->format('Y-m-d 17:00:00')));
        $checkIn = (clone $shiftStart)->modify('+'.$this->faker->numberBetween(0, 15).' minutes');
        $checkOut = (clone $shiftEnd)->modify('+'.$this->faker->numberBetween(-15, 120).' minutes');

        return [
            'employee_id' => $employee,
            'branch_id' => fn (array $attributes): int => Employee::query()->where('id', $attributes['employee_id'])->firstOrFail()->branch_id,
            'work_date' => $workDate,
            'check_in_at' => $checkIn,
            'check_out_at' => $checkOut,
            'check_in_method' => $this->faker->randomElement(['qr', 'manual', 'biometric']),
            'check_out_method' => $this->faker->randomElement(['qr', 'manual', 'biometric']),
            'status' => 'present',
            'notes' => $this->faker->optional()->sentence(),
        ];
    }

    public function absent(): self
    {
        return $this->state(fn (array $attributes) => [
            'check_in_at' => null,
            'check_out_at' => null,
            'status' => 'absent',
        ]);
    }

    public function late(): self
    {
        return $this->state(function (array $attributes) {
            $workDate = $attributes['work_date'];
            $shiftStart = (new \DateTime($workDate->format('Y-m-d 08:00:00')));
            $lateStart = (clone $shiftStart)->modify('+30 minutes');
            $shiftEnd = (new \DateTime($workDate->format('Y-m-d 17:00:00')));

            return [
                'check_in_at' => $lateStart,
                'check_out_at' => $shiftEnd,
                'status' => 'late',
            ];
        });
    }
}
