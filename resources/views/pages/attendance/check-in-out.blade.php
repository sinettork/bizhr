<?php

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\WorkShift;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('កត់ត្រាវត្តមាន')] class extends Component
{
    public ?int $employeeId = null;

    public string $location = '';

    public string $notes = '';

    public function mount(): void
    {
        $this->employeeId = auth()
            ->user()
            ?->employee()
            ->value('id');
    }

    protected function rules(): array
    {
        return [
            'location' => [
                'nullable',
                'string',
                'max:255',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'location.max' =>
                'ទីតាំងមិនអាចលើសពី ២៥៥ តួអក្សរ។',

            'notes.max' =>
                'កំណត់សម្គាល់មិនអាចលើសពី ១,០០០ តួអក្សរ។',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Employee and schedule
    |--------------------------------------------------------------------------
    */

    #[Computed]
    public function employee(): ?Employee
    {
        if (! $this->employeeId) {
            return null;
        }

        return Employee::query()
            ->with([
                'branch',
                'department',
                'position',
                'employmentType',
            ])
            ->find($this->employeeId);
    }

    #[Computed]
    public function todaySchedule(): ?EmployeeSchedule
    {
        if (! $this->employeeId) {
            return null;
        }

        return EmployeeSchedule::query()
            ->with([
                'branch',
                'workShift',
            ])
            ->where(
                'employee_id',
                $this->employeeId
            )
            ->whereDate(
                'work_date',
                today()
            )
            ->first();
    }

    /*
    |--------------------------------------------------------------------------
    | Attendance records
    |--------------------------------------------------------------------------
    */

    #[Computed]
    public function currentAttendance(): ?Attendance
    {
        if (! $this->employeeId) {
            return null;
        }

        /*
         * Find an open attendance first.
         * This supports night shifts checked out after midnight.
         */
        $openAttendance = Attendance::query()
            ->with([
                'branch',
            ])
            ->where(
                'employee_id',
                $this->employeeId
            )
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->whereDate(
                'work_date',
                '>=',
                today()->subDays(2)
            )
            ->latest('check_in_at')
            ->first();

        if ($openAttendance) {
            return $openAttendance;
        }

        return Attendance::query()
            ->with([
                'branch',
            ])
            ->where(
                'employee_id',
                $this->employeeId
            )
            ->whereDate(
                'work_date',
                today()
            )
            ->first();
    }

    #[Computed]
    public function recentAttendances()
    {
        if (! $this->employeeId) {
            return collect();
        }

        return Attendance::query()
            ->with('branch')
            ->where(
                'employee_id',
                $this->employeeId
            )
            ->orderByDesc('work_date')
            ->limit(10)
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Check in
    |--------------------------------------------------------------------------
    */

    public function checkIn(): void
    {
        abort_unless(
            auth()->user()?->can(
                'attendance.checkin'
            ),
            403
        );

        $validated = $this->validate();

        $employee = $this->requireEmployee();

        $openAttendance = Attendance::query()
            ->where(
                'employee_id',
                $employee->id
            )
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->first();

        if ($openAttendance) {
            session()->flash(
                'error',
                'អ្នកបានកត់ត្រាម៉ោងចូលរួចហើយ។ សូមកត់ត្រាម៉ោងចេញជាមុនសិន។'
            );

            return;
        }

        $schedule = EmployeeSchedule::query()
            ->with('workShift')
            ->where(
                'employee_id',
                $employee->id
            )
            ->whereDate(
                'work_date',
                today()
            )
            ->first();

        if ($schedule?->is_rest_day) {
            session()->flash(
                'error',
                'ថ្ងៃនេះត្រូវបានកំណត់ជាថ្ងៃសម្រាក។ មិនអាចកត់ត្រាម៉ោងចូលបានទេ។'
            );

            return;
        }

        $now = now();

        $shift = $schedule?->workShift;

        $lateMinutes = $this->calculateLateMinutes(
            $now,
            today(),
            $shift
        );

        DB::transaction(
            function () use (
                $employee,
                $schedule,
                $shift,
                $now,
                $lateMinutes,
                $validated
            ): void {
                $attendance = Attendance::query()
                    ->where(
                        'employee_id',
                        $employee->id
                    )
                    ->whereDate(
                        'work_date',
                        today()
                    )
                    ->lockForUpdate()
                    ->first();

                if ($attendance?->check_in_at) {
                    session()->flash(
                        'error',
                        'អ្នកបានកត់ត្រាម៉ោងចូលសម្រាប់ថ្ងៃនេះរួចហើយ។'
                    );

                    return;
                }

                $data = [
                    'employee_id' =>
                        $employee->id,

                    'branch_id' =>
                        $schedule?->branch_id
                        ?: $employee->branch_id,

                    'work_date' =>
                        today()->toDateString(),

                    'scheduled_start' =>
                        $shift?->start_time,

                    'scheduled_end' =>
                        $shift?->end_time,

                    'check_in_at' =>
                        $now,

                    'check_in_method' =>
                        'web',

                    'check_in_location' =>
                        filled($validated['location'])
                            ? trim(
                                $validated['location']
                            )
                            : null,

                    'late_minutes' =>
                        $lateMinutes,

                    'early_leave_minutes' =>
                        0,

                    'worked_minutes' =>
                        0,

                    'overtime_minutes' =>
                        0,

                    'status' =>
                        $lateMinutes > 0
                            ? 'late'
                            : 'present',

                    'notes' =>
                        filled($validated['notes'])
                            ? trim(
                                $validated['notes']
                            )
                            : null,
                ];

                if ($attendance) {
                    $attendance->update($data);
                } else {
                    Attendance::query()
                        ->create($data);
                }

                session()->flash(
                    'status',
                    $lateMinutes > 0
                        ? "បានកត់ត្រាម៉ោងចូល។ អ្នកមកយឺត {$lateMinutes} នាទី។"
                        : 'បានកត់ត្រាម៉ោងចូលដោយជោគជ័យ។'
                );
            }
        );

        $this->clearComponentCache();

        $this->reset([
            'location',
            'notes',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Check out
    |--------------------------------------------------------------------------
    */

    public function checkOut(): void
    {
        abort_unless(
            auth()->user()?->can(
                'attendance.checkout'
            ),
            403
        );

        $validated = $this->validate();

        $employee = $this->requireEmployee();

        $attendance = Attendance::query()
            ->where(
                'employee_id',
                $employee->id
            )
            ->whereNotNull('check_in_at')
            ->whereNull('check_out_at')
            ->latest('check_in_at')
            ->first();

        if (! $attendance) {
            session()->flash(
                'error',
                'រកមិនឃើញកំណត់ត្រាម៉ោងចូលដែលកំពុងបើកទេ។'
            );

            return;
        }

        $schedule = EmployeeSchedule::query()
            ->with('workShift')
            ->where(
                'employee_id',
                $employee->id
            )
            ->whereDate(
                'work_date',
                $attendance->work_date
            )
            ->first();

        $shift = $schedule?->workShift;

        $now = now();

        $workedMinutes =
            $this->calculateWorkedMinutes(
                $attendance,
                $now,
                $shift
            );

        $earlyLeaveMinutes =
            $this->calculateEarlyLeaveMinutes(
                $attendance,
                $now,
                $shift
            );

        $scheduledWorkMinutes =
            $this->calculateScheduledWorkMinutes(
                $attendance,
                $shift
            );

        $overtimeMinutes = max(
            0,
            $workedMinutes - $scheduledWorkMinutes
        );

        DB::transaction(
            function () use (
                $attendance,
                $now,
                $workedMinutes,
                $earlyLeaveMinutes,
                $overtimeMinutes,
                $validated
            ): void {
                $lockedAttendance =
                    Attendance::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $attendance->id
                        );

                if ($lockedAttendance->check_out_at) {
                    session()->flash(
                        'error',
                        'កំណត់ត្រានេះមានម៉ោងចេញរួចហើយ។'
                    );

                    return;
                }

                $lockedAttendance->update([
                    'check_out_at' =>
                        $now,

                    'check_out_method' =>
                        'web',

                    'check_out_location' =>
                        filled(
                            $validated['location']
                        )
                            ? trim(
                                $validated['location']
                            )
                            : null,

                    'early_leave_minutes' =>
                        $earlyLeaveMinutes,

                    'worked_minutes' =>
                        $workedMinutes,

                    'overtime_minutes' =>
                        $overtimeMinutes,

                    'status' =>
                        $lockedAttendance
                            ->late_minutes > 0
                            ? 'late'
                            : 'present',

                    'notes' =>
                        $this->mergeNotes(
                            $lockedAttendance->notes,
                            $validated['notes']
                        ),
                ]);

                session()->flash(
                    'status',
                    'បានកត់ត្រាម៉ោងចេញដោយជោគជ័យ។'
                );
            }
        );

        $this->clearComponentCache();

        $this->reset([
            'location',
            'notes',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Calculation helpers
    |--------------------------------------------------------------------------
    */

    private function calculateLateMinutes(
        Carbon $checkIn,
        Carbon $workDate,
        ?WorkShift $shift
    ): int {
        if (! $shift) {
            return 0;
        }

        $scheduledStart =
            $this->dateWithTime(
                $workDate,
                $shift->start_time
            );

        $graceMinutes =
            (int) $shift->late_grace_minutes;

        $allowedTime = $scheduledStart
            ->copy()
            ->addMinutes($graceMinutes);

        if (
            ! $checkIn->greaterThan(
                $allowedTime
            )
        ) {
            return 0;
        }

        return max(
            0,
            (int) $scheduledStart
                ->diffInMinutes($checkIn)
                - $graceMinutes
        );
    }

    private function calculateEarlyLeaveMinutes(
        Attendance $attendance,
        Carbon $checkOut,
        ?WorkShift $shift
    ): int {
        $scheduledEnd =
            $shift?->end_time
            ?: $attendance->scheduled_end;

        if (! $scheduledEnd) {
            return 0;
        }

        $scheduledStart =
            $shift?->start_time
            ?: $attendance->scheduled_start;

        $workDate =
            $attendance->work_date->copy();

        $endAt = $this->dateWithTime(
            $workDate,
            $scheduledEnd
        );

        if ($scheduledStart) {
            $startAt = $this->dateWithTime(
                $workDate,
                $scheduledStart
            );

            if (
                $endAt->lessThanOrEqualTo(
                    $startAt
                )
            ) {
                $endAt->addDay();
            }
        }

        $graceMinutes = $shift
            ? (int) $shift
                ->early_leave_grace_minutes
            : 0;

        $allowedEarlyTime = $endAt
            ->copy()
            ->subMinutes($graceMinutes);

        if (
            ! $checkOut->lessThan(
                $allowedEarlyTime
            )
        ) {
            return 0;
        }

        return max(
            0,
            (int) $checkOut
                ->diffInMinutes($endAt)
                - $graceMinutes
        );
    }

    private function calculateWorkedMinutes(
        Attendance $attendance,
        Carbon $checkOut,
        ?WorkShift $shift
    ): int {
        if (! $attendance->check_in_at) {
            return 0;
        }

        $grossMinutes = (int) $attendance
            ->check_in_at
            ->diffInMinutes($checkOut);

        $breakMinutes = $shift
            ? (int) $shift->break_minutes
            : 0;

        return max(
            0,
            $grossMinutes - $breakMinutes
        );
    }

    private function calculateScheduledWorkMinutes(
        Attendance $attendance,
        ?WorkShift $shift
    ): int {
        $startTime =
            $shift?->start_time
            ?: $attendance->scheduled_start;

        $endTime =
            $shift?->end_time
            ?: $attendance->scheduled_end;

        if (
            ! $startTime
            || ! $endTime
        ) {
            return 0;
        }

        $workDate =
            $attendance->work_date->copy();

        $startAt = $this->dateWithTime(
            $workDate,
            $startTime
        );

        $endAt = $this->dateWithTime(
            $workDate,
            $endTime
        );

        if (
            $endAt->lessThanOrEqualTo(
                $startAt
            )
        ) {
            $endAt->addDay();
        }

        $minutes = (int) $startAt
            ->diffInMinutes($endAt);

        $breakMinutes = $shift
            ? (int) $shift->break_minutes
            : 0;

        return max(
            0,
            $minutes - $breakMinutes
        );
    }

    private function dateWithTime(
        Carbon $date,
        string $time
    ): Carbon {
        return $date
            ->copy()
            ->setTimeFromTimeString(
                substr($time, 0, 8)
            );
    }

    private function mergeNotes(
        ?string $existingNotes,
        ?string $newNotes
    ): ?string {
        $existingNotes = filled($existingNotes)
            ? trim($existingNotes)
            : null;

        $newNotes = filled($newNotes)
            ? trim($newNotes)
            : null;

        if (
            $existingNotes
            && $newNotes
        ) {
            return $existingNotes
                . PHP_EOL
                . $newNotes;
        }

        return $newNotes ?: $existingNotes;
    }

    /*
    |--------------------------------------------------------------------------
    | Other helpers
    |--------------------------------------------------------------------------
    */

    private function requireEmployee(): Employee
    {
        abort_unless(
            $this->employeeId,
            422,
            'គណនីនេះមិនទាន់បានភ្ជាប់ជាមួយបុគ្គលិក។'
        );

        return Employee::query()
            ->findOrFail(
                $this->employeeId
            );
    }

    private function clearComponentCache(): void
    {
        unset(
            $this->employee,
            $this->todaySchedule,
            $this->currentAttendance,
            $this->recentAttendances
        );
    }
};
?>

@php
    $employee = $this->employee;

    $schedule = $this->todaySchedule;

    $attendance = $this->currentAttendance;

    $durationLabel = function (
        int|float|null $minutes
    ): string {
        $minutes = max(
            0,
            (int) $minutes
        );

        $hours = intdiv(
            $minutes,
            60
        );

        $remainingMinutes =
            $minutes % 60;

        if (
            $hours > 0
            && $remainingMinutes > 0
        ) {
            return "{$hours} ម៉ោង {$remainingMinutes} នាទី";
        }

        if ($hours > 0) {
            return "{$hours} ម៉ោង";
        }

        return "{$remainingMinutes} នាទី";
    };

    $statusLabel = match (
        $attendance?->status
    ) {
        'present' => 'មានវត្តមាន',
        'late' => 'មកយឺត',
        'absent' => 'អវត្តមាន',
        'half_day' => 'ពាក់កណ្ដាលថ្ងៃ',
        'on_leave' => 'ឈប់សម្រាក',
        'remote_work' => 'ធ្វើការពីចម្ងាយ',
        'business_trip' => 'បេសកកម្ម',
        default => 'មិនទាន់កត់ត្រា',
    };

    $statusColor = match (
        $attendance?->status
    ) {
        'present' => 'green',
        'late' => 'amber',
        'absent' => 'red',
        'half_day' => 'blue',
        'on_leave' => 'purple',
        'remote_work' => 'cyan',
        'business_trip' => 'indigo',
        default => 'zinc',
    };
@endphp

<div
    wire:poll.30s
    class="w-full space-y-6 p-4 sm:p-6"
>
    {{-- Header --}}
    <div
        class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
    >
        <div>
            <h1
                class="text-2xl font-semibold text-zinc-900 dark:text-white"
            >
                កត់ត្រាវត្តមាន
            </h1>

            <p
                class="mt-1 text-zinc-600 dark:text-zinc-300"
            >
                កត់ត្រាម៉ោងចូល ម៉ោងចេញ
                និងពិនិត្យព័ត៌មានវត្តមានប្រចាំថ្ងៃ។
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <flux:button
                type="button"
                variant="ghost"
                :href="route(
                    'attendance.corrections.request'
                )"
                wire:navigate
            >
                ស្នើសុំកែតម្រូវ
            </flux:button>

            @can('attendance.report')
                <flux:button
                    type="button"
                    variant="ghost"
                    :href="route(
                        'attendance.reports.index'
                    )"
                >
                    របាយការណ៍វត្តមាន
                </flux:button>
            @endcan
        </div>
    </div>

    {{-- Messages --}}
    @if (session('status'))
        <div
            class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800 dark:border-green-800 dark:bg-green-950/30 dark:text-green-200"
        >
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div
            class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 dark:border-red-800 dark:bg-red-950/30 dark:text-red-200"
        >
            {{ session('error') }}
        </div>
    @endif

    @if (! $employee)
        <div
            class="rounded-2xl border border-amber-200 bg-amber-50 p-6 text-amber-900 dark:border-amber-800 dark:bg-amber-950/30 dark:text-amber-100"
        >
            <h2 class="text-lg font-semibold">
                គណនីមិនទាន់បានភ្ជាប់ជាមួយបុគ្គលិក
            </h2>

            <p class="mt-2 text-sm">
                គណនីដែលកំពុងចូលប្រើត្រូវមាន
                Employee ដែលមាន user_id ត្រូវគ្នា
                មុនពេលអាចកត់ត្រាវត្តមាន។
            </p>
        </div>
    @else
        {{-- Employee and clock --}}
        <div
            class="grid gap-6 xl:grid-cols-[1fr_360px]"
        >
            <div
                class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6"
            >
                @php
                    $employeeName =
                        $employee->full_name_km
                        ?: $employee->full_name_en
                        ?: trim(
                            $employee->first_name
                            . ' '
                            . $employee->last_name
                        );

                    $initial = mb_substr(
                        $employeeName,
                        0,
                        1
                    );

                    $profilePhotoUrl =
                        $employee->profile_photo
                            ? asset(
                                'storage/'
                                . ltrim(
                                    $employee
                                        ->profile_photo,
                                    '/'
                                )
                            )
                            : null;
                @endphp

                <div
                    class="flex flex-col gap-5 sm:flex-row sm:items-center"
                >
                    @if ($profilePhotoUrl)
                        <img
                            src="{{ $profilePhotoUrl }}"
                            alt="{{ $employeeName }}"
                            class="h-20 w-20 rounded-2xl object-cover ring-1 ring-zinc-200 dark:ring-zinc-700"
                        >
                    @else
                        <div
                            class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-zinc-100 text-2xl font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                        >
                            {{ $initial }}
                        </div>
                    @endif

                    <div class="min-w-0 flex-1">
                        <h2
                            class="truncate text-xl font-semibold text-zinc-900 dark:text-white"
                        >
                            {{ $employeeName }}
                        </h2>

                        <p
                            class="mt-1 text-sm text-zinc-500"
                        >
                            {{ $employee->employee_code }}
                        </p>

                        <div
                            class="mt-3 flex flex-wrap gap-2"
                        >
                            <flux:badge
                                size="sm"
                                color="blue"
                            >
                                {{ $employee
                                    ->position?->title
                                    ?? 'មិនទាន់មានមុខតំណែង' }}
                            </flux:badge>

                            <flux:badge
                                size="sm"
                                color="zinc"
                            >
                                {{ $employee
                                    ->department?->name
                                    ?? 'មិនទាន់មានផ្នែក' }}
                            </flux:badge>

                            <flux:badge
                                size="sm"
                                color="zinc"
                            >
                                {{ $employee
                                    ->branch?->name
                                    ?? 'មិនទាន់មានសាខា' }}
                            </flux:badge>
                        </div>
                    </div>
                </div>
            </div>

            <div
                class="rounded-2xl border border-zinc-200 bg-zinc-900 p-6 text-center text-white dark:border-zinc-700"
            >
                <p
                    class="text-sm text-zinc-300"
                >
                    ម៉ោងបច្ចុប្បន្ន
                </p>

                <p
                    class="mt-2 text-4xl font-semibold tabular-nums"
                >
                    {{ now()->format('H:i:s') }}
                </p>

                <p class="mt-2 text-sm text-zinc-300">
                    {{ now()->format('d/m/Y') }}
                </p>
            </div>
        </div>

        {{-- Attendance action --}}
        <div
            class="grid gap-6 xl:grid-cols-[1fr_360px]"
        >
            <section
                class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6"
            >
                <div
                    class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between"
                >
                    <div>
                        <h2
                            class="text-lg font-semibold text-zinc-900 dark:text-white"
                        >
                            វត្តមានថ្ងៃនេះ
                        </h2>

                        <p
                            class="mt-1 text-sm text-zinc-500"
                        >
                            ស្ថានភាពកត់ត្រាម៉ោងចូល
                            និងម៉ោងចេញ។
                        </p>
                    </div>

                    <flux:badge
                        size="sm"
                        :color="$statusColor"
                    >
                        {{ $statusLabel }}
                    </flux:badge>
                </div>

                <div
                    class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
                >
                    <div
                        class="rounded-xl bg-zinc-50 p-4 dark:bg-zinc-800"
                    >
                        <p class="text-sm text-zinc-500">
                            ម៉ោងចូល
                        </p>

                        <p
                            class="mt-2 text-xl font-semibold text-zinc-900 dark:text-white"
                        >
                            {{ $attendance?->check_in_at
                                ? $attendance
                                    ->check_in_at
                                    ->format('H:i:s')
                                : '—' }}
                        </p>
                    </div>

                    <div
                        class="rounded-xl bg-zinc-50 p-4 dark:bg-zinc-800"
                    >
                        <p class="text-sm text-zinc-500">
                            ម៉ោងចេញ
                        </p>

                        <p
                            class="mt-2 text-xl font-semibold text-zinc-900 dark:text-white"
                        >
                            {{ $attendance?->check_out_at
                                ? $attendance
                                    ->check_out_at
                                    ->format('H:i:s')
                                : '—' }}
                        </p>
                    </div>

                    <div
                        class="rounded-xl bg-zinc-50 p-4 dark:bg-zinc-800"
                    >
                        <p class="text-sm text-zinc-500">
                            ម៉ោងធ្វើការ
                        </p>

                        <p
                            class="mt-2 font-semibold text-zinc-900 dark:text-white"
                        >
                            {{ $durationLabel(
                                $attendance
                                    ?->worked_minutes
                            ) }}
                        </p>
                    </div>

                    <div
                        class="rounded-xl bg-zinc-50 p-4 dark:bg-zinc-800"
                    >
                        <p class="text-sm text-zinc-500">
                            ថែមម៉ោង
                        </p>

                        <p
                            class="mt-2 font-semibold text-zinc-900 dark:text-white"
                        >
                            {{ $durationLabel(
                                $attendance
                                    ?->overtime_minutes
                            ) }}
                        </p>
                    </div>
                </div>

                <div
                    class="mt-5 grid gap-4 md:grid-cols-2"
                >
                    <flux:input
                        wire:model="location"
                        label="ទីតាំង"
                        placeholder="ឧទាហរណ៍៖ ការិយាល័យកណ្ដាល"
                    />

                    <flux:textarea
                        wire:model="notes"
                        label="កំណត់សម្គាល់"
                        placeholder="ព័ត៌មានបន្ថែម..."
                        rows="2"
                    />
                </div>

                <div
                    class="mt-6 border-t border-zinc-200 pt-5 dark:border-zinc-700"
                >
                    @if (
                        ! $attendance
                        || ! $attendance->check_in_at
                    )
                        @can('attendance.checkin')
                            <flux:button
                                type="button"
                                variant="primary"
                                class="w-full sm:w-auto"
                                wire:click="checkIn"
                                wire:loading.attr="disabled"
                                wire:target="checkIn"
                            >
                                <span
                                    wire:loading.remove
                                    wire:target="checkIn"
                                >
                                    កត់ត្រាម៉ោងចូល
                                </span>

                                <span
                                    wire:loading
                                    wire:target="checkIn"
                                >
                                    កំពុងកត់ត្រា...
                                </span>
                            </flux:button>
                        @endcan
                    @elseif (
                        ! $attendance->check_out_at
                    )
                        @can('attendance.checkout')
                            <flux:button
                                type="button"
                                variant="primary"
                                class="w-full sm:w-auto"
                                wire:click="checkOut"
                                wire:loading.attr="disabled"
                                wire:target="checkOut"
                            >
                                <span
                                    wire:loading.remove
                                    wire:target="checkOut"
                                >
                                    កត់ត្រាម៉ោងចេញ
                                </span>

                                <span
                                    wire:loading
                                    wire:target="checkOut"
                                >
                                    កំពុងកត់ត្រា...
                                </span>
                            </flux:button>
                        @endcan
                    @else
                        <div
                            class="rounded-xl bg-green-50 p-4 text-sm text-green-800 dark:bg-green-950/30 dark:text-green-200"
                        >
                            ការកត់ត្រាវត្តមានសម្រាប់ថ្ងៃនេះ
                            បានបញ្ចប់រួចរាល់។
                        </div>
                    @endif
                </div>
            </section>

            {{-- Schedule --}}
            <aside
                class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <h2
                    class="text-lg font-semibold text-zinc-900 dark:text-white"
                >
                    កាលវិភាគថ្ងៃនេះ
                </h2>

                @if (! $schedule)
                    <div
                        class="mt-5 rounded-xl bg-amber-50 p-4 text-sm text-amber-800 dark:bg-amber-950/30 dark:text-amber-200"
                    >
                        មិនមានកាលវិភាគសម្រាប់ថ្ងៃនេះទេ។
                        ប្រព័ន្ធនៅតែអនុញ្ញាតឱ្យកត់ត្រាវត្តមាន។
                    </div>
                @elseif ($schedule->is_rest_day)
                    <div
                        class="mt-5 rounded-xl bg-amber-50 p-4 text-sm text-amber-800 dark:bg-amber-950/30 dark:text-amber-200"
                    >
                        ថ្ងៃនេះត្រូវបានកំណត់ជាថ្ងៃសម្រាក។
                    </div>
                @else
                    <dl class="mt-5 space-y-5">
                        <div>
                            <dt class="text-sm text-zinc-500">
                                វេនការងារ
                            </dt>

                            <dd
                                class="mt-1 font-medium text-zinc-900 dark:text-white"
                            >
                                {{ $schedule
                                    ->workShift?->name
                                    ?? 'មិនទាន់កំណត់' }}
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-zinc-500">
                                ម៉ោងការងារ
                            </dt>

                            <dd
                                class="mt-1 font-medium text-zinc-900 dark:text-white"
                            >
                                @if ($schedule->workShift)
                                    {{ substr(
                                        $schedule
                                            ->workShift
                                            ->start_time,
                                        0,
                                        5
                                    ) }}

                                    –

                                    {{ substr(
                                        $schedule
                                            ->workShift
                                            ->end_time,
                                        0,
                                        5
                                    ) }}
                                @else
                                    មិនទាន់កំណត់
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-zinc-500">
                                ពេលសម្រាក
                            </dt>

                            <dd
                                class="mt-1 font-medium text-zinc-900 dark:text-white"
                            >
                                {{ $schedule
                                    ->workShift?->break_minutes
                                    ?? 0 }}
                                នាទី
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-zinc-500">
                                អនុញ្ញាតឱ្យយឺត
                            </dt>

                            <dd
                                class="mt-1 font-medium text-zinc-900 dark:text-white"
                            >
                                {{ $schedule
                                    ->workShift?->late_grace_minutes
                                    ?? 0 }}
                                នាទី
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm text-zinc-500">
                                សាខា
                            </dt>

                            <dd
                                class="mt-1 font-medium text-zinc-900 dark:text-white"
                            >
                                {{ $schedule
                                    ->branch?->name
                                    ?? $employee
                                        ->branch?->name
                                    ?? 'មិនទាន់កំណត់' }}
                            </dd>
                        </div>
                    </dl>
                @endif
            </aside>
        </div>

        {{-- Metrics --}}
        @if ($attendance)
            <div
                class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
            >
                <div
                    class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
                >
                    <p class="text-sm text-zinc-500">
                        មកយឺត
                    </p>

                    <p
                        class="mt-2 text-2xl font-semibold text-amber-600"
                    >
                        {{ $attendance->late_minutes }}
                        នាទី
                    </p>
                </div>

                <div
                    class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
                >
                    <p class="text-sm text-zinc-500">
                        ចេញមុន
                    </p>

                    <p
                        class="mt-2 text-2xl font-semibold text-red-600"
                    >
                        {{ $attendance
                            ->early_leave_minutes }}
                        នាទី
                    </p>
                </div>

                <div
                    class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
                >
                    <p class="text-sm text-zinc-500">
                        ម៉ោងធ្វើការ
                    </p>

                    <p
                        class="mt-2 text-xl font-semibold text-zinc-900 dark:text-white"
                    >
                        {{ $durationLabel(
                            $attendance
                                ->worked_minutes
                        ) }}
                    </p>
                </div>

                <div
                    class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
                >
                    <p class="text-sm text-zinc-500">
                        ថែមម៉ោង
                    </p>

                    <p
                        class="mt-2 text-xl font-semibold text-blue-600"
                    >
                        {{ $durationLabel(
                            $attendance
                                ->overtime_minutes
                        ) }}
                    </p>
                </div>
            </div>
        @endif

        {{-- Recent attendance --}}
        <section
            class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
        >
            <div
                class="border-b border-zinc-200 p-5 dark:border-zinc-700"
            >
                <h2
                    class="text-lg font-semibold text-zinc-900 dark:text-white"
                >
                    ប្រវត្តិវត្តមានថ្មីៗ
                </h2>

                <p class="mt-1 text-sm text-zinc-500">
                    កំណត់ត្រា ១០ ចុងក្រោយ។
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="bg-zinc-50 dark:bg-zinc-800"
                    >
                        <tr>
                            <th class="px-5 py-4 font-medium">
                                ថ្ងៃ
                            </th>

                            <th class="px-5 py-4 font-medium">
                                ម៉ោងចូល
                            </th>

                            <th class="px-5 py-4 font-medium">
                                ម៉ោងចេញ
                            </th>

                            <th class="px-5 py-4 font-medium">
                                ម៉ោងធ្វើការ
                            </th>

                            <th class="px-5 py-4 font-medium">
                                យឺត
                            </th>

                            <th class="px-5 py-4 font-medium">
                                ថែមម៉ោង
                            </th>

                            <th class="px-5 py-4 font-medium">
                                ស្ថានភាព
                            </th>
                        </tr>
                    </thead>

                    <tbody
                        class="divide-y divide-zinc-200 dark:divide-zinc-700"
                    >
                        @forelse (
                            $this->recentAttendances
                            as $record
                        )
                            @php
                                $recordStatusLabel =
                                    match (
                                        $record->status
                                    ) {
                                        'present' =>
                                            'មានវត្តមាន',

                                        'late' =>
                                            'មកយឺត',

                                        'absent' =>
                                            'អវត្តមាន',

                                        'half_day' =>
                                            'ពាក់កណ្ដាលថ្ងៃ',

                                        'on_leave' =>
                                            'ឈប់សម្រាក',

                                        default =>
                                            $record->status,
                                    };

                                $recordStatusColor =
                                    match (
                                        $record->status
                                    ) {
                                        'present' => 'green',
                                        'late' => 'amber',
                                        'absent' => 'red',
                                        'half_day' => 'blue',
                                        'on_leave' => 'purple',
                                        default => 'zinc',
                                    };
                            @endphp

                            <tr
                                class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                            >
                                <td class="px-5 py-4">
                                    <div
                                        class="font-medium text-zinc-900 dark:text-white"
                                    >
                                        {{ $record
                                            ->work_date
                                            ->format(
                                                'd/m/Y'
                                            ) }}
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    {{ $record->check_in_at
                                        ? $record
                                            ->check_in_at
                                            ->format(
                                                'H:i'
                                            )
                                        : '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ $record->check_out_at
                                        ? $record
                                            ->check_out_at
                                            ->format(
                                                'H:i'
                                            )
                                        : '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ $durationLabel(
                                        $record
                                            ->worked_minutes
                                    ) }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ $record
                                        ->late_minutes }}
                                    នាទី
                                </td>

                                <td class="px-5 py-4">
                                    {{ $durationLabel(
                                        $record
                                            ->overtime_minutes
                                    ) }}
                                </td>

                                <td class="px-5 py-4">
                                    <flux:badge
                                        size="sm"
                                        :color="$recordStatusColor"
                                    >
                                        {{ $recordStatusLabel }}
                                    </flux:badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="7"
                                    class="px-5 py-14 text-center text-zinc-500"
                                >
                                    មិនទាន់មានប្រវត្តិវត្តមាន។
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</div>