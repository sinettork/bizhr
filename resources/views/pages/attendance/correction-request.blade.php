<?php

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public ?string $attendanceId = null;

    public ?string $requestedCheckIn = null;

    public ?string $requestedCheckOut = null;

    public string $reason = '';

    public string $statusFilter = 'all';

    #[Computed]
    public function employee()
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        return $user
            ->employee()
            ->with([
                'branch',
                'department',
                'position',
            ])
            ->first();
    }

    #[Computed]
    public function attendanceOptions()
    {
        $employee = $this->employee;

        if (! $employee) {
            return collect();
        }

        return Attendance::query()
            ->where('employee_id', $employee->id)
            ->where(function (Builder $query): void {
                $query
                    ->whereNotNull('check_in_at')
                    ->orWhereNotNull('check_out_at');
            })
            ->with([
                'corrections' => fn ($query) => $query
                    ->latest('id'),
            ])
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->limit(90)
            ->get();
    }

    #[Computed]
    public function selectedAttendance()
    {
        $employee = $this->employee;

        if (
            ! $employee
            || blank($this->attendanceId)
        ) {
            return null;
        }

        return Attendance::query()
            ->whereKey((int) $this->attendanceId)
            ->where('employee_id', $employee->id)
            ->with('corrections')
            ->first();
    }

    #[Computed]
    public function recentRequests()
    {
        $employee = $this->employee;

        if (! $employee) {
            return collect();
        }

        $allowedStatuses = [
            'pending',
            'approved',
            'rejected',
        ];

        return AttendanceCorrection::query()
            ->where('employee_id', $employee->id)
            ->when(
                in_array(
                    $this->statusFilter,
                    $allowedStatuses,
                    true
                ),
                fn ($query) => $query->where(
                    'status',
                    $this->statusFilter
                )
            )
            ->with([
                'attendance',
                'reviewedBy',
            ])
            ->latest('id')
            ->limit(30)
            ->get();
    }

    public function updatedAttendanceId(
        ?string $value
    ): void {
        $this->resetValidation();

        if (blank($value)) {
            $this->requestedCheckIn = null;
            $this->requestedCheckOut = null;

            unset($this->selectedAttendance);

            return;
        }

        $employee = $this->employee;

        if (! $employee) {
            $this->attendanceId = null;

            return;
        }

        $attendance = Attendance::query()
            ->whereKey((int) $value)
            ->where('employee_id', $employee->id)
            ->first();

        if (! $attendance) {
            $this->attendanceId = null;
            $this->requestedCheckIn = null;
            $this->requestedCheckOut = null;

            unset($this->selectedAttendance);

            return;
        }

        $this->requestedCheckIn = $attendance
            ->check_in_at
            ?->format('Y-m-d\TH:i');

        $this->requestedCheckOut = $attendance
            ->check_out_at
            ?->format('Y-m-d\TH:i');

        unset($this->selectedAttendance);
    }

    protected function rules(): array
    {
        $employeeId = $this->employee?->id ?? 0;

        return [
            'attendanceId' => [
                'required',
                'integer',
                Rule::exists(
                    'attendances',
                    'id'
                )->where(
                    fn ($query) => $query->where(
                        'employee_id',
                        $employeeId
                    )
                ),
            ],

            'requestedCheckIn' => [
                'nullable',
                'date',
            ],

            'requestedCheckOut' => [
                'nullable',
                'date',
            ],

            'reason' => [
                'required',
                'string',
                'min:5',
                'max:2000',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'attendanceId.required' =>
                'សូមជ្រើសរើសកំណត់ត្រាវត្តមាន។',

            'attendanceId.integer' =>
                'កំណត់ត្រាវត្តមានមិនត្រឹមត្រូវ។',

            'attendanceId.exists' =>
                'កំណត់ត្រាវត្តមាននេះមិនត្រឹមត្រូវ។',

            'requestedCheckIn.date' =>
                'ម៉ោងចូលថ្មីមិនត្រឹមត្រូវ។',

            'requestedCheckOut.date' =>
                'ម៉ោងចេញថ្មីមិនត្រឹមត្រូវ។',

            'reason.required' =>
                'សូមបញ្ចូលមូលហេតុ។',

            'reason.min' =>
                'មូលហេតុត្រូវមានយ៉ាងតិច ៥ តួអក្សរ។',

            'reason.max' =>
                'មូលហេតុមិនអាចលើសពី ២០០០ តួអក្សរ។',
        ];
    }

    public function submit(): void
    {
        abort_unless(
            auth()->user()?->can(
                'attendance.correction.request'
            ),
            403
        );

        $employee = $this->employee;

        if (! $employee) {
            $this->addError(
                'attendanceId',
                'គណនីនេះមិនទាន់បានភ្ជាប់ជាមួយបុគ្គលិកទេ។'
            );

            return;
        }

        $validated = $this->validate();

        $attendance = Attendance::query()
            ->whereKey(
                (int) $validated['attendanceId']
            )
            ->where(
                'employee_id',
                $employee->id
            )
            ->firstOrFail();

        $pendingExists = AttendanceCorrection::query()
            ->where(
                'attendance_id',
                $attendance->id
            )
            ->where(
                'employee_id',
                $employee->id
            )
            ->where(
                'status',
                'pending'
            )
            ->exists();

        if ($pendingExists) {
            $this->addError(
                'attendanceId',
                'កំណត់ត្រានេះមានសំណើកំពុងរង់ចាំរួចហើយ។'
            );

            return;
        }

        $newCheckIn = filled(
            $validated['requestedCheckIn']
        )
            ? Carbon::parse(
                $validated['requestedCheckIn']
            )
            : null;

        $newCheckOut = filled(
            $validated['requestedCheckOut']
        )
            ? Carbon::parse(
                $validated['requestedCheckOut']
            )
            : null;

        $hasCheckInChange =
            $newCheckIn !== null
            && (
                ! $attendance->check_in_at
                || $newCheckIn->format('Y-m-d H:i')
                    !== $attendance
                        ->check_in_at
                        ->format('Y-m-d H:i')
            );

        $hasCheckOutChange =
            $newCheckOut !== null
            && (
                ! $attendance->check_out_at
                || $newCheckOut->format('Y-m-d H:i')
                    !== $attendance
                        ->check_out_at
                        ->format('Y-m-d H:i')
            );

        if (
            ! $hasCheckInChange
            && ! $hasCheckOutChange
        ) {
            $this->addError(
                'requestedCheckIn',
                'សូមកែប្រែម៉ោងចូល ឬម៉ោងចេញយ៉ាងតិចមួយ។'
            );

            return;
        }

        $effectiveCheckIn = $hasCheckInChange
            ? $newCheckIn
            : $attendance->check_in_at;

        $effectiveCheckOut = $hasCheckOutChange
            ? $newCheckOut
            : $attendance->check_out_at;

        if (
            $effectiveCheckIn
            && $effectiveCheckOut
            && $effectiveCheckOut->lt(
                $effectiveCheckIn
            )
        ) {
            $this->addError(
                'requestedCheckOut',
                'ម៉ោងចេញត្រូវតែនៅក្រោយម៉ោងចូល។'
            );

            return;
        }

        DB::transaction(function () use (
            $attendance,
            $employee,
            $newCheckIn,
            $newCheckOut,
            $hasCheckInChange,
            $hasCheckOutChange,
            $validated
        ): void {
            AttendanceCorrection::create([
                'attendance_id' =>
                    $attendance->id,

                'employee_id' =>
                    $employee->id,

                'requested_check_in' =>
                    $hasCheckInChange
                        ? $newCheckIn
                        : null,

                'requested_check_out' =>
                    $hasCheckOutChange
                        ? $newCheckOut
                        : null,

                'reason' => trim(
                    $validated['reason']
                ),

                'status' => 'pending',
            ]);
        });

        session()->flash(
            'success',
            'សំណើកែប្រែវត្តមានត្រូវបានផ្ញើដោយជោគជ័យ។'
        );

        $this->reset([
            'attendanceId',
            'requestedCheckIn',
            'requestedCheckOut',
            'reason',
        ]);

        $this->resetValidation();

        unset(
            $this->attendanceOptions,
            $this->selectedAttendance,
            $this->recentRequests
        );
    }

    public function clearForm(): void
    {
        $this->reset([
            'attendanceId',
            'requestedCheckIn',
            'requestedCheckOut',
            'reason',
        ]);

        $this->resetValidation();

        unset($this->selectedAttendance);
    }
};
?>

<div class="w-full space-y-6 p-4 sm:p-6">
    {{-- Header --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">
                ស្នើសុំកែប្រែវត្តមាន
            </h1>

            <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                ស្នើសុំកែម៉ោងចូល ឬម៉ោងចេញដែលមិនត្រឹមត្រូវ
            </p>
        </div>

        <flux:button
            variant="ghost"
            icon="arrow-left"
            :href="route('attendance.checkinout')"
            wire:navigate
        >
            ទៅទំព័រវត្តមាន
        </flux:button>
    </div>

    {{-- Success message --}}
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300">
            {{ session('success') }}
        </div>
    @endif

    {{-- Missing employee connection --}}
    @if (! $this->employee)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6 dark:border-amber-900 dark:bg-amber-950/30">
            <div class="flex gap-4">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-900/50 dark:text-amber-300">
                    <svg
                        class="h-6 w-6"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path d="M12 9v4"/>
                        <path d="M12 17h.01"/>
                        <path d="M10.3 2.9 1.8 17a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 2.9a2 2 0 0 0-3.4 0Z"/>
                    </svg>
                </div>

                <div>
                    <h2 class="font-semibold text-amber-900 dark:text-amber-200">
                        គណនីមិនទាន់បានភ្ជាប់ជាមួយបុគ្គលិក
                    </h2>

                    <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">
                        សូមទាក់ទងអ្នកគ្រប់គ្រង ដើម្បីភ្ជាប់គណនីនេះជាមួយកំណត់ត្រាបុគ្គលិក។
                    </p>
                </div>
            </div>
        </div>
    @else
        {{-- Employee card --}}
        <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-indigo-100 text-lg font-bold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">
                        {{ strtoupper(
                            substr(
                                $this->employee->full_name_en
                                    ?? $this->employee->full_name_km
                                    ?? 'E',
                                0,
                                1
                            )
                        ) }}
                    </div>

                    <div>
                        <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                            បុគ្គលិក
                        </p>

                        <h2 class="mt-0.5 text-lg font-semibold text-zinc-900 dark:text-white">
                            {{ $this->employee->full_name_km
                                ?? $this->employee->full_name_en
                                ?? 'មិនមានឈ្មោះ' }}
                        </h2>

                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            {{ $this->employee->employee_code }}
                        </p>
                    </div>
                </div>

                <div class="grid gap-1 text-sm text-zinc-500 dark:text-zinc-400 sm:text-right">
                    <span>
                        {{ $this->employee->position->title
                            ?? 'មិនមានមុខតំណែង' }}
                    </span>

                    <span>
                        {{ $this->employee->department->name
                            ?? 'មិនមានផ្នែក' }}
                    </span>

                    <span>
                        {{ $this->employee->branch->name
                            ?? 'មិនមានសាខា' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- No attendance records --}}
        @if ($this->attendanceOptions->isEmpty())
            <div class="rounded-2xl border border-zinc-200 bg-white px-6 py-14 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-indigo-50 text-indigo-600 dark:bg-indigo-950/60 dark:text-indigo-300">
                    <svg
                        class="h-8 w-8"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                    >
                        <circle cx="12" cy="12" r="9"/>
                        <path d="M12 7v5l3 2"/>
                    </svg>
                </div>

                <h2 class="mt-5 text-lg font-semibold text-zinc-900 dark:text-white">
                    មិនទាន់មានកំណត់ត្រាវត្តមាន
                </h2>

                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                    អ្នកត្រូវចុះវត្តមានជាមុនសិន ទើបអាចស្នើសុំកែម៉ោងចូល ឬម៉ោងចេញបាន។
                </p>

                <a
                    href="{{ route('attendance.checkinout') }}"
                    wire:navigate
                    class="mt-6 inline-flex items-center justify-center rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700"
                >
                    ទៅចុះវត្តមាន
                </a>
            </div>
        @else
            {{-- Correction form --}}
            <form
                wire:submit="submit"
                class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
            >
                <div class="border-b border-zinc-200 px-5 py-5 dark:border-zinc-700 sm:px-6">
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                        ព័ត៌មានសំណើ
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        ជ្រើសរើសកំណត់ត្រា បន្ទាប់មកកែតែម៉ោងដែលមិនត្រឹមត្រូវ
                    </p>
                </div>

                <div class="space-y-6 p-5 sm:p-6">
                    {{-- Select attendance --}}
                    <div>
                        <label
                            for="attendanceId"
                            class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                        >
                            កំណត់ត្រាវត្តមាន
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="attendanceId"
                            wire:model.live="attendanceId"
                            class="block w-full rounded-xl border-zinc-300 bg-white text-sm text-zinc-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                        >
                            <option value="">
                                សូមជ្រើសរើសកំណត់ត្រា
                            </option>

                            @foreach ($this->attendanceOptions as $attendance)
                                @php
                                    $hasPending = $attendance
                                        ->corrections
                                        ->contains(
                                            'status',
                                            'pending'
                                        );
                                @endphp

                                <option value="{{ $attendance->id }}">
                                    {{ $attendance->work_date?->format('d/m/Y') }}

                                    — ចូល:
                                    {{ $attendance->check_in_at?->format('H:i') ?? 'មិនមាន' }}

                                    — ចេញ:
                                    {{ $attendance->check_out_at?->format('H:i') ?? 'មិនមាន' }}

                                    {{ $hasPending ? '— កំពុងរង់ចាំ' : '' }}
                                </option>
                            @endforeach
                        </select>

                        @error('attendanceId')
                            <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    @if (! $this->selectedAttendance)
                        <div class="rounded-xl border border-dashed border-zinc-300 bg-zinc-50 px-5 py-8 text-center dark:border-zinc-700 dark:bg-zinc-900/50">
                            <p class="text-sm text-zinc-500 dark:text-zinc-400">
                                សូមជ្រើសរើសកំណត់ត្រាវត្តមានខាងលើ។
                            </p>
                        </div>
                    @else
                        @php
                            $selected =
                                $this->selectedAttendance;

                            $hasPending =
                                $selected
                                    ->corrections
                                    ->contains(
                                        'status',
                                        'pending'
                                    );
                        @endphp

                        {{-- Current attendance information --}}
                        <div class="rounded-2xl bg-zinc-50 p-5 dark:bg-zinc-900">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <div>
                                    <p class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                        ព័ត៌មានវត្តមានបច្ចុប្បន្ន
                                    </p>

                                    <h3 class="mt-1 font-semibold text-zinc-900 dark:text-white">
                                        {{ $selected->work_date?->format('d/m/Y') }}
                                    </h3>
                                </div>

                                @if ($hasPending)
                                    <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-medium text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                        មានសំណើកំពុងរង់ចាំ
                                    </span>
                                @endif
                            </div>

                            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                <div>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                        វេនការងារ
                                    </p>

                                    <p class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $selected->scheduled_start
                                            ? substr(
                                                (string) $selected->scheduled_start,
                                                0,
                                                5
                                            )
                                            : '—' }}

                                        -

                                        {{ $selected->scheduled_end
                                            ? substr(
                                                (string) $selected->scheduled_end,
                                                0,
                                                5
                                            )
                                            : '—' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                        ម៉ោងចូល
                                    </p>

                                    <p class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $selected->check_in_at?->format('d/m/Y H:i')
                                            ?? 'មិនមាន' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                        ម៉ោងចេញ
                                    </p>

                                    <p class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $selected->check_out_at?->format('d/m/Y H:i')
                                            ?? 'មិនមាន' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                        ស្ថានភាព
                                    </p>

                                    <p class="mt-1 text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $selected->status ?? '—' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        {{-- Requested time inputs --}}
                        <div class="grid gap-5 md:grid-cols-2">
                            <div>
                                <label
                                    for="requestedCheckIn"
                                    class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                                >
                                    ម៉ោងចូលដែលត្រូវការ
                                </label>

                                <input
                                    id="requestedCheckIn"
                                    type="datetime-local"
                                    wire:model="requestedCheckIn"
                                    class="block w-full rounded-xl border-zinc-300 bg-white text-sm text-zinc-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                                >

                                @error('requestedCheckIn')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <div>
                                <label
                                    for="requestedCheckOut"
                                    class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                                >
                                    ម៉ោងចេញដែលត្រូវការ
                                </label>

                                <input
                                    id="requestedCheckOut"
                                    type="datetime-local"
                                    wire:model="requestedCheckOut"
                                    class="block w-full rounded-xl border-zinc-300 bg-white text-sm text-zinc-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                                >

                                @error('requestedCheckOut')
                                    <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>

                        {{-- Reason --}}
                        <div>
                            <label
                                for="reason"
                                class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                            >
                                មូលហេតុ
                                <span class="text-red-500">*</span>
                            </label>

                            <textarea
                                id="reason"
                                wire:model="reason"
                                rows="4"
                                maxlength="2000"
                                placeholder="ឧទាហរណ៍៖ ភ្លេចចុចចូលការងារ ឬប្រព័ន្ធមានបញ្ហា..."
                                class="block w-full rounded-xl border-zinc-300 bg-white text-sm text-zinc-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                            ></textarea>

                            @error('reason')
                                <p class="mt-2 text-sm text-red-600 dark:text-red-400">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-800 dark:border-blue-900 dark:bg-blue-950/30 dark:text-blue-300">
                            កែតែម៉ោងដែលមិនត្រឹមត្រូវ ហើយសរសេរមូលហេតុឱ្យបានច្បាស់។
                        </div>
                    @endif
                </div>

                @if ($this->selectedAttendance)
                    <div class="flex flex-col-reverse gap-3 border-t border-zinc-200 bg-zinc-50 px-5 py-4 dark:border-zinc-700 dark:bg-zinc-900/50 sm:flex-row sm:justify-end sm:px-6">
                        <button
                            type="button"
                            wire:click="clearForm"
                            class="rounded-xl border border-zinc-300 bg-white px-4 py-2.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200 dark:hover:bg-zinc-800"
                        >
                            សម្អាត
                        </button>

                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="submit"
                            @disabled(
                                $this->selectedAttendance
                                    ->corrections
                                    ->contains(
                                        'status',
                                        'pending'
                                    )
                            )
                            class="rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            <span
                                wire:loading.remove
                                wire:target="submit"
                            >
                                ផ្ញើសំណើ
                            </span>

                            <span
                                wire:loading
                                wire:target="submit"
                            >
                                កំពុងផ្ញើ...
                            </span>
                        </button>
                    </div>
                @endif
            </form>
        @endif

        {{-- Request history --}}
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 border-b border-zinc-200 px-5 py-5 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div>
                    <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">
                        ប្រវត្តិសំណើកែប្រែ
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                        តាមដានស្ថានភាពសំណើរបស់អ្នក
                    </p>
                </div>

                <select
                    wire:model.live="statusFilter"
                    class="rounded-xl border-zinc-300 bg-white text-sm text-zinc-900 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                >
                    <option value="all">
                        ស្ថានភាពទាំងអស់
                    </option>

                    <option value="pending">
                        កំពុងរង់ចាំ
                    </option>

                    <option value="approved">
                        បានអនុម័ត
                    </option>

                    <option value="rejected">
                        បានបដិសេធ
                    </option>
                </select>
            </div>

            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->recentRequests as $request)
                    @php
                        $statusLabel = match (
                            $request->status
                        ) {
                            'approved' =>
                                'បានអនុម័ត',

                            'rejected' =>
                                'បានបដិសេធ',

                            default =>
                                'កំពុងរង់ចាំ',
                        };

                        $statusClass = match (
                            $request->status
                        ) {
                            'approved' =>
                                'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300',

                            'rejected' =>
                                'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300',

                            default =>
                                'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
                        };
                    @endphp

                    <div
                        wire:key="attendance-correction-{{ $request->id }}"
                        class="p-5 sm:p-6"
                    >
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-semibold text-zinc-900 dark:text-white">
                                        {{ $request
                                            ->attendance
                                            ?->work_date
                                            ?->format('d/m/Y')
                                            ?? 'មិនមានកាលបរិច្ឆេទ' }}
                                    </h3>

                                    <span class="rounded-full px-3 py-1 text-xs font-medium {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </div>

                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                                    បានស្នើនៅ
                                    {{ $request->created_at?->format('d/m/Y H:i') }}
                                </p>
                            </div>

                            @if ($request->reviewedBy)
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    ពិនិត្យដោយ:
                                    {{ $request->reviewedBy->name }}
                                </p>
                            @endif
                        </div>

                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-xl bg-zinc-50 p-4 dark:bg-zinc-900">
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                    ម៉ោងចូលដែលបានស្នើ
                                </p>

                                <p class="mt-1 text-sm font-semibold text-indigo-600 dark:text-indigo-400">
                                    {{ $request->requested_check_in?->format('d/m/Y H:i')
                                        ?? 'មិនបានកែប្រែ' }}
                                </p>
                            </div>

                            <div class="rounded-xl bg-zinc-50 p-4 dark:bg-zinc-900">
                                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                    ម៉ោងចេញដែលបានស្នើ
                                </p>

                                <p class="mt-1 text-sm font-semibold text-indigo-600 dark:text-indigo-400">
                                    {{ $request->requested_check_out?->format('d/m/Y H:i')
                                        ?? 'មិនបានកែប្រែ' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-4">
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">
                                មូលហេតុ
                            </p>

                            <p class="mt-1 whitespace-pre-line text-sm leading-6 text-zinc-700 dark:text-zinc-300">
                                {{ $request->reason }}
                            </p>
                        </div>

                        @if ($request->review_note)
                            <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 p-4 dark:border-blue-900 dark:bg-blue-950/30">
                                <p class="text-xs font-medium text-blue-700 dark:text-blue-300">
                                    ចំណាំពីអ្នកពិនិត្យ
                                </p>

                                <p class="mt-1 whitespace-pre-line text-sm text-blue-900 dark:text-blue-200">
                                    {{ $request->review_note }}
                                </p>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="px-6 py-12 text-center">
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            មិនទាន់មានសំណើកែប្រែវត្តមានទេ។
                        </p>
                    </div>
                @endforelse
            </div>
        </div>
    @endif
</div>