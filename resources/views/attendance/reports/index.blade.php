<x-layouts::app>
    @php
        $formatMinutes = static function ($minutes): string {
            $minutes = max(0, (int) $minutes);

            if ($minutes === 0) {
                return '០ នាទី';
            }

            $hours = intdiv($minutes, 60);
            $remainingMinutes = $minutes % 60;

            if ($hours > 0 && $remainingMinutes > 0) {
                return $hours.' ម៉ោង '.$remainingMinutes.' នាទី';
            }

            if ($hours > 0) {
                return $hours.' ម៉ោង';
            }

            return $remainingMinutes.' នាទី';
        };

        $formatDate = static function ($value): string {
            if (blank($value)) {
                return '—';
            }

            return \Illuminate\Support\Carbon::parse($value)
                ->format('d/m/Y');
        };

        $formatTime = static function ($value): string {
            if (blank($value)) {
                return '—';
            }

            return \Illuminate\Support\Carbon::parse($value)
                ->format('H:i');
        };

        $statusOptions = [
            'present' => [
                'label' => 'មានវត្តមាន',
                'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300',
            ],

            'late' => [
                'label' => 'មកយឺត',
                'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-300',
            ],

            'absent' => [
                'label' => 'អវត្តមាន',
                'class' => 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300',
            ],

            'on_leave' => [
                'label' => 'ឈប់សម្រាក',
                'class' => 'bg-purple-100 text-purple-700 dark:bg-purple-950/50 dark:text-purple-300',
            ],

            'half_day' => [
                'label' => 'ពាក់កណ្ដាលថ្ងៃ',
                'class' => 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-300',
            ],

            'holiday' => [
                'label' => 'ថ្ងៃឈប់សម្រាក',
                'class' => 'bg-violet-100 text-violet-700 dark:bg-violet-950/50 dark:text-violet-300',
            ],

            'rest_day' => [
                'label' => 'ថ្ងៃសម្រាក',
                'class' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
            ],

            'remote_work' => [
                'label' => 'ធ្វើការពីចម្ងាយ',
                'class' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-950/50 dark:text-cyan-300',
            ],

            'business_trip' => [
                'label' => 'បេសកកម្ម',
                'class' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950/50 dark:text-indigo-300',
            ],
        ];
    @endphp

    <div class="w-full space-y-5 p-4 sm:p-6">

        {{-- Page heading --}}
        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
        >
            <div>
                <h1
                    class="text-2xl font-semibold text-zinc-900 dark:text-white"
                >
                    របាយការណ៍វត្តមាន
                </h1>

                <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                    តាមដានវត្តមាន ការមកយឺត អវត្តមាន
                    និងម៉ោងធ្វើការរបស់បុគ្គលិក។
                </p>
            </div>

            @if (Route::has('attendance.corrections.review'))
                <flux:button
                    type="button"
                    variant="ghost"
                    icon="clipboard-document-check"
                    :href="route('attendance.corrections.review')"
                    wire:navigate
                >
                    ពិនិត្យសំណើកែវត្តមាន
                </flux:button>
            @endif
        </div>

        {{-- Statistics --}}
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div
                class="rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    កំណត់ត្រាសរុប
                </p>

                <p
                    class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-white"
                >
                    {{ number_format($statistics['total'] ?? 0) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    មានវត្តមាន
                </p>

                <div class="mt-1 flex items-baseline gap-2">
                    <p class="text-2xl font-semibold text-emerald-600">
                        {{ number_format($statistics['present'] ?? 0) }}
                    </p>

                    <span class="text-xs text-zinc-500 dark:text-zinc-400">
                        {{
                            number_format(
                                $statistics['attendance_rate'] ?? 0,
                                1
                            )
                        }}%
                    </span>
                </div>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    មកយឺត
                </p>

                <p class="mt-1 text-2xl font-semibold text-amber-600">
                    {{ number_format($statistics['late'] ?? 0) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    អវត្តមាន
                </p>

                <p class="mt-1 text-2xl font-semibold text-red-600">
                    {{ number_format($statistics['absent'] ?? 0) }}
                </p>
            </div>
        </div>

        {{-- Time totals --}}
        <div
            class="grid overflow-hidden rounded-xl border border-zinc-200 bg-white sm:grid-cols-2 xl:grid-cols-4 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <div
                class="border-b border-zinc-200 px-4 py-3 sm:border-r xl:border-b-0 dark:border-zinc-700"
            >
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    ម៉ោងធ្វើការសរុប
                </p>

                <p class="mt-1 font-semibold text-zinc-900 dark:text-white">
                    {{
                        $formatMinutes(
                            $statistics['worked_minutes'] ?? 0
                        )
                    }}
                </p>
            </div>

            <div
                class="border-b border-zinc-200 px-4 py-3 xl:border-b-0 xl:border-r dark:border-zinc-700"
            >
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    ម៉ោងបន្ថែម
                </p>

                <p class="mt-1 font-semibold text-blue-600">
                    {{
                        $formatMinutes(
                            $statistics['overtime_minutes'] ?? 0
                        )
                    }}
                </p>
            </div>

            <div
                class="border-b border-zinc-200 px-4 py-3 sm:border-b-0 sm:border-r dark:border-zinc-700"
            >
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    ពេលមកយឺត
                </p>

                <p class="mt-1 font-semibold text-amber-600">
                    {{
                        $formatMinutes(
                            $statistics['late_minutes'] ?? 0
                        )
                    }}
                </p>
            </div>

            <div class="px-4 py-3">
                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                    ចេញមុន
                </p>

                <p class="mt-1 font-semibold text-red-600">
                    {{
                        $formatMinutes(
                            $statistics['early_leave_minutes'] ?? 0
                        )
                    }}
                </p>
            </div>
        </div>

        {{-- Report card --}}
        <div
            class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
        >
            {{-- Filters --}}
            <form
                method="GET"
                action="{{ route('attendance.reports.index') }}"
                class="border-b border-zinc-200 p-4 dark:border-zinc-700 sm:p-5"
            >
                <div
                    class="grid gap-4 md:grid-cols-2 xl:grid-cols-4"
                >
                    <div class="md:col-span-2">
                        <label
                            for="search"
                            class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                        >
                            ស្វែងរក
                        </label>

                        <input
                            id="search"
                            name="search"
                            type="text"
                            value="{{ $search }}"
                            placeholder="ឈ្មោះ លេខកូដ ឬលេខទូរស័ព្ទ..."
                            class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-zinc-500 focus:ring-2 focus:ring-zinc-500/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                        >
                    </div>

                    <div>
                        <label
                            for="date_from"
                            class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                        >
                            ចាប់ពីថ្ងៃ
                        </label>

                        <input
                            id="date_from"
                            name="date_from"
                            type="date"
                            value="{{ $dateFrom }}"
                            class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                        >
                    </div>

                    <div>
                        <label
                            for="date_to"
                            class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                        >
                            ដល់ថ្ងៃ
                        </label>

                        <input
                            id="date_to"
                            name="date_to"
                            type="date"
                            value="{{ $dateTo }}"
                            class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                        >
                    </div>

                    <div>
                        <label
                            for="branch_id"
                            class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                        >
                            សាខា
                        </label>

                        <select
                            id="branch_id"
                            name="branch_id"
                            class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                        >
                            <option value="">
                                សាខាទាំងអស់
                            </option>

                            @foreach ($branches as $branch)
                                <option
                                    value="{{ $branch->id }}"
                                    @selected(
                                        (string) $branchId
                                        ===
                                        (string) $branch->id
                                    )
                                >
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label
                            for="employee_id"
                            class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                        >
                            បុគ្គលិក
                        </label>

                        <select
                            id="employee_id"
                            name="employee_id"
                            class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                        >
                            <option value="">
                                បុគ្គលិកទាំងអស់
                            </option>

                            @foreach ($employees as $employee)
                                @php
                                    $employeeName =
                                        $employee->full_name_km
                                        ?: $employee->full_name_en
                                        ?: trim(
                                            $employee->first_name
                                            .' '
                                            .$employee->last_name
                                        );
                                @endphp

                                <option
                                    value="{{ $employee->id }}"
                                    @selected(
                                        (string) $employeeId
                                        ===
                                        (string) $employee->id
                                    )
                                >
                                    {{ $employee->employee_code }}
                                    — {{ $employeeName }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label
                            for="status"
                            class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                        >
                            ស្ថានភាព
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                        >
                            <option value="">
                                ស្ថានភាពទាំងអស់
                            </option>

                            @foreach ($statusOptions as $value => $option)
                                <option
                                    value="{{ $value }}"
                                    @selected($status === $value)
                                >
                                    {{ $option['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label
                            for="per_page"
                            class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                        >
                            ក្នុងមួយទំព័រ
                        </label>

                        <select
                            id="per_page"
                            name="per_page"
                            class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                        >
                            @foreach ([10, 25, 50, 100] as $size)
                                <option
                                    value="{{ $size }}"
                                    @selected((int) $perPage === $size)
                                >
                                    {{ $size }} កំណត់ត្រា
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div
                    class="mt-5 flex flex-col-reverse gap-3 border-t border-zinc-200 pt-4 dark:border-zinc-700 sm:flex-row sm:justify-end"
                >
                    <a
                        href="{{ route('attendance.reports.index') }}"
                        class="inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100 dark:text-zinc-300 dark:hover:bg-zinc-800"
                    >
                        សម្អាតតម្រង
                    </a>

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                    >
                        បង្ហាញរបាយការណ៍
                    </button>
                </div>
            </form>

            {{-- Table heading --}}
            <div
                class="flex flex-col gap-2 border-b border-zinc-200 px-5 py-4 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2
                        class="font-semibold text-zinc-900 dark:text-white"
                    >
                        បញ្ជីវត្តមាន
                    </h2>

                    <p
                        class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                    >
                        {{ $formatDate($dateFrom) }}
                        ដល់
                        {{ $formatDate($dateTo) }}
                    </p>
                </div>

                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    សរុប
                    {{ number_format($attendances->total()) }}
                    កំណត់ត្រា
                </p>
            </div>

            {{-- Desktop table --}}
            <div class="overflow-x-auto">
                <table class="w-full min-w-[1150px] text-left text-sm">
                    <thead
                        class="bg-zinc-50 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                    >
                        <tr>
                            <th class="px-5 py-3.5 font-medium">
                                បុគ្គលិក
                            </th>

                            <th class="px-5 py-3.5 font-medium">
                                កាលបរិច្ឆេទ
                            </th>

                            <th class="px-5 py-3.5 font-medium">
                                ម៉ោងចូល / ចេញ
                            </th>

                            <th class="px-5 py-3.5 font-medium">
                                ស្ថានភាព
                            </th>

                            <th class="px-5 py-3.5 text-right font-medium">
                                មកយឺត
                            </th>

                            <th class="px-5 py-3.5 text-right font-medium">
                                ចេញមុន
                            </th>

                            <th class="px-5 py-3.5 text-right font-medium">
                                ម៉ោងធ្វើការ
                            </th>

                            <th class="px-5 py-3.5 text-right font-medium">
                                ម៉ោងបន្ថែម
                            </th>
                        </tr>
                    </thead>

                    <tbody
                        class="divide-y divide-zinc-200 dark:divide-zinc-700"
                    >
                        @forelse ($attendances as $attendance)
                            @php
                                $attendanceEmployeeName =
                                    $attendance->employee?->full_name_km
                                    ?: $attendance->employee?->full_name_en
                                    ?: trim(
                                        ($attendance->employee?->first_name ?? '')
                                        .' '
                                        .($attendance->employee?->last_name ?? '')
                                    );

                                $statusInfo =
                                    $statusOptions[$attendance->status]
                                    ?? [
                                        'label' => $attendance->status ?: 'មិនបានកំណត់',
                                        'class' => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
                                    ];
                            @endphp

                            <tr
                                class="align-top transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                            >
                                <td class="px-5 py-4">
                                    <div
                                        class="font-medium text-zinc-900 dark:text-white"
                                    >
                                        {{ $attendanceEmployeeName ?: 'មិនមានឈ្មោះ' }}
                                    </div>

                                    <div
                                        class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                    >
                                        {{ $attendance->employee?->employee_code ?? '—' }}

                                        @if ($attendance->employee?->department)
                                            ·
                                            {{ $attendance->employee->department->name }}
                                        @endif
                                    </div>

                                    @if ($attendance->employee?->branch)
                                        <div
                                            class="mt-1 text-xs text-zinc-400 dark:text-zinc-500"
                                        >
                                            {{ $attendance->employee->branch->name }}
                                        </div>
                                    @endif
                                </td>

                                <td
                                    class="whitespace-nowrap px-5 py-4 text-zinc-700 dark:text-zinc-300"
                                >
                                    {{ $formatDate($attendance->work_date) }}
                                </td>

                                <td class="whitespace-nowrap px-5 py-4">
                                    <div
                                        class="font-medium text-zinc-900 dark:text-white"
                                    >
                                        {{ $formatTime($attendance->check_in_at) }}

                                        <span
                                            class="mx-1 text-zinc-300 dark:text-zinc-600"
                                        >
                                            /
                                        </span>

                                        {{ $formatTime($attendance->check_out_at) }}
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <span
                                        class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $statusInfo['class'] }}"
                                    >
                                        {{ $statusInfo['label'] }}
                                    </span>
                                </td>

                                <td
                                    class="whitespace-nowrap px-5 py-4 text-right"
                                >
                                    @if ((int) $attendance->late_minutes > 0)
                                        <span
                                            class="font-medium text-amber-600"
                                        >
                                            {{ number_format($attendance->late_minutes) }}
                                            នាទី
                                        </span>
                                    @else
                                        <span class="text-zinc-400">
                                            —
                                        </span>
                                    @endif
                                </td>

                                <td
                                    class="whitespace-nowrap px-5 py-4 text-right"
                                >
                                    @if ((int) $attendance->early_leave_minutes > 0)
                                        <span class="font-medium text-red-600">
                                            {{ number_format($attendance->early_leave_minutes) }}
                                            នាទី
                                        </span>
                                    @else
                                        <span class="text-zinc-400">
                                            —
                                        </span>
                                    @endif
                                </td>

                                <td
                                    class="whitespace-nowrap px-5 py-4 text-right font-medium text-zinc-900 dark:text-white"
                                >
                                    {{
                                        $formatMinutes(
                                            $attendance->worked_minutes
                                        )
                                    }}
                                </td>

                                <td
                                    class="whitespace-nowrap px-5 py-4 text-right"
                                >
                                    @if ((int) $attendance->overtime_minutes > 0)
                                        <span class="font-medium text-blue-600">
                                            {{
                                                $formatMinutes(
                                                    $attendance->overtime_minutes
                                                )
                                            }}
                                        </span>
                                    @else
                                        <span class="text-zinc-400">
                                            —
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="8"
                                    class="px-5 py-14 text-center"
                                >
                                    <div
                                        class="font-medium text-zinc-700 dark:text-zinc-200"
                                    >
                                        មិនមានទិន្នន័យវត្តមាន
                                    </div>

                                    <p
                                        class="mt-2 text-sm text-zinc-500 dark:text-zinc-400"
                                    >
                                        សូមប្តូរតម្រង ឬជ្រើសរើសកាលបរិច្ឆេទផ្សេង។
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($attendances->hasPages())
                <div
                    class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-700"
                >
                    {{ $attendances->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>