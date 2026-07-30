<x-layouts::app>
    @php
        $employeeName = $employee->full_name_km
            ?: $employee->full_name_en
            ?: trim(
                ($employee->first_name ?? '')
                .' '.
                ($employee->last_name ?? '')
            );

        $statusInformation = static function (
            ?string $status
        ): array {
            return match ($status) {
                'pending' => [
                    'label' => 'កំពុងរង់ចាំ',
                    'color' => 'amber',
                ],

                'manager_approved' => [
                    'label' => 'អ្នកគ្រប់គ្រងបានអនុម័ត',
                    'color' => 'blue',
                ],

                'approved' => [
                    'label' => 'បានអនុម័ត',
                    'color' => 'green',
                ],

                'rejected' => [
                    'label' => 'បានបដិសេធ',
                    'color' => 'red',
                ],

                'cancelled' => [
                    'label' => 'បានបោះបង់',
                    'color' => 'zinc',
                ],

                default => [
                    'label' => $status ?: 'មិនបានកំណត់',
                    'color' => 'zinc',
                ],
            };
        };

        $formatDays = static function (
            float|int|string|null $days
        ): string {
            return number_format(
                (float) ($days ?? 0),
                1
            );
        };
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
                    សំណើការឈប់សម្រាក
                </h1>

                <p
                    class="mt-1 text-zinc-600 dark:text-zinc-300"
                >
                    ដាក់សំណើថ្មី និងតាមដានស្ថានភាពការឈប់សម្រាករបស់អ្នក។
                </p>
            </div>

            @if (Route::has('attendance.checkinout'))
                <flux:button
                    type="button"
                    variant="ghost"
                    icon="clock"
                    :href="route('attendance.checkinout')"
                    wire:navigate
                >
                    ទៅវត្តមាន
                </flux:button>
            @endif
        </div>

        {{-- Success message --}}
        @if (session('status'))
            <div
                class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-800 dark:bg-green-950/30 dark:text-green-200"
            >
                {{ session('status') }}
            </div>
        @endif

        {{-- Validation errors --}}
        @if ($errors->any())
            <div
                class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 dark:border-red-800 dark:bg-red-950/30 dark:text-red-200"
            >
                <div class="font-medium">
                    សូមពិនិត្យព័ត៌មានខាងក្រោម៖
                </div>

                <ul class="mt-2 space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Statistics --}}
        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div
                class="rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    សំណើសរុប
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
                    កំពុងរង់ចាំ
                </p>

                <p class="mt-1 text-2xl font-semibold text-amber-600">
                    {{ number_format($statistics['pending'] ?? 0) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    បានអនុម័ត
                </p>

                <p class="mt-1 text-2xl font-semibold text-green-600">
                    {{ number_format($statistics['approved'] ?? 0) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    ថ្ងៃនៅសល់ឆ្នាំនេះ
                </p>

                <p class="mt-1 text-2xl font-semibold text-blue-600">
                    {{
                        number_format(
                            $statistics['remaining'] ?? 0,
                            1
                        )
                    }}
                </p>
            </div>
        </div>

        {{-- New leave request --}}
        <form
            method="POST"
            action="{{ route('leave.requests.store') }}"
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            @csrf

            <div
                class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
            >
                <div>
                    <h2
                        class="text-lg font-semibold text-zinc-900 dark:text-white"
                    >
                        ដាក់សំណើថ្មី
                    </h2>

                    <p
                        class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
                    >
                        {{ $employeeName ?: 'មិនមានឈ្មោះ' }}

                        @if ($employee->employee_code)
                            · {{ $employee->employee_code }}
                        @endif
                    </p>
                </div>

                <span
                    class="inline-flex w-fit rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300"
                >
                    ឆ្នាំ {{ now()->year }}
                </span>
            </div>

            <div
                class="grid grid-cols-1 gap-x-4 gap-y-4 md:grid-cols-2 xl:grid-cols-4"
            >
                {{-- Leave type --}}
                <div class="md:col-span-2">
                    <label
                        for="leave_type_id"
                        class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                    >
                        ប្រភេទការឈប់សម្រាក
                        <span class="text-red-500">*</span>
                    </label>

                    <select
                        id="leave_type_id"
                        name="leave_type_id"
                        required
                        class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-zinc-500 focus:ring-2 focus:ring-zinc-500/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                    >
                        <option value="">
                            ជ្រើសរើសប្រភេទការឈប់សម្រាក
                        </option>

                        @foreach ($types as $type)
                            <option
                                value="{{ $type->id }}"
                                @selected(
                                    (string) old('leave_type_id')
                                    ===
                                    (string) $type->id
                                )
                            >
                                {{ $type->name }}
                                ·
                                {{ $formatDays($type->days_per_year) }}
                                ថ្ងៃ/ឆ្នាំ

                                @if (! $type->is_paid)
                                    · មិនមានប្រាក់ឈ្នួល
                                @endif
                            </option>
                        @endforeach
                    </select>

                    @error('leave_type_id')
                        <p class="mt-1.5 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Start date --}}
                <div>
                    <label
                        for="start_date"
                        class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                    >
                        ថ្ងៃចាប់ផ្ដើម
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        id="start_date"
                        name="start_date"
                        type="date"
                        value="{{ old('start_date') }}"
                        required
                        class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-zinc-500 focus:ring-2 focus:ring-zinc-500/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                    >

                    @error('start_date')
                        <p class="mt-1.5 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- End date --}}
                <div>
                    <label
                        for="end_date"
                        class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                    >
                        ថ្ងៃបញ្ចប់
                        <span class="text-red-500">*</span>
                    </label>

                    <input
                        id="end_date"
                        name="end_date"
                        type="date"
                        value="{{ old('end_date') }}"
                        required
                        class="block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-zinc-500 focus:ring-2 focus:ring-zinc-500/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                    >

                    @error('end_date')
                        <p class="mt-1.5 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Reason --}}
                <div class="md:col-span-2 xl:col-span-3">
                    <label
                        for="reason"
                        class="mb-1.5 block text-sm font-medium text-zinc-700 dark:text-zinc-300"
                    >
                        មូលហេតុ
                    </label>

                    <textarea
                        id="reason"
                        name="reason"
                        rows="2"
                        maxlength="2000"
                        placeholder="ពិពណ៌នាមូលហេតុនៃការឈប់សម្រាក..."
                        class="block min-h-20 w-full resize-y rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-zinc-500 focus:ring-2 focus:ring-zinc-500/20 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white"
                    >{{ old('reason') }}</textarea>

                    @error('reason')
                        <p class="mt-1.5 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Submit --}}
                <div class="flex items-end">
                    <flux:button
                        type="submit"
                        variant="primary"
                        icon="paper-airplane"
                        class="w-full"
                    >
                        ដាក់សំណើ
                    </flux:button>
                </div>
            </div>
        </form>

        {{-- Leave balances --}}
        @if ($balances->isNotEmpty())
            <div
                class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
            >
                <div
                    class="flex flex-col gap-2 border-b border-zinc-200 px-5 py-4 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h2
                            class="font-semibold text-zinc-900 dark:text-white"
                        >
                            សមតុល្យការឈប់សម្រាក
                        </h2>

                        <p
                            class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                        >
                            ឆ្នាំ {{ now()->year }}
                        </p>
                    </div>

                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        សរុបនៅសល់
                        <span
                            class="font-semibold text-blue-600 dark:text-blue-400"
                        >
                            {{
                                number_format(
                                    $balances->sum('remaining_days'),
                                    1
                                )
                            }}
                            ថ្ងៃ
                        </span>
                    </p>
                </div>

                <div
                    class="grid gap-px bg-zinc-200 sm:grid-cols-2 xl:grid-cols-4 dark:bg-zinc-700"
                >
                    @foreach ($balances as $balance)
                        <div
                            class="bg-white px-5 py-4 dark:bg-zinc-900"
                        >
                            <div
                                class="flex items-start justify-between gap-3"
                            >
                                <div class="min-w-0">
                                    <p
                                        class="truncate text-sm font-medium text-zinc-700 dark:text-zinc-300"
                                    >
                                        {{
                                            $balance->leaveType?->name
                                            ?? 'មិនមានប្រភេទ'
                                        }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                    >
                                        {{
                                            $balance->leaveType?->code
                                            ?? '—'
                                        }}
                                    </p>
                                </div>

                                @if ($balance->leaveType?->is_paid)
                                    <flux:badge
                                        size="sm"
                                        color="green"
                                    >
                                        មានប្រាក់
                                    </flux:badge>
                                @else
                                    <flux:badge
                                        size="sm"
                                        color="zinc"
                                    >
                                        គ្មានប្រាក់
                                    </flux:badge>
                                @endif
                            </div>

                            <div
                                class="mt-3 flex items-end justify-between gap-3"
                            >
                                <div>
                                    <p
                                        class="text-2xl font-semibold text-zinc-900 dark:text-white"
                                    >
                                        {{
                                            $formatDays(
                                                $balance->remaining_days
                                            )
                                        }}
                                    </p>

                                    <p
                                        class="text-xs text-zinc-500 dark:text-zinc-400"
                                    >
                                        ថ្ងៃនៅសល់
                                    </p>
                                </div>

                                <p
                                    class="text-right text-xs text-zinc-500 dark:text-zinc-400"
                                >
                                    បានប្រើ
                                    <br>
                                    <span
                                        class="font-medium text-zinc-700 dark:text-zinc-300"
                                    >
                                        {{
                                            $formatDays(
                                                $balance->used_days
                                            )
                                        }}
                                        ថ្ងៃ
                                    </span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Request history --}}
        <div
            class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
        >
            <div
                class="flex flex-col gap-2 border-b border-zinc-200 px-5 py-4 dark:border-zinc-700 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2
                        class="font-semibold text-zinc-900 dark:text-white"
                    >
                        ប្រវត្តិសំណើ
                    </h2>

                    <p
                        class="mt-1 text-sm text-zinc-500 dark:text-zinc-400"
                    >
                        បញ្ជីសំណើការឈប់សម្រាករបស់អ្នក។
                    </p>
                </div>

                <p class="text-sm text-zinc-500 dark:text-zinc-400">
                    សរុប
                    {{ number_format($requests->total()) }}
                    សំណើ
                </p>
            </div>

            {{-- Desktop table --}}
            <div class="hidden overflow-x-auto md:block">
                <table
                    class="w-full min-w-[900px] text-left text-sm"
                >
                    <thead
                        class="bg-zinc-50 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                    >
                        <tr>
                            <th class="px-5 py-3.5 font-medium">
                                ប្រភេទការឈប់សម្រាក
                            </th>

                            <th class="px-5 py-3.5 font-medium">
                                កាលបរិច្ឆេទ
                            </th>

                            <th class="px-5 py-3.5 font-medium">
                                ចំនួនថ្ងៃ
                            </th>

                            <th class="px-5 py-3.5 font-medium">
                                មូលហេតុ
                            </th>

                            <th class="px-5 py-3.5 font-medium">
                                ស្ថានភាព
                            </th>
                        </tr>
                    </thead>

                    <tbody
                        class="divide-y divide-zinc-200 dark:divide-zinc-700"
                    >
                        @forelse ($requests as $leaveRequest)
                            @php
                                $statusInfo = $statusInformation(
                                    $leaveRequest->status
                                );
                            @endphp

                            <tr
                                class="align-top transition hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                            >
                                <td class="px-5 py-4">
                                    <div
                                        class="font-medium text-zinc-900 dark:text-white"
                                    >
                                        {{
                                            $leaveRequest->leaveType?->name
                                            ?? '—'
                                        }}
                                    </div>

                                    <div
                                        class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                    >
                                        {{
                                            $leaveRequest->leaveType?->code
                                            ?? '—'
                                        }}

                                        @if (
                                            $leaveRequest->leaveType
                                            &&
                                            ! $leaveRequest->leaveType->is_paid
                                        )
                                            · គ្មានប្រាក់ឈ្នួល
                                        @endif
                                    </div>
                                </td>

                                <td
                                    class="whitespace-nowrap px-5 py-4 text-zinc-700 dark:text-zinc-300"
                                >
                                    <div>
                                        {{
                                            $leaveRequest->start_date
                                                ?->format('d/m/Y')
                                            ?? '—'
                                        }}

                                        —

                                        {{
                                            $leaveRequest->end_date
                                                ?->format('d/m/Y')
                                            ?? '—'
                                        }}
                                    </div>

                                    <div
                                        class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                    >
                                        ដាក់សំណើ
                                        {{
                                            $leaveRequest->created_at
                                                ?->format('d/m/Y H:i')
                                            ?? '—'
                                        }}
                                    </div>
                                </td>

                                <td
                                    class="whitespace-nowrap px-5 py-4 font-medium text-zinc-900 dark:text-white"
                                >
                                    {{
                                        $formatDays(
                                            $leaveRequest->total_days
                                        )
                                    }}
                                    ថ្ងៃ
                                </td>

                                <td class="px-5 py-4">
                                    <p
                                        class="max-w-72 truncate text-zinc-600 dark:text-zinc-300"
                                        title="{{ $leaveRequest->reason }}"
                                    >
                                        {{
                                            $leaveRequest->reason
                                            ?: 'មិនបានបញ្ចូលមូលហេតុ'
                                        }}
                                    </p>

                                    @if (
                                        $leaveRequest->status === 'rejected'
                                        &&
                                        (
                                            $leaveRequest->hr_note
                                            ||
                                            $leaveRequest->manager_note
                                        )
                                    )
                                        <p
                                            class="mt-1 max-w-72 truncate text-xs text-red-600 dark:text-red-400"
                                            title="{{
                                                $leaveRequest->hr_note
                                                ?: $leaveRequest->manager_note
                                            }}"
                                        >
                                            បដិសេធ៖
                                            {{
                                                $leaveRequest->hr_note
                                                ?: $leaveRequest->manager_note
                                            }}
                                        </p>
                                    @endif
                                </td>

                                <td class="px-5 py-4">
                                    <flux:badge
                                        size="sm"
                                        :color="$statusInfo['color']"
                                    >
                                        {{ $statusInfo['label'] }}
                                    </flux:badge>

                                    @if (
                                        $leaveRequest->status === 'approved'
                                        &&
                                        $leaveRequest->hr
                                    )
                                        <p
                                            class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                        >
                                            ដោយ {{ $leaveRequest->hr->name }}
                                        </p>
                                    @elseif (
                                        $leaveRequest->status === 'manager_approved'
                                        &&
                                        $leaveRequest->manager
                                    )
                                        <p
                                            class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                        >
                                            ដោយ {{ $leaveRequest->manager->name }}
                                        </p>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="5"
                                    class="px-5 py-14 text-center"
                                >
                                    <div
                                        class="font-medium text-zinc-700 dark:text-zinc-200"
                                    >
                                        មិនទាន់មានសំណើ
                                    </div>

                                    <p
                                        class="mt-2 text-sm text-zinc-500 dark:text-zinc-400"
                                    >
                                        ដាក់សំណើការឈប់សម្រាកថ្មីតាមទម្រង់ខាងលើ។
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Mobile cards --}}
            <div
                class="divide-y divide-zinc-200 dark:divide-zinc-700 md:hidden"
            >
                @forelse ($requests as $leaveRequest)
                    @php
                        $statusInfo = $statusInformation(
                            $leaveRequest->status
                        );
                    @endphp

                    <div class="space-y-4 p-5">
                        <div
                            class="flex items-start justify-between gap-4"
                        >
                            <div class="min-w-0">
                                <p
                                    class="truncate font-medium text-zinc-900 dark:text-white"
                                >
                                    {{
                                        $leaveRequest->leaveType?->name
                                        ?? 'មិនមានប្រភេទ'
                                    }}
                                </p>

                                <p
                                    class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
                                >
                                    {{
                                        $leaveRequest->leaveType?->code
                                        ?? '—'
                                    }}
                                </p>
                            </div>

                            <flux:badge
                                size="sm"
                                :color="$statusInfo['color']"
                            >
                                {{ $statusInfo['label'] }}
                            </flux:badge>
                        </div>

                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <p
                                    class="text-xs text-zinc-500 dark:text-zinc-400"
                                >
                                    កាលបរិច្ឆេទ
                                </p>

                                <p
                                    class="mt-1 text-zinc-800 dark:text-zinc-200"
                                >
                                    {{
                                        $leaveRequest->start_date
                                            ?->format('d/m/Y')
                                        ?? '—'
                                    }}

                                    —

                                    {{
                                        $leaveRequest->end_date
                                            ?->format('d/m/Y')
                                        ?? '—'
                                    }}
                                </p>
                            </div>

                            <div>
                                <p
                                    class="text-xs text-zinc-500 dark:text-zinc-400"
                                >
                                    ចំនួនថ្ងៃ
                                </p>

                                <p
                                    class="mt-1 font-medium text-zinc-800 dark:text-zinc-200"
                                >
                                    {{
                                        $formatDays(
                                            $leaveRequest->total_days
                                        )
                                    }}
                                    ថ្ងៃ
                                </p>
                            </div>
                        </div>

                        <div>
                            <p
                                class="text-xs text-zinc-500 dark:text-zinc-400"
                            >
                                មូលហេតុ
                            </p>

                            <p
                                class="mt-1 text-sm text-zinc-700 dark:text-zinc-300"
                            >
                                {{
                                    $leaveRequest->reason
                                    ?: 'មិនបានបញ្ចូលមូលហេតុ'
                                }}
                            </p>
                        </div>

                        @if (
                            $leaveRequest->status === 'rejected'
                            &&
                            (
                                $leaveRequest->hr_note
                                ||
                                $leaveRequest->manager_note
                            )
                        )
                            <div
                                class="rounded-lg bg-red-50 p-3 text-sm text-red-700 dark:bg-red-950/30 dark:text-red-300"
                            >
                                <span class="font-medium">
                                    មូលហេតុបដិសេធ៖
                                </span>

                                {{
                                    $leaveRequest->hr_note
                                    ?: $leaveRequest->manager_note
                                }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="px-5 py-14 text-center">
                        <div
                            class="font-medium text-zinc-700 dark:text-zinc-200"
                        >
                            មិនទាន់មានសំណើ
                        </div>

                        <p
                            class="mt-2 text-sm text-zinc-500 dark:text-zinc-400"
                        >
                            ដាក់សំណើការឈប់សម្រាកថ្មីតាមទម្រង់ខាងលើ។
                        </p>
                    </div>
                @endforelse
            </div>

            @if ($requests->hasPages())
                <div
                    class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-700"
                >
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>