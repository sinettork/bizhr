<?php

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Company;
use Flux\Flux;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('ពិនិត្យសំណើកែវត្តមាន')] class extends Component
{
    public int $companyId = 0;

    public string $search = '';

    public string $statusFilter = 'pending';

    public string $dateFrom = '';

    public string $dateTo = '';

    public ?int $selectedCorrectionId = null;

    public string $reviewNote = '';

    public function mount(): mixed
    {
        $company = Company::query()->first();

        if (! $company) {
            return $this->redirectRoute(
                'company.settings',
                navigate: true
            );
        }

        $this->companyId = $company->id;

        return null;
    }

    #[Computed]
    public function corrections()
    {
        return $this->baseCorrectionQuery()
            ->with([
                'employee.branch:id,name',
                'employee.department:id,name',
                'attendance',
                'reviewedBy:id,name',
            ])
            ->when(
                filled($this->search),
                function (Builder $query): void {
                    $search = trim($this->search);

                    $query->whereHas(
                        'employee',
                        function (
                            Builder $employeeQuery
                        ) use ($search): void {
                            $employeeQuery->where(
                                function (
                                    Builder $nameQuery
                                ) use ($search): void {
                                    $nameQuery
                                        ->where(
                                            'employee_code',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'full_name_en',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'full_name_km',
                                            'like',
                                            "%{$search}%"
                                        );
                                }
                            );
                        }
                    );
                }
            )
            ->when(
                in_array(
                    $this->statusFilter,
                    [
                        'pending',
                        'approved',
                        'rejected',
                    ],
                    true
                ),
                fn (Builder $query) => $query->where(
                    'status',
                    $this->statusFilter
                )
            )
            ->when(
                filled($this->dateFrom),
                fn (Builder $query) => $query->whereHas(
                    'attendance',
                    fn (
                        Builder $attendanceQuery
                    ) => $attendanceQuery->whereDate(
                        'work_date',
                        '>=',
                        $this->dateFrom
                    )
                )
            )
            ->when(
                filled($this->dateTo),
                fn (Builder $query) => $query->whereHas(
                    'attendance',
                    fn (
                        Builder $attendanceQuery
                    ) => $attendanceQuery->whereDate(
                        'work_date',
                        '<=',
                        $this->dateTo
                    )
                )
            )
            ->orderByRaw(
                "CASE WHEN status = 'pending' THEN 0 ELSE 1 END"
            )
            ->latest('id')
            ->limit(100)
            ->get();
    }

    #[Computed]
    public function statistics(): array
    {
        $query = $this->baseCorrectionQuery();

        return [
            'pending' => (clone $query)
                ->where('status', 'pending')
                ->count(),

            'approved_today' => (clone $query)
                ->where('status', 'approved')
                ->whereDate('reviewed_at', today())
                ->count(),

            'rejected_today' => (clone $query)
                ->where('status', 'rejected')
                ->whereDate('reviewed_at', today())
                ->count(),

            'total' => (clone $query)->count(),
        ];
    }

    #[Computed]
    public function selectedCorrection(): ?AttendanceCorrection
    {
        if ($this->selectedCorrectionId === null) {
            return null;
        }

        return $this->baseCorrectionQuery()
            ->with([
                'employee.branch:id,name',
                'employee.department:id,name',
                'attendance',
                'reviewedBy:id,name',
            ])
            ->find($this->selectedCorrectionId);
    }

    public function updatedSearch(): void
    {
        unset($this->corrections);
    }

    public function updatedStatusFilter(): void
    {
        unset($this->corrections);
    }

    public function updatedDateFrom(): void
    {
        unset($this->corrections);
    }

    public function updatedDateTo(): void
    {
        unset($this->corrections);
    }

    public function resetFilters(): void
    {
        $this->reset([
            'search',
            'dateFrom',
            'dateTo',
        ]);

        $this->statusFilter = 'pending';

        unset($this->corrections);
    }

    public function openReview(
        int $correctionId
    ): void {
        $this->authorizeReview();

        $correction = $this->baseCorrectionQuery()
            ->findOrFail($correctionId);

        $this->selectedCorrectionId =
            $correction->id;

        $this->reviewNote =
            (string) ($correction->review_note ?? '');

        $this->resetValidation();

        unset($this->selectedCorrection);
    }

    public function closeReview(): void
    {
        $this->reset([
            'selectedCorrectionId',
            'reviewNote',
        ]);

        $this->resetValidation();

        unset($this->selectedCorrection);
    }

    public function approveSelected(): void
    {
        $this->authorizeReview();

        $this->validateReview(false);

        DB::transaction(function (): void {
            $correction = $this->baseCorrectionQuery()
                ->lockForUpdate()
                ->with('attendance')
                ->findOrFail(
                    $this->selectedCorrectionId
                );

            if ($correction->status !== 'pending') {
                throw ValidationException::withMessages([
                    'reviewNote' =>
                        'សំណើនេះត្រូវបានពិនិត្យរួចហើយ។',
                ]);
            }

            $attendance = Attendance::query()
                ->lockForUpdate()
                ->findOrFail(
                    $correction->attendance_id
                );

            $checkIn =
                $correction->requested_check_in?->copy()
                ?? $attendance->check_in_at?->copy();

            $checkOut =
                $correction->requested_check_out?->copy()
                ?? $attendance->check_out_at?->copy();

            if (
                $checkIn &&
                $checkOut &&
                $checkOut->lessThanOrEqualTo(
                    $checkIn
                )
            ) {
                throw ValidationException::withMessages([
                    'reviewNote' =>
                        'ម៉ោងចេញត្រូវតែនៅក្រោយម៉ោងចូល។',
                ]);
            }

            $scheduledStart =
                $this->scheduleDateTime(
                    $attendance,
                    $attendance->scheduled_start
                );

            $scheduledEnd =
                $this->scheduleDateTime(
                    $attendance,
                    $attendance->scheduled_end
                );

            /*
            |--------------------------------------------------------------------------
            | Overnight shift
            |--------------------------------------------------------------------------
            */

            if (
                $scheduledStart &&
                $scheduledEnd &&
                $scheduledEnd->lessThanOrEqualTo(
                    $scheduledStart
                )
            ) {
                $scheduledEnd->addDay();
            }

            $lateMinutes = 0;

            if (
                $checkIn &&
                $scheduledStart &&
                $checkIn->greaterThan(
                    $scheduledStart
                )
            ) {
                $lateMinutes = (int) $scheduledStart
                    ->diffInMinutes($checkIn);
            }

            $earlyLeaveMinutes = 0;

            if (
                $checkOut &&
                $scheduledEnd &&
                $checkOut->lessThan(
                    $scheduledEnd
                )
            ) {
                $earlyLeaveMinutes =
                    (int) $checkOut->diffInMinutes(
                        $scheduledEnd
                    );
            }

            $workedMinutes =
                ($checkIn && $checkOut)
                    ? (int) $checkIn->diffInMinutes(
                        $checkOut
                    )
                    : 0;

            $scheduledMinutes =
                ($scheduledStart && $scheduledEnd)
                    ? (int) $scheduledStart
                        ->diffInMinutes(
                            $scheduledEnd
                        )
                    : 0;

            $overtimeMinutes =
                $scheduledMinutes > 0
                    ? max(
                        0,
                        $workedMinutes -
                            $scheduledMinutes
                    )
                    : 0;

            $attendanceStatus = match (true) {
                ! $checkIn && ! $checkOut =>
                    'absent',

                $checkIn && $lateMinutes > 0 =>
                    'late',

                $checkIn !== null =>
                    'present',

                default =>
                    $attendance->status,
            };

            /*
             * saveQuietly prevents the Attendance saving
             * event from recalculating these values again.
             */
            $attendance->fill([
                'check_in_at' =>
                    $checkIn,

                'check_out_at' =>
                    $checkOut,

                'late_minutes' =>
                    $lateMinutes,

                'early_leave_minutes' =>
                    $earlyLeaveMinutes,

                'worked_minutes' =>
                    $workedMinutes,

                'overtime_minutes' =>
                    $overtimeMinutes,

                'status' =>
                    $attendanceStatus,

                'approved_by' =>
                    auth()->id(),
            ]);

            $attendance->saveQuietly();

            $correction->update([
                'status' =>
                    'approved',

                'reviewed_by' =>
                    auth()->id(),

                'reviewed_at' =>
                    now(),

                'review_note' =>
                    filled($this->reviewNote)
                        ? trim($this->reviewNote)
                        : null,
            ]);
        });

        Flux::toast(
            variant: 'success',
            text: 'បានអនុម័តសំណើកែវត្តមានដោយជោគជ័យ។'
        );

        $this->finishReview();
    }

    public function rejectSelected(): void
    {
        $this->authorizeReview();

        $this->validateReview(true);

        DB::transaction(function (): void {
            $correction = $this->baseCorrectionQuery()
                ->lockForUpdate()
                ->findOrFail(
                    $this->selectedCorrectionId
                );

            if ($correction->status !== 'pending') {
                throw ValidationException::withMessages([
                    'reviewNote' =>
                        'សំណើនេះត្រូវបានពិនិត្យរួចហើយ។',
                ]);
            }

            $correction->update([
                'status' =>
                    'rejected',

                'reviewed_by' =>
                    auth()->id(),

                'reviewed_at' =>
                    now(),

                'review_note' =>
                    trim($this->reviewNote),
            ]);
        });

        Flux::toast(
            variant: 'success',
            text: 'បានបដិសេធសំណើកែវត្តមាន។'
        );

        $this->finishReview();
    }

    private function validateReview(
        bool $noteRequired
    ): void {
        $noteRules = $noteRequired
            ? [
                'required',
                'string',
                'min:3',
                'max:1000',
            ]
            : [
                'nullable',
                'string',
                'max:1000',
            ];

        $this->validate(
            [
                'selectedCorrectionId' => [
                    'required',
                    'integer',
                    'exists:attendance_corrections,id',
                ],

                'reviewNote' => $noteRules,
            ],
            [
                'selectedCorrectionId.required' =>
                    'មិនមានសំណើដែលបានជ្រើសរើស។',

                'selectedCorrectionId.exists' =>
                    'សំណើនេះមិនមានក្នុងប្រព័ន្ធទេ។',

                'reviewNote.required' =>
                    'សូមបញ្ចូលមូលហេតុនៃការបដិសេធ។',

                'reviewNote.min' =>
                    'មូលហេតុត្រូវមានយ៉ាងតិច ៣ តួអក្សរ។',

                'reviewNote.max' =>
                    'ចំណាំមិនអាចលើសពី ១០០០ តួអក្សរ។',
            ]
        );
    }

    private function finishReview(): void
    {
        $this->closeReview();

        unset(
            $this->corrections,
            $this->statistics
        );
    }

    private function baseCorrectionQuery(): Builder
    {
        return AttendanceCorrection::query()
            ->whereHas(
                'employee',
                fn (Builder $query) =>
                    $query->where(
                        'company_id',
                        $this->companyId
                    )
            );
    }

    private function scheduleDateTime(
        Attendance $attendance,
        mixed $time
    ): ?Carbon {
        if (blank($time)) {
            return null;
        }

        $timeValue = substr(
            (string) $time,
            0,
            8
        );

        return Carbon::parse(
            $attendance->work_date
        )
            ->startOfDay()
            ->setTimeFromTimeString(
                $timeValue
            );
    }

    private function authorizeReview(): void
    {
        abort_unless(
            auth()->user()?->can(
                'attendance.approve'
            ),
            403
        );
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
                ពិនិត្យសំណើកែវត្តមាន
            </h1>

            <p
                class="mt-1 text-zinc-600 dark:text-zinc-300"
            >
                ពិនិត្យ អនុម័ត ឬបដិសេធសំណើកែម៉ោងចូល
                និងម៉ោងចេញរបស់បុគ្គលិក។
            </p>
        </div>

        @can('attendance.report')
            <flux:button
                type="button"
                variant="ghost"
                icon="chart-bar"
                :href="route(
                    'attendance.reports.index'
                )"
                wire:navigate
            >
                របាយការណ៍វត្តមាន
            </flux:button>
        @endcan
    </div>

    {{-- Compact statistics --}}
    <div
        class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4"
    >
        <div
            class="rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p
                class="text-sm text-zinc-500 dark:text-zinc-400"
            >
                កំពុងរង់ចាំ
            </p>

            <p
                class="mt-1 text-2xl font-semibold text-amber-600"
            >
                {{
                    number_format(
                        $this->statistics['pending']
                    )
                }}
            </p>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p
                class="text-sm text-zinc-500 dark:text-zinc-400"
            >
                អនុម័តថ្ងៃនេះ
            </p>

            <p
                class="mt-1 text-2xl font-semibold text-emerald-600"
            >
                {{
                    number_format(
                        $this->statistics[
                            'approved_today'
                        ]
                    )
                }}
            </p>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p
                class="text-sm text-zinc-500 dark:text-zinc-400"
            >
                បដិសេធថ្ងៃនេះ
            </p>

            <p
                class="mt-1 text-2xl font-semibold text-red-600"
            >
                {{
                    number_format(
                        $this->statistics[
                            'rejected_today'
                        ]
                    )
                }}
            </p>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p
                class="text-sm text-zinc-500 dark:text-zinc-400"
            >
                សំណើសរុប
            </p>

            <p
                class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-white"
            >
                {{
                    number_format(
                        $this->statistics['total']
                    )
                }}
            </p>
        </div>
    </div>

    {{-- Filters and list --}}
    <div
        class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
    >
        {{-- Filters --}}
        <div
            class="border-b border-zinc-200 p-4 dark:border-zinc-700 sm:p-5"
        >
            <div
                class="grid gap-4 md:grid-cols-2 xl:grid-cols-[minmax(260px,2fr)_repeat(3,minmax(150px,1fr))_auto] xl:items-end"
            >
                <flux:input
                    wire:model.live.debounce.350ms="search"
                    icon="magnifying-glass"
                    label="ស្វែងរកបុគ្គលិក"
                    placeholder="ឈ្មោះ ឬលេខកូដបុគ្គលិក..."
                    clearable
                />

                <flux:select
                    wire:model.live="statusFilter"
                    label="ស្ថានភាព"
                >
                    <flux:select.option value="all">
                        ទាំងអស់
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

                <flux:input
                    wire:model.live="dateFrom"
                    type="date"
                    label="ចាប់ពីថ្ងៃ"
                />

                <flux:input
                    wire:model.live="dateTo"
                    type="date"
                    label="ដល់ថ្ងៃ"
                />

                <flux:button
                    type="button"
                    variant="ghost"
                    icon="arrow-path"
                    wire:click="resetFilters"
                    wire:loading.attr="disabled"
                    wire:target="resetFilters"
                    class="w-full xl:w-auto"
                >
                    សម្អាត
                </flux:button>
            </div>
        </div>

        {{-- List heading --}}
        <div
            class="border-b border-zinc-200 px-5 py-4 dark:border-zinc-700"
        >
            <h2
                class="font-semibold text-zinc-900 dark:text-white"
            >
                បញ្ជីសំណើកែវត្តមាន
            </h2>

            <p
                class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
            >
                រកឃើញ
                {{
                    number_format(
                        $this->corrections->count()
                    )
                }}
                សំណើ
            </p>
        </div>

        {{-- Desktop table --}}
        <div
            class="hidden overflow-x-auto lg:block"
        >
            <table
                class="w-full min-w-[1100px] text-left text-sm"
            >
                <thead
                    class="bg-zinc-50 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                >
                    <tr>
                        <th
                            class="px-5 py-4 font-medium"
                        >
                            បុគ្គលិក
                        </th>

                        <th
                            class="px-5 py-4 font-medium"
                        >
                            កាលបរិច្ឆេទ
                        </th>

                        <th
                            class="px-5 py-4 font-medium"
                        >
                            ម៉ោងបច្ចុប្បន្ន
                        </th>

                        <th
                            class="px-5 py-4 font-medium"
                        >
                            ម៉ោងដែលបានស្នើ
                        </th>

                        <th
                            class="px-5 py-4 font-medium"
                        >
                            ស្ថានភាព
                        </th>

                        <th
                            class="px-5 py-4 text-right font-medium"
                        >
                            សកម្មភាព
                        </th>
                    </tr>
                </thead>

                <tbody
                    class="divide-y divide-zinc-200 dark:divide-zinc-700"
                >
                    @forelse (
                        $this->corrections
                        as $correction
                    )
                        @php
                            $employeeName =
                                $correction->employee
                                    ?->full_name_km
                                ?: $correction->employee
                                    ?->full_name_en
                                ?: 'មិនមានឈ្មោះ';
                        @endphp

                        <tr
                            wire:key="correction-table-{{ $correction->id }}"
                            class="align-top transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        >
                            <td class="px-5 py-4">
                                <div
                                    class="font-medium text-zinc-900 dark:text-white"
                                >
                                    {{ $employeeName }}
                                </div>

                                <div
                                    class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                >
                                    {{
                                        $correction
                                            ->employee
                                            ?->employee_code
                                        ?? '—'
                                    }}

                                    @if (
                                        $correction
                                            ->employee
                                            ?->department
                                    )
                                        ·
                                        {{
                                            $correction
                                                ->employee
                                                ->department
                                                ->name
                                        }}
                                    @endif
                                </div>

                                @if (
                                    $correction
                                        ->employee
                                        ?->branch
                                )
                                    <div
                                        class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                    >
                                        {{
                                            $correction
                                                ->employee
                                                ->branch
                                                ->name
                                        }}
                                    </div>
                                @endif
                            </td>

                            <td
                                class="px-5 py-4 text-zinc-700 dark:text-zinc-300"
                            >
                                <div class="font-medium">
                                    {{
                                        $correction
                                            ->attendance
                                            ?->work_date
                                            ?->format(
                                                'd/m/Y'
                                            )
                                        ?? '—'
                                    }}
                                </div>

                                <div
                                    class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                >
                                    ស្នើនៅ
                                    {{
                                        $correction
                                            ->created_at
                                            ?->format(
                                                'd/m/Y H:i'
                                            )
                                    }}
                                </div>
                            </td>

                            <td
                                class="px-5 py-4 text-zinc-700 dark:text-zinc-300"
                            >
                                <div>
                                    ចូល៖
                                    {{
                                        $correction
                                            ->attendance
                                            ?->check_in_at
                                            ?->format('H:i')
                                        ?? '—'
                                    }}
                                </div>

                                <div class="mt-1">
                                    ចេញ៖
                                    {{
                                        $correction
                                            ->attendance
                                            ?->check_out_at
                                            ?->format('H:i')
                                        ?? '—'
                                    }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div
                                    class="font-medium text-indigo-700 dark:text-indigo-300"
                                >
                                    ចូល៖
                                    {{
                                        $correction
                                            ->requested_check_in
                                            ?->format('H:i')
                                        ?? 'មិនកែ'
                                    }}
                                </div>

                                <div
                                    class="mt-1 font-medium text-indigo-700 dark:text-indigo-300"
                                >
                                    ចេញ៖
                                    {{
                                        $correction
                                            ->requested_check_out
                                            ?->format('H:i')
                                        ?? 'មិនកែ'
                                    }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if (
                                    $correction->status
                                    === 'approved'
                                )
                                    <flux:badge
                                        size="sm"
                                        color="green"
                                    >
                                        បានអនុម័ត
                                    </flux:badge>
                                @elseif (
                                    $correction->status
                                    === 'rejected'
                                )
                                    <flux:badge
                                        size="sm"
                                        color="red"
                                    >
                                        បានបដិសេធ
                                    </flux:badge>
                                @else
                                    <flux:badge
                                        size="sm"
                                        color="amber"
                                    >
                                        កំពុងរង់ចាំ
                                    </flux:badge>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <div
                                    class="flex justify-end"
                                >
                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="ghost"
                                        icon="eye"
                                        square
                                        wire:click="openReview({{ $correction->id }})"
                                        title="មើល និងពិនិត្យ"
                                        aria-label="មើល និងពិនិត្យ"
                                    />
                                </div>
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
                                    មិនមានសំណើកែវត្តមាន
                                </div>

                                <div
                                    class="mt-2 text-sm text-zinc-500 dark:text-zinc-400"
                                >
                                    មិនមានសំណើដែលត្រូវនឹងតម្រងបច្ចុប្បន្នទេ។
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div
            class="divide-y divide-zinc-200 dark:divide-zinc-700 lg:hidden"
        >
            @forelse (
                $this->corrections
                as $correction
            )
                @php
                    $employeeName =
                        $correction->employee
                            ?->full_name_km
                        ?: $correction->employee
                            ?->full_name_en
                        ?: 'មិនមានឈ្មោះ';
                @endphp

                <div
                    wire:key="correction-card-{{ $correction->id }}"
                    class="space-y-4 p-5"
                >
                    <div
                        class="flex items-start justify-between gap-4"
                    >
                        <div class="min-w-0">
                            <div
                                class="truncate font-medium text-zinc-900 dark:text-white"
                            >
                                {{ $employeeName }}
                            </div>

                            <div
                                class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                            >
                                {{
                                    $correction
                                        ->employee
                                        ?->employee_code
                                    ?? '—'
                                }}

                                ·

                                {{
                                    $correction
                                        ->attendance
                                        ?->work_date
                                        ?->format('d/m/Y')
                                    ?? '—'
                                }}
                            </div>
                        </div>

                        @if (
                            $correction->status
                            === 'approved'
                        )
                            <flux:badge
                                size="sm"
                                color="green"
                            >
                                បានអនុម័ត
                            </flux:badge>
                        @elseif (
                            $correction->status
                            === 'rejected'
                        )
                            <flux:badge
                                size="sm"
                                color="red"
                            >
                                បានបដិសេធ
                            </flux:badge>
                        @else
                            <flux:badge
                                size="sm"
                                color="amber"
                            >
                                កំពុងរង់ចាំ
                            </flux:badge>
                        @endif
                    </div>

                    <div
                        class="grid gap-3 sm:grid-cols-2"
                    >
                        <div
                            class="rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800/60"
                        >
                            <div
                                class="text-xs text-zinc-500 dark:text-zinc-400"
                            >
                                ម៉ោងបច្ចុប្បន្ន
                            </div>

                            <div
                                class="mt-1 text-sm text-zinc-800 dark:text-zinc-200"
                            >
                                ចូល
                                {{
                                    $correction
                                        ->attendance
                                        ?->check_in_at
                                        ?->format('H:i')
                                    ?? '—'
                                }}

                                · ចេញ
                                {{
                                    $correction
                                        ->attendance
                                        ?->check_out_at
                                        ?->format('H:i')
                                    ?? '—'
                                }}
                            </div>
                        </div>

                        <div
                            class="rounded-lg bg-indigo-50 p-3 dark:bg-indigo-950/30"
                        >
                            <div
                                class="text-xs text-indigo-600 dark:text-indigo-400"
                            >
                                ម៉ោងដែលបានស្នើ
                            </div>

                            <div
                                class="mt-1 text-sm text-indigo-800 dark:text-indigo-200"
                            >
                                ចូល
                                {{
                                    $correction
                                        ->requested_check_in
                                        ?->format('H:i')
                                    ?? 'មិនកែ'
                                }}

                                · ចេញ
                                {{
                                    $correction
                                        ->requested_check_out
                                        ?->format('H:i')
                                    ?? 'មិនកែ'
                                }}
                            </div>
                        </div>
                    </div>

                    <div
                        class="flex justify-end border-t border-zinc-100 pt-3 dark:border-zinc-800"
                    >
                        <flux:button
                            type="button"
                            size="sm"
                            variant="ghost"
                            icon="eye"
                            wire:click="openReview({{ $correction->id }})"
                        >
                            {{
                                $correction->status
                                    === 'pending'
                                    ? 'ពិនិត្យសំណើ'
                                    : 'មើលព័ត៌មាន'
                            }}
                        </flux:button>
                    </div>
                </div>
            @empty
                <div
                    class="px-5 py-14 text-center"
                >
                    <div
                        class="font-medium text-zinc-700 dark:text-zinc-200"
                    >
                        មិនមានសំណើកែវត្តមាន
                    </div>

                    <div
                        class="mt-2 text-sm text-zinc-500 dark:text-zinc-400"
                    >
                        មិនមានសំណើដែលត្រូវនឹងតម្រងបច្ចុប្បន្នទេ។
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Review dialog --}}
    @if ($this->selectedCorrection)
        @php
            $selected =
                $this->selectedCorrection;

            $selectedEmployeeName =
                $selected->employee
                    ?->full_name_km
                ?: $selected->employee
                    ?->full_name_en
                ?: 'មិនមានឈ្មោះ';
        @endphp

        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4"
            wire:click.self="closeReview"
            wire:keydown.escape.window="closeReview"
        >
            <div
                class="max-h-[92vh] w-full max-w-3xl overflow-y-auto rounded-xl border border-zinc-200 bg-white shadow-2xl dark:border-zinc-700 dark:bg-zinc-900"
            >
                {{-- Modal heading --}}
                <div
                    class="flex items-start justify-between gap-4 border-b border-zinc-200 p-5 dark:border-zinc-700"
                >
                    <div>
                        <h2
                            class="text-lg font-semibold text-zinc-900 dark:text-white"
                        >
                            ពិនិត្យសំណើកែវត្តមាន
                        </h2>

                        <p
                            class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
                        >
                            {{ $selectedEmployeeName }}

                            ·

                            {{
                                $selected
                                    ->employee
                                    ?->employee_code
                                ?? '—'
                            }}

                            ·

                            {{
                                $selected
                                    ->attendance
                                    ?->work_date
                                    ?->format('d/m/Y')
                                ?? '—'
                            }}
                        </p>
                    </div>

                    <flux:button
                        type="button"
                        variant="ghost"
                        icon="x-mark"
                        square
                        wire:click="closeReview"
                        title="បិទ"
                        aria-label="បិទ"
                    />
                </div>

                {{-- Modal content --}}
                <div class="space-y-5 p-5">
                    <div
                        class="grid gap-4 md:grid-cols-2"
                    >
                        {{-- Check-in --}}
                        <div
                            class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700"
                        >
                            <h3
                                class="text-sm font-medium text-zinc-700 dark:text-zinc-300"
                            >
                                ម៉ោងចូល
                            </h3>

                            <div
                                class="mt-3 grid grid-cols-2 gap-3"
                            >
                                <div>
                                    <div
                                        class="text-xs text-zinc-500 dark:text-zinc-400"
                                    >
                                        បច្ចុប្បន្ន
                                    </div>

                                    <div
                                        class="mt-1 font-semibold text-zinc-900 dark:text-white"
                                    >
                                        {{
                                            $selected
                                                ->attendance
                                                ?->check_in_at
                                                ?->format(
                                                    'd/m/Y H:i'
                                                )
                                            ?? 'មិនមាន'
                                        }}
                                    </div>
                                </div>

                                <div>
                                    <div
                                        class="text-xs text-indigo-600 dark:text-indigo-400"
                                    >
                                        បានស្នើ
                                    </div>

                                    <div
                                        class="mt-1 font-semibold text-indigo-700 dark:text-indigo-300"
                                    >
                                        {{
                                            $selected
                                                ->requested_check_in
                                                ?->format(
                                                    'd/m/Y H:i'
                                                )
                                            ?? 'មិនបានកែ'
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Check-out --}}
                        <div
                            class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700"
                        >
                            <h3
                                class="text-sm font-medium text-zinc-700 dark:text-zinc-300"
                            >
                                ម៉ោងចេញ
                            </h3>

                            <div
                                class="mt-3 grid grid-cols-2 gap-3"
                            >
                                <div>
                                    <div
                                        class="text-xs text-zinc-500 dark:text-zinc-400"
                                    >
                                        បច្ចុប្បន្ន
                                    </div>

                                    <div
                                        class="mt-1 font-semibold text-zinc-900 dark:text-white"
                                    >
                                        {{
                                            $selected
                                                ->attendance
                                                ?->check_out_at
                                                ?->format(
                                                    'd/m/Y H:i'
                                                )
                                            ?? 'មិនមាន'
                                        }}
                                    </div>
                                </div>

                                <div>
                                    <div
                                        class="text-xs text-indigo-600 dark:text-indigo-400"
                                    >
                                        បានស្នើ
                                    </div>

                                    <div
                                        class="mt-1 font-semibold text-indigo-700 dark:text-indigo-300"
                                    >
                                        {{
                                            $selected
                                                ->requested_check_out
                                                ?->format(
                                                    'd/m/Y H:i'
                                                )
                                            ?? 'មិនបានកែ'
                                        }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Employee reason --}}
                    <div>
                        <div
                            class="text-sm font-medium text-zinc-700 dark:text-zinc-300"
                        >
                            មូលហេតុរបស់បុគ្គលិក
                        </div>

                        <div
                            class="mt-2 whitespace-pre-line rounded-xl bg-zinc-50 p-4 text-sm leading-6 text-zinc-700 dark:bg-zinc-800/60 dark:text-zinc-300"
                        >
                            {{ $selected->reason }}
                        </div>
                    </div>

                    {{-- Review note --}}
                    <flux:textarea
                        wire:model="reviewNote"
                        label="ចំណាំរបស់អ្នកពិនិត្យ"
                        rows="3"
                        maxlength="1000"
                        placeholder="បញ្ចូលចំណាំ ឬមូលហេតុនៃការបដិសេធ..."
                        :disabled="$selected->status !== 'pending'"
                    />

                    {{-- Existing review information --}}
                    @if (
                        $selected->status
                        !== 'pending'
                    )
                        <div
                            class="rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 dark:border-blue-900 dark:bg-blue-950/30 dark:text-blue-300"
                        >
                            សំណើនេះត្រូវបានពិនិត្យរួចហើយដោយ

                            {{
                                $selected
                                    ->reviewedBy
                                    ?->name
                                ?? 'អ្នកប្រើប្រាស់'
                            }}

                            នៅ

                            {{
                                $selected
                                    ->reviewed_at
                                    ?->format(
                                        'd/m/Y H:i'
                                    )
                                ?? '—'
                            }}។
                        </div>
                    @endif
                </div>

                {{-- Modal actions --}}
                <div
                    class="flex flex-col-reverse gap-3 border-t border-zinc-200 p-5 dark:border-zinc-700 sm:flex-row sm:justify-end"
                >
                    <flux:button
                        type="button"
                        variant="ghost"
                        wire:click="closeReview"
                    >
                        បិទ
                    </flux:button>

                    @if (
                        $selected->status
                        === 'pending'
                    )
                        <flux:button
                            type="button"
                            variant="danger"
                            icon="x-circle"
                            wire:click="rejectSelected"
                            wire:loading.attr="disabled"
                            wire:target="rejectSelected"
                        >
                            <span
                                wire:loading.remove
                                wire:target="rejectSelected"
                            >
                                បដិសេធ
                            </span>

                            <span
                                wire:loading
                                wire:target="rejectSelected"
                            >
                                កំពុងរក្សាទុក...
                            </span>
                        </flux:button>

                        <flux:button
                            type="button"
                            variant="primary"
                            icon="check-circle"
                            wire:click="approveSelected"
                            wire:loading.attr="disabled"
                            wire:target="approveSelected"
                        >
                            <span
                                wire:loading.remove
                                wire:target="approveSelected"
                            >
                                អនុម័ត
                            </span>

                            <span
                                wire:loading
                                wire:target="approveSelected"
                            >
                                កំពុងរក្សាទុក...
                            </span>
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>