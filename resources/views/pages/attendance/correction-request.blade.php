<?php

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('ស្នើសុំកែប្រែវត្តមាន')] class extends Component
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
                'branch:id,name',
                'department:id,name',
                'position:id,title',
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
                    ->select([
                        'id',
                        'attendance_id',
                        'status',
                    ])
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

        if (! $employee || blank($this->attendanceId)) {
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

        $statuses = [
            'pending',
            'approved',
            'rejected',
        ];

        return AttendanceCorrection::query()
            ->where('employee_id', $employee->id)
            ->when(
                in_array(
                    $this->statusFilter,
                    $statuses,
                    true
                ),
                fn ($query) => $query->where(
                    'status',
                    $this->statusFilter
                )
            )
            ->with([
                'attendance',
                'reviewedBy:id,name',
            ])
            ->latest('id')
            ->limit(50)
            ->get();
    }

    #[Computed]
    public function statistics(): array
    {
        $employee = $this->employee;

        if (! $employee) {
            return [
                'pending' => 0,
                'approved' => 0,
                'rejected' => 0,
                'total' => 0,
            ];
        }

        $query = AttendanceCorrection::query()
            ->where('employee_id', $employee->id);

        return [
            'pending' => (clone $query)
                ->where('status', 'pending')
                ->count(),

            'approved' => (clone $query)
                ->where('status', 'approved')
                ->count(),

            'rejected' => (clone $query)
                ->where('status', 'rejected')
                ->count(),

            'total' => (clone $query)->count(),
        ];
    }

    public function updatedAttendanceId(?string $value): void
    {
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

    public function updatedStatusFilter(): void
    {
        unset($this->recentRequests);
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
            ->where('status', 'pending')
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
            ? CarbonImmutable::parse(
                $validated['requestedCheckIn']
            )
            : null;

        $newCheckOut = filled(
            $validated['requestedCheckOut']
        )
            ? CarbonImmutable::parse(
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

        if (! $hasCheckInChange && ! $hasCheckOutChange) {
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
            && $effectiveCheckOut->lessThanOrEqualTo(
                $effectiveCheckIn
            )
        ) {
            $this->addError(
                'requestedCheckOut',
                'ម៉ោងចេញត្រូវតែនៅក្រោយម៉ោងចូល។'
            );

            return;
        }

        AttendanceCorrection::query()->create([
            'attendance_id' => $attendance->id,
            'employee_id' => $employee->id,

            'requested_check_in' => $hasCheckInChange
                ? $newCheckIn
                : null,

            'requested_check_out' => $hasCheckOutChange
                ? $newCheckOut
                : null,

            'reason' => trim($validated['reason']),
            'status' => 'pending',
        ]);

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
            $this->recentRequests,
            $this->statistics
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

<div class="w-full space-y-5 p-4 sm:p-6">
    {{-- Page heading --}}
    <div
        class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
    >
        <div>
            <h1
                class="text-2xl font-semibold text-zinc-900 dark:text-white"
            >
                ស្នើសុំកែប្រែវត្តមាន
            </h1>

            <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                ស្នើកែម៉ោងចូល ឬម៉ោងចេញដែលមិនត្រឹមត្រូវ។
            </p>
        </div>

        @if (Route::has('attendance.checkinout'))
            <flux:button
                type="button"
                variant="ghost"
                icon="arrow-left"
                :href="route('attendance.checkinout')"
                wire:navigate
            >
                ត្រឡប់ទៅវត្តមាន
            </flux:button>
        @endif
    </div>

    @if (session('success'))
        <div
            class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950/40 dark:text-emerald-300"
        >
            {{ session('success') }}
        </div>
    @endif

    @if (! $this->employee)
        <div
            class="rounded-xl border border-amber-200 bg-amber-50 p-5 text-amber-800 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200"
        >
            <h2 class="font-semibold">
                គណនីមិនទាន់បានភ្ជាប់ជាមួយបុគ្គលិក
            </h2>

            <p class="mt-1 text-sm">
                សូមទាក់ទងអ្នកគ្រប់គ្រង ដើម្បីភ្ជាប់គណនីនេះ
                ជាមួយកំណត់ត្រាបុគ្គលិក។
            </p>
        </div>
    @else
        @php
            $employeeName =
                $this->employee->full_name_km
                ?: $this->employee->full_name_en
                ?: trim(
                    ($this->employee->first_name ?? '')
                    .' '.
                    ($this->employee->last_name ?? '')
                )
                ?: 'មិនមានឈ្មោះ';
        @endphp

        {{-- Employee and statistics --}}
        <div
            class="grid gap-3 sm:grid-cols-2 xl:grid-cols-[minmax(280px,2fr)_repeat(4,minmax(120px,1fr))]"
        >
            <div
                class="rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p
                    class="font-semibold text-zinc-900 dark:text-white"
                >
                    {{ $employeeName }}
                </p>

                <p
                    class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                >
                    {{ $this->employee->employee_code ?: '—' }}

                    @if ($this->employee->department)
                        · {{ $this->employee->department->name }}
                    @endif

                    @if ($this->employee->branch)
                        · {{ $this->employee->branch->name }}
                    @endif
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    កំពុងរង់ចាំ
                </p>

                <p class="mt-1 text-xl font-semibold text-amber-600">
                    {{ number_format($this->statistics['pending']) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    បានអនុម័ត
                </p>

                <p class="mt-1 text-xl font-semibold text-emerald-600">
                    {{ number_format($this->statistics['approved']) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    បានបដិសេធ
                </p>

                <p class="mt-1 text-xl font-semibold text-red-600">
                    {{ number_format($this->statistics['rejected']) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    សំណើសរុប
                </p>

                <p
                    class="mt-1 text-xl font-semibold text-zinc-900 dark:text-white"
                >
                    {{ number_format($this->statistics['total']) }}
                </p>
            </div>
        </div>

        @if ($this->attendanceOptions->isEmpty())
            <div
                class="rounded-xl border border-zinc-200 bg-white px-6 py-12 text-center dark:border-zinc-700 dark:bg-zinc-900"
            >
                <h2
                    class="text-lg font-semibold text-zinc-900 dark:text-white"
                >
                    មិនទាន់មានកំណត់ត្រាវត្តមាន
                </h2>

                <p
                    class="mx-auto mt-2 max-w-md text-sm text-zinc-500 dark:text-zinc-400"
                >
                    អ្នកត្រូវចុះវត្តមានជាមុនសិន ទើបអាចស្នើកែម៉ោងបាន។
                </p>
            </div>
        @else
            {{-- Correction form --}}
            <form
                wire:submit="submit"
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <div class="mb-5">
                    <h2
                        class="text-lg font-semibold text-zinc-900 dark:text-white"
                    >
                        ព័ត៌មានសំណើ
                    </h2>

                    <p
                        class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
                    >
                        ជ្រើសរើសកំណត់ត្រា ហើយកែតែម៉ោងដែលមិនត្រឹមត្រូវ។
                    </p>
                </div>

                <div
                    class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4"
                >
                    <div class="md:col-span-2">
                        <label
                            for="attendanceId"
                            class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                        >
                            កំណត់ត្រាវត្តមាន
                            <span class="text-red-500">*</span>
                        </label>

                        <select
                            id="attendanceId"
                            wire:model.live="attendanceId"
                            class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
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
                                    — ចូល {{ $attendance->check_in_at?->format('H:i') ?? 'មិនមាន' }}
                                    — ចេញ {{ $attendance->check_out_at?->format('H:i') ?? 'មិនមាន' }}
                                    {{ $hasPending ? '— កំពុងរង់ចាំ' : '' }}
                                </option>
                            @endforeach
                        </select>

                        @error('attendanceId')
                            <p class="mt-1.5 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="requestedCheckIn"
                            class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                        >
                            ម៉ោងចូលដែលត្រូវការ
                        </label>

                        <input
                            id="requestedCheckIn"
                            type="datetime-local"
                            wire:model="requestedCheckIn"
                            @disabled(! $this->selectedAttendance)
                            class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 disabled:cursor-not-allowed disabled:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-800/50"
                        >

                        @error('requestedCheckIn')
                            <p class="mt-1.5 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div>
                        <label
                            for="requestedCheckOut"
                            class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                        >
                            ម៉ោងចេញដែលត្រូវការ
                        </label>

                        <input
                            id="requestedCheckOut"
                            type="datetime-local"
                            wire:model="requestedCheckOut"
                            @disabled(! $this->selectedAttendance)
                            class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 disabled:cursor-not-allowed disabled:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:disabled:bg-zinc-800/50"
                        >

                        @error('requestedCheckOut')
                            <p class="mt-1.5 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                @if ($this->selectedAttendance)
                    @php
                        $selected = $this->selectedAttendance;

                        $hasPending = $selected
                            ->corrections
                            ->contains(
                                'status',
                                'pending'
                            );
                    @endphp

                    <div
                        class="mt-5 grid gap-px overflow-hidden rounded-xl border border-zinc-200 bg-zinc-200 sm:grid-cols-2 xl:grid-cols-5 dark:border-zinc-700 dark:bg-zinc-700"
                    >
                        <div class="bg-zinc-50 px-4 py-3 dark:bg-zinc-800/70">
                            <p class="text-xs text-zinc-500">
                                កាលបរិច្ឆេទ
                            </p>

                            <p
                                class="mt-1 text-sm font-medium text-zinc-900 dark:text-white"
                            >
                                {{ $selected->work_date?->format('d/m/Y') ?? '—' }}
                            </p>
                        </div>

                        <div class="bg-zinc-50 px-4 py-3 dark:bg-zinc-800/70">
                            <p class="text-xs text-zinc-500">
                                វេនការងារ
                            </p>

                            <p
                                class="mt-1 text-sm font-medium text-zinc-900 dark:text-white"
                            >
                                {{
                                    $selected->scheduled_start
                                        ? substr(
                                            (string) $selected->scheduled_start,
                                            0,
                                            5
                                        )
                                        : '—'
                                }}

                                –

                                {{
                                    $selected->scheduled_end
                                        ? substr(
                                            (string) $selected->scheduled_end,
                                            0,
                                            5
                                        )
                                        : '—'
                                }}
                            </p>
                        </div>

                        <div class="bg-zinc-50 px-4 py-3 dark:bg-zinc-800/70">
                            <p class="text-xs text-zinc-500">
                                ម៉ោងចូលបច្ចុប្បន្ន
                            </p>

                            <p
                                class="mt-1 text-sm font-medium text-zinc-900 dark:text-white"
                            >
                                {{
                                    $selected->check_in_at
                                        ?->format('d/m/Y H:i')
                                    ?? 'មិនមាន'
                                }}
                            </p>
                        </div>

                        <div class="bg-zinc-50 px-4 py-3 dark:bg-zinc-800/70">
                            <p class="text-xs text-zinc-500">
                                ម៉ោងចេញបច្ចុប្បន្ន
                            </p>

                            <p
                                class="mt-1 text-sm font-medium text-zinc-900 dark:text-white"
                            >
                                {{
                                    $selected->check_out_at
                                        ?->format('d/m/Y H:i')
                                    ?? 'មិនមាន'
                                }}
                            </p>
                        </div>

                        <div class="bg-zinc-50 px-4 py-3 dark:bg-zinc-800/70">
                            <p class="text-xs text-zinc-500">
                                ស្ថានភាព
                            </p>

                            @if ($hasPending)
                                <span
                                    class="mt-1 inline-flex rounded-full bg-amber-100 px-2.5 py-1 text-xs font-medium text-amber-700 dark:bg-amber-950 dark:text-amber-300"
                                >
                                    មានសំណើកំពុងរង់ចាំ
                                </span>
                            @else
                                <p
                                    class="mt-1 text-sm font-medium text-zinc-900 dark:text-white"
                                >
                                    {{ $selected->status ?: 'មិនបានកំណត់' }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-5">
                        <label
                            for="reason"
                            class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                        >
                            មូលហេតុ
                            <span class="text-red-500">*</span>
                        </label>

                        <textarea
                            id="reason"
                            wire:model="reason"
                            rows="2"
                            maxlength="2000"
                            placeholder="ឧទាហរណ៍៖ ភ្លេចចុចចូលការងារ ឬប្រព័ន្ធមានបញ្ហា..."
                            class="block min-h-20 w-full resize-y rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                        ></textarea>

                        @error('reason')
                            <p class="mt-1.5 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div
                        class="mt-5 flex flex-col-reverse gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700 sm:flex-row sm:justify-end"
                    >
                        <flux:button
                            type="button"
                            variant="ghost"
                            icon="arrow-path"
                            wire:click="clearForm"
                            class="w-full sm:w-auto"
                        >
                            សម្អាត
                        </flux:button>

                        <flux:button
                            type="submit"
                            variant="primary"
                            icon="paper-airplane"
                            wire:loading.attr="disabled"
                            wire:target="submit"
                            :disabled="$hasPending"
                            class="w-full sm:w-auto"
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
                        </flux:button>
                    </div>
                @else
                    <div
                        class="mt-5 rounded-xl border border-dashed border-zinc-300 bg-zinc-50 px-5 py-8 text-center dark:border-zinc-700 dark:bg-zinc-800/30"
                    >
                        <p class="text-sm text-zinc-500 dark:text-zinc-400">
                            សូមជ្រើសរើសកំណត់ត្រាវត្តមានខាងលើ។
                        </p>
                    </div>
                @endif
            </form>
        @endif

        {{-- Request history --}}
        <div
            class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
        >
            <div
                class="flex flex-col gap-4 border-b border-zinc-200 px-5 py-4 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2
                        class="font-semibold text-zinc-900 dark:text-white"
                    >
                        ប្រវត្តិសំណើកែប្រែ
                    </h2>

                    <p
                        class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
                    >
                        រកឃើញ
                        {{ number_format($this->recentRequests->count()) }}
                        សំណើ
                    </p>
                </div>

                <div class="w-full sm:w-52">
                    <flux:select wire:model.live="statusFilter">
                        <flux:select.option value="all">
                            ស្ថានភាពទាំងអស់
                        </flux:select.option>

                        <flux:select.option value="pending">
                            កំពុងរង់ចាំ
                        </flux:select.option>

                        <flux:select.option value="approved">
                            បានអនុម័ត
                        </flux:select.option>

                        <flux:select.option value="rejected">
                            បានបដិសេធ
                        </flux:select.option>
                    </flux:select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table
                    class="w-full min-w-[1050px] text-left text-sm"
                >
                    <thead
                        class="bg-zinc-50 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                    >
                        <tr>
                            <th class="px-5 py-3.5 font-medium">
                                កាលបរិច្ឆេទ
                            </th>

                            <th class="px-5 py-3.5 font-medium">
                                ម៉ោងបច្ចុប្បន្ន
                            </th>

                            <th class="px-5 py-3.5 font-medium">
                                ម៉ោងដែលបានស្នើ
                            </th>

                            <th class="px-5 py-3.5 font-medium">
                                មូលហេតុ
                            </th>

                            <th class="px-5 py-3.5 font-medium">
                                ស្ថានភាព
                            </th>

                            <th class="px-5 py-3.5 font-medium">
                                អ្នកពិនិត្យ
                            </th>
                        </tr>
                    </thead>

                    <tbody
                        class="divide-y divide-zinc-200 dark:divide-zinc-700"
                    >
                        @forelse ($this->recentRequests as $request)
                            @php
                                $statusMeta = match ($request->status) {
                                    'approved' => [
                                        'label' => 'បានអនុម័ត',
                                        'color' => 'green',
                                    ],

                                    'rejected' => [
                                        'label' => 'បានបដិសេធ',
                                        'color' => 'red',
                                    ],

                                    default => [
                                        'label' => 'កំពុងរង់ចាំ',
                                        'color' => 'amber',
                                    ],
                                };
                            @endphp

                            <tr
                                wire:key="correction-{{ $request->id }}"
                                class="align-top hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                            >
                                <td class="whitespace-nowrap px-5 py-4">
                                    <div
                                        class="font-medium text-zinc-900 dark:text-white"
                                    >
                                        {{
                                            $request->attendance
                                                ?->work_date
                                                ?->format('d/m/Y')
                                            ?? '—'
                                        }}
                                    </div>

                                    <div
                                        class="mt-1 text-xs text-zinc-500"
                                    >
                                        ស្នើនៅ
                                        {{
                                            $request->created_at
                                                ?->format('d/m/Y H:i')
                                            ?? '—'
                                        }}
                                    </div>
                                </td>

                                <td
                                    class="whitespace-nowrap px-5 py-4 text-zinc-700 dark:text-zinc-300"
                                >
                                    <div>
                                        ចូល៖
                                        {{
                                            $request->attendance
                                                ?->check_in_at
                                                ?->format('H:i')
                                            ?? '—'
                                        }}
                                    </div>

                                    <div class="mt-1">
                                        ចេញ៖
                                        {{
                                            $request->attendance
                                                ?->check_out_at
                                                ?->format('H:i')
                                            ?? '—'
                                        }}
                                    </div>
                                </td>

                                <td
                                    class="whitespace-nowrap px-5 py-4 text-indigo-700 dark:text-indigo-300"
                                >
                                    <div>
                                        ចូល៖
                                        {{
                                            $request->requested_check_in
                                                ?->format('d/m/Y H:i')
                                            ?? 'មិនបានកែ'
                                        }}
                                    </div>

                                    <div class="mt-1">
                                        ចេញ៖
                                        {{
                                            $request->requested_check_out
                                                ?->format('d/m/Y H:i')
                                            ?? 'មិនបានកែ'
                                        }}
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <p
                                        class="max-w-72 whitespace-pre-line text-zinc-700 dark:text-zinc-300"
                                    >
                                        {{ $request->reason }}
                                    </p>

                                    @if ($request->review_note)
                                        <p
                                            class="mt-2 max-w-72 text-xs text-blue-700 dark:text-blue-300"
                                        >
                                            ចំណាំ៖
                                            {{ $request->review_note }}
                                        </p>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <flux:badge
                                        size="sm"
                                        :color="$statusMeta['color']"
                                    >
                                        {{ $statusMeta['label'] }}
                                    </flux:badge>
                                </td>

                                <td
                                    class="px-5 py-4 text-zinc-700 dark:text-zinc-300"
                                >
                                    {{
                                        $request->reviewedBy?->name
                                        ?? 'មិនទាន់ពិនិត្យ'
                                    }}

                                    @if ($request->reviewed_at)
                                        <div
                                            class="mt-1 text-xs text-zinc-500"
                                        >
                                            {{
                                                $request->reviewed_at
                                                    ->format('d/m/Y H:i')
                                            }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="6"
                                    class="px-5 py-14 text-center"
                                >
                                    <div
                                        class="font-medium text-zinc-700 dark:text-zinc-200"
                                    >
                                        មិនទាន់មានសំណើកែប្រែវត្តមាន
                                    </div>

                                    <p
                                        class="mt-2 text-sm text-zinc-500 dark:text-zinc-400"
                                    >
                                        សំណើដែលអ្នកផ្ញើនឹងបង្ហាញនៅទីនេះ។
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>