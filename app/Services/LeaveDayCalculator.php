<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\PublicHoliday;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class LeaveDayCalculator
{
    public function workingDates(Employee $employee, CarbonImmutable $start, CarbonImmutable $end): Collection
    {
        $schedules = EmployeeSchedule::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('work_date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn (EmployeeSchedule $schedule): string => $schedule->work_date->toDateString());

        $holidays = PublicHoliday::query()
            ->where('company_id', $employee->company_id)
            ->whereBetween('holiday_date', [$start->toDateString(), $end->toDateString()])
            ->pluck('holiday_date')
            ->map(fn ($date): string => CarbonImmutable::parse($date)->toDateString())
            ->flip();

        $dates = collect();

        for ($date = $start; $date->lte($end); $date = $date->addDay()) {
            $key = $date->toDateString();

            if ($holidays->has($key)) {
                continue;
            }

            $schedule = $schedules->get($key);

            if ($schedule) {
                if (! $schedule->is_rest_day && $schedule->work_shift_id) {
                    $dates->push($date);
                }

                continue;
            }

            if (! $date->isWeekend()) {
                $dates->push($date);
            }
        }

        return $dates;
    }

    public function daysByYear(Employee $employee, CarbonImmutable $start, CarbonImmutable $end): array
    {
        return $this->workingDates($employee, $start, $end)
            ->groupBy(fn (CarbonImmutable $date): int => $date->year)
            ->map(fn (Collection $dates): int => $dates->count())
            ->all();
    }
}
