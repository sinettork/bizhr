<x-layouts::app>
    @php
        $minutesToText = static function (
            int $minutes
        ): string {
            if ($minutes <= 0) {
                return '០ នាទី';
            }

            $hours = intdiv($minutes, 60);
            $remainingMinutes = $minutes % 60;

            if ($hours > 0 && $remainingMinutes > 0) {
                return number_format($hours)
                    .' ម៉ោង '
                    .number_format($remainingMinutes)
                    .' នាទី';
            }

            if ($hours > 0) {
                return number_format($hours).' ម៉ោង';
            }

            return number_format($remainingMinutes).' នាទី';
        };

        $statusInformation = static function (
            ?string $attendanceStatus
        ): array {
            return match ($attendanceStatus) {
                'present' => [
                    'label' => 'វត្តមាន',
                    'color' => 'green',
                ],

                'late' => [
                    'label' => 'មកយឺត',
                    'color' => 'amber',
                ],

                'absent' => [
                    'label' => 'អវត្តមាន',
                    'color' => 'red',
                ],

                'on_leave' => [
                    'label' => 'ឈប់សម្រាក',
                    'color' => 'purple',
                ],

                'half_day' => [
                    'label' => 'ពាក់កណ្ដាលថ្ងៃ',
                    'color' => 'blue',
                ],

                'holiday' => [
                    'label' => 'ថ្ងៃឈប់សម្រាក',
                    'color' => 'purple',
                ],

                'rest_day' => [
                    'label' => 'ថ្ងៃសម្រាក',
                    'color' => 'zinc',
                ],

                'remote_work' => [
                    'label' => 'ធ្វើការពីចម្ងាយ',
                    'color' => 'cyan',
                ],

                'business_trip' => [
                    'label' => 'បេសកកម្ម',
                    'color' => 'indigo',
                ],

                default => [
                    'label' => $attendanceStatus ?: 'មិនបានកំណត់',
                    'color' => 'zinc',
                ],
            };
        };
    @endphp

    <div class="w-full space-y-6 p-4 sm:p-6">
        {{-- Header --}}
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
                    តាមដានវត្តមាន ការមកយឺត ម៉ោងធ្វើការ និងម៉ោងបន្ថែម។
                </p>
            </div>

            @can('attendance.approve')
                <flux:button
                    variant="ghost"
                    icon="clipboard-document-check"
                    :href="route('attendance.corrections.review')"
                    wire:navigate
                >
                    ពិនិត្យសំណើកែវត្តមាន
                </flux:button>
            @endcan
        </div>

        {{-- Statistics --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500">
                    កំណត់ត្រាសរុប
                </p>

                <p
                    class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white"
                >
                    {{ number_format($statistics['total']) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500">
                    មានវត្តមាន
                </p>

                <p class="mt-2 text-3xl font-semibold text-green-600">
                    {{ number_format($statistics['present']) }}
                </p>

                <p class="mt-1 text-xs text-zinc-500">
                    អត្រា
                    {{ number_format($statistics['attendance_rate'], 1) }}%
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500">
                    មកយឺត
                </p>

                <p class="mt-2 text-3xl font-semibold text-amber-600">
                    {{ number_format($statistics['late']) }}
                </p>

                <p class="mt-1 text-xs text-zinc-500">
                    {{ $minutesToText($statistics['late_minutes']) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500">
                    អវត្តមាន
                </p>

                <p class="mt-2 text-3xl font-semibold text-red-600">
                    {{ number_format($statistics['absent']) }}
                </p>
            </div>
        </div>

        {{-- Work totals --}}
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500">
                    ម៉ោងធ្វើការសរុប
                </p>

                <p
                    class="mt-2 text-xl font-semibold text-zinc-900 dark:text-white"
                >
                    {{ $minutesToText($statistics['worked_minutes']) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500">
                    ម៉ោងបន្ថែមសរុប
                </p>

                <p class="mt-2 text-xl font-semibold text-blue-600">
                    {{ $minutesToText($statistics['overtime_minutes']) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500">
                    ពេលមកយឺតសរុប
                </p>

                <p class="mt-2 text-xl font-semibold text-amber-600">
                    {{ $minutesToText($statistics['late_minutes']) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500">
                    ចេញមុនសរុប
                </p>

                <p class="mt-2 text-xl font-semibold text-red-600">
                    {{ $minutesToText($statistics['early_leave_minutes']) }}
                </p>
            </div>
        </div>

        {{-- Report list --}}
        <div
            class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
        >
            {{-- Filters --}}
            <form
                method="GET"
                action="{{ route('attendance.reports.index') }}"
                class="border-b border-zinc-200 p-5 dark:border-zinc-700"
            >
                <div
                    class="grid gap-3 md:grid-cols-2 xl:grid-cols-4"
                >
                    <div class="md:col-span-2">
                        <flux:input
                            name="search"
                            value="{{ $search }}"
                            icon="magnifying-glass"
                            placeholder="ស្វែងរកឈ្មោះ ឬលេខកូដបុគ្គលិក..."
                        />
                    </div>

                    <flux:input
                        name="date_from"
                        type="date"
                        label="ចាប់ពីថ្ងៃ"
                        value="{{ $dateFrom }}"
                    />

                    <flux:input
                        name="date_to"
                        type="date"
                        label="ដល់ថ្ងៃ"
                        value="{{ $dateTo }}"
                    />

                    <flux:select
                        name="branch_id"
                        label="សាខា"
                    >
                        <flux:select.option value="">
                            សាខាទាំងអស់
                        </flux:select.option>

                        @foreach ($branches as $branch)
                            <flux:select.option
                                value="{{ $branch->id }}"
                                @selected(
                                    (string) $branchId
                                    === (string) $branch->id
                                )
                            >
                                {{ $branch->name }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select
                        name="employee_id"
                        label="បុគ្គលិក"
                    >
                        <flux:select.option value="">
                            បុគ្គលិកទាំងអស់
                        </flux:select.option>

                        @foreach ($employees as $employee)
                            @php
                                $employeeOptionName = $employee->full_name_km
                                    ?: $employee->full_name_en
                                    ?: trim($employee->first_name.' '.$employee->last_name);
                            @endphp

                            <flux:select.option
                                value="{{ $employee->id }}"
                                @selected(
                                    (string) $employeeId
                                    === (string) $employee->id
                                )
                            >
                                {{ $employee->employee_code }}
                                —
                                {{ $employeeOptionName }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:select
                        name="status"
                        label="ស្ថានភាព"
                    >
                        <flux:select.option value="">
                            ស្ថានភាពទាំងអស់
                        </flux:select.option>

                        <flux:select.option
                            value="present"
                            @selected($status === 'present')
                        >
                            វត្តមាន
                        </flux:select.option>

                        <flux:select.option
                            value="late"
                            @selected($status === 'late')
                        >
                            មកយឺត
                        </flux:select.option>

                        <flux:select.option
                            value="absent"
                            @selected($status === 'absent')
                        >
                            អវត្តមាន
                        </flux:select.option>

                        <flux:select.option
                            value="on_leave"
                            @selected($status === 'on_leave')
                        >
                            ឈប់សម្រាក
                        </flux:select.option>

                        <flux:select.option
                            value="half_day"
                            @selected($status === 'half_day')
                        >
                            ពាក់កណ្ដាលថ្ងៃ
                        </flux:select.option>

                        <flux:select.option
                            value="holiday"
                            @selected($status === 'holiday')
                        >
                            ថ្ងៃឈប់សម្រាក
                        </flux:select.option>

                        <flux:select.option
                            value="rest_day"
                            @selected($status === 'rest_day')
                        >
                            ថ្ងៃសម្រាក
                        </flux:select.option>

                        <flux:select.option
                            value="remote_work"
                            @selected($status === 'remote_work')
                        >
                            ធ្វើការពីចម្ងាយ
                        </flux:select.option>

                        <flux:select.option
                            value="business_trip"
                            @selected($status === 'business_trip')
                        >
                            បេសកកម្ម
                        </flux:select.option>
                    </flux:select>

                    <flux:select
                        name="per_page"
                        label="ចំនួនក្នុងមួយទំព័រ"
                    >
                        @foreach ([10, 25, 50, 100] as $size)
                            <flux:select.option
                                value="{{ $size }}"
                                @selected($perPage === $size)
                            >
                                {{ $size }}
                            </flux:select.option>
                        @endforeach
                    </flux:select>
                </div>

                <div
                    class="mt-5 flex flex-wrap justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700"
                >
                    <flux:button
                        type="button"
                        variant="ghost"
                        icon="x-mark"
                        :href="route('attendance.reports.index')"
                    >
                        សម្អាតតម្រង
                    </flux:button>

                    <flux:button
                        type="submit"
                        variant="primary"
                        icon="funnel"
                    >
                        បង្ហាញរបាយការណ៍
                    </flux:button>
                </div>
            </form>

            <div
                class="flex flex-col gap-2 border-b border-zinc-200 px-5 py-4 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2
                        class="font-medium text-zinc-900 dark:text-white"
                    >
                        បញ្ជីវត្តមាន
                    </h2>

                    <p class="mt-1 text-xs text-zinc-500">
                        {{ $dateFrom }} ដល់ {{ $dateTo }}
                    </p>
                </div>

                <p class="text-sm text-zinc-500">
                    សរុប {{ number_format($attendances->total()) }} កំណត់ត្រា
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-5 py-4 font-medium">បុគ្គលិក</th>
                            <th class="px-5 py-4 font-medium">កាលបរិច្ឆេទ</th>
                            <th class="px-5 py-4 font-medium">វេនការងារ</th>
                            <th class="px-5 py-4 font-medium">ម៉ោងចូល</th>
                            <th class="px-5 py-4 font-medium">ម៉ោងចេញ</th>
                            <th class="px-5 py-4 font-medium">ស្ថានភាព</th>
                            <th class="px-5 py-4 text-right font-medium">មកយឺត</th>
                            <th class="px-5 py-4 text-right font-medium">ម៉ោងធ្វើការ</th>
                            <th class="px-5 py-4 text-right font-medium">ម៉ោងបន្ថែម</th>
                        </tr>
                    </thead>

                    <tbody
                        class="divide-y divide-zinc-200 dark:divide-zinc-700"
                    >
                        @forelse ($attendances as $attendance)
                            @php
                                $attendanceEmployeeName = $attendance->employee?->full_name_km
                                    ?: $attendance->employee?->full_name_en
                                    ?: trim(
                                        ($attendance->employee?->first_name ?? '')
                                        .' '.
                                        ($attendance->employee?->last_name ?? '')
                                    );

                                $statusInfo = $statusInformation(
                                    $attendance->status
                                );
                            @endphp

                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-5 py-4">
                                    <div
                                        class="font-medium text-zinc-900 dark:text-white"
                                    >
                                        {{ $attendanceEmployeeName ?: 'មិនមានឈ្មោះ' }}
                                    </div>

                                    <div class="mt-1 text-xs text-zinc-500">
                                        {{ $attendance->employee?->employee_code ?? '—' }}
                                        @if ($attendance->employee?->department)
                                            · {{ $attendance->employee->department->name }}
                                        @endif
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    {{ $attendance->work_date?->format('d/m/Y') ?? '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    {{ $attendance->scheduled_start
                                        ? substr((string) $attendance->scheduled_start, 0, 5)
                                        : '—' }}
                                    –
                                    {{ $attendance->scheduled_end
                                        ? substr((string) $attendance->scheduled_end, 0, 5)
                                        : '—' }}
                                </td>

                                <td class="px-5 py-4">
                                    <div
                                        class="font-medium text-zinc-900 dark:text-white"
                                    >
                                        {{ $attendance->check_in_at?->format('H:i') ?? '—' }}
                                    </div>

                                    @if ($attendance->check_in_method)
                                        <div class="mt-1 text-xs text-zinc-500">
                                            {{ $attendance->check_in_method }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <div
                                        class="font-medium text-zinc-900 dark:text-white"
                                    >
                                        {{ $attendance->check_out_at?->format('H:i') ?? '—' }}
                                    </div>

                                    @if ($attendance->check_out_method)
                                        <div class="mt-1 text-xs text-zinc-500">
                                            {{ $attendance->check_out_method }}
                                        </div>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <flux:badge
                                        size="sm"
                                        :color="$statusInfo['color']"
                                    >
                                        {{ $statusInfo['label'] }}
                                    </flux:badge>
                                </td>

                                <td class="px-5 py-4 text-right">
                                    @if ((int) $attendance->late_minutes > 0)
                                        <span class="text-amber-600 dark:text-amber-400">
                                            {{ number_format($attendance->late_minutes) }} នាទី
                                        </span>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </td>

                                <td
                                    class="px-5 py-4 text-right font-medium text-zinc-900 dark:text-white"
                                >
                                    {{ $minutesToText((int) $attendance->worked_minutes) }}
                                </td>

                                <td class="px-5 py-4 text-right">
                                    @if ((int) $attendance->overtime_minutes > 0)
                                        <span class="text-blue-600 dark:text-blue-400">
                                            {{ $minutesToText((int) $attendance->overtime_minutes) }}
                                        </span>
                                    @else
                                        <span class="text-zinc-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-5 py-14 text-center">
                                    <div
                                        class="font-medium text-zinc-700 dark:text-zinc-200"
                                    >
                                        មិនមានទិន្នន័យវត្តមាន
                                    </div>

                                    <p class="mt-2 text-sm text-zinc-500">
                                        សូមប្តូរលក្ខខណ្ឌស្វែងរក ឬជ្រើសរើសកាលបរិច្ឆេទផ្សេង។
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
