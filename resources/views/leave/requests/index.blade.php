<x-layouts::app>
    @php
        $employeeName = $employee->full_name_km
            ?: $employee->full_name_en
            ?: trim($employee->first_name.' '.$employee->last_name);

        $statusInformation = static function (
            string $status
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
                    'label' => $status,
                    'color' => 'zinc',
                ],
            };
        };
    @endphp

    <div class="w-full space-y-6 p-4 sm:p-6">
        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
        >
            <div>
                <h1
                    class="text-2xl font-semibold text-zinc-900 dark:text-white"
                >
                    សំណើការឈប់សម្រាក
                </h1>

                <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                    ដាក់សំណើ និងតាមដានស្ថានភាពការឈប់សម្រាករបស់អ្នក។
                </p>
            </div>

            <flux:button
                variant="ghost"
                icon="clock"
                :href="route('attendance.checkinout')"
                wire:navigate
            >
                ទៅវត្តមាន
            </flux:button>
        </div>

        @if (session('status'))
            <div
                class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800 dark:border-green-800 dark:bg-green-950/30 dark:text-green-200"
            >
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div
                class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-red-800 dark:border-red-800 dark:bg-red-950/30 dark:text-red-200"
            >
                <ul class="space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500">សំណើសរុប</p>
                <p
                    class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white"
                >
                    {{ number_format($statistics['total']) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500">កំពុងរង់ចាំ</p>
                <p class="mt-2 text-3xl font-semibold text-amber-600">
                    {{ number_format($statistics['pending']) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500">បានអនុម័ត</p>
                <p class="mt-2 text-3xl font-semibold text-green-600">
                    {{ number_format($statistics['approved']) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500">ថ្ងៃនៅសល់ឆ្នាំនេះ</p>
                <p class="mt-2 text-3xl font-semibold text-blue-600">
                    {{ number_format($statistics['remaining'], 1) }}
                </p>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('leave.requests.store') }}"
            class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6"
        >
            @csrf

            <div
                class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2
                        class="text-lg font-semibold text-zinc-900 dark:text-white"
                    >
                        ដាក់សំណើថ្មី
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500">
                        {{ $employeeName }} · {{ $employee->employee_code }}
                    </p>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                <flux:select
                    name="leave_type_id"
                    label="ប្រភេទការឈប់សម្រាក"
                    required
                >
                    <flux:select.option value="">
                        ជ្រើសរើសប្រភេទច្បាប់
                    </flux:select.option>

                    @foreach ($types as $type)
                        <flux:select.option
                            value="{{ $type->id }}"
                            @selected(
                                (string) old('leave_type_id')
                                === (string) $type->id
                            )
                        >
                            {{ $type->name }}
                            —
                            {{ number_format((float) $type->days_per_year, 1) }} ថ្ងៃ/ឆ្នាំ
                        </flux:select.option>
                    @endforeach
                </flux:select>

                <flux:input
                    name="start_date"
                    type="date"
                    label="ថ្ងៃចាប់ផ្ដើម"
                    value="{{ old('start_date') }}"
                    required
                />

                <flux:input
                    name="end_date"
                    type="date"
                    label="ថ្ងៃបញ្ចប់"
                    value="{{ old('end_date') }}"
                    required
                />
            </div>

            <div class="mt-5">
                <flux:textarea
                    name="reason"
                    label="មូលហេតុ"
                    placeholder="ពិពណ៌នាមូលហេតុនៃការឈប់សម្រាក..."
                    rows="4"
                    value="{{ old('reason') }}"
                />
            </div>

            <div
                class="mt-6 flex justify-end border-t border-zinc-200 pt-5 dark:border-zinc-700"
            >
                <flux:button
                    type="submit"
                    variant="primary"
                    icon="paper-airplane"
                >
                    ដាក់សំណើ
                </flux:button>
            </div>
        </form>

        @if ($balances->isNotEmpty())
            <div
                class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
            >
                <div
                    class="border-b border-zinc-200 p-5 dark:border-zinc-700"
                >
                    <h2
                        class="font-medium text-zinc-900 dark:text-white"
                    >
                        សមតុល្យការឈប់សម្រាក ឆ្នាំ {{ now()->year }}
                    </h2>
                </div>

                <div class="grid gap-px bg-zinc-200 sm:grid-cols-2 xl:grid-cols-4 dark:bg-zinc-700">
                    @foreach ($balances as $balance)
                        <div class="bg-white p-5 dark:bg-zinc-900">
                            <p class="text-sm text-zinc-500">
                                {{ $balance->leaveType?->name ?? 'មិនមានប្រភេទ' }}
                            </p>

                            <p
                                class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white"
                            >
                                {{ number_format((float) $balance->remaining_days, 1) }}
                                ថ្ងៃ
                            </p>

                            <p class="mt-1 text-xs text-zinc-500">
                                បានប្រើ {{ number_format((float) $balance->used_days, 1) }} ថ្ងៃ
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div
            class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
        >
            <div
                class="border-b border-zinc-200 p-5 dark:border-zinc-700"
            >
                <h2
                    class="font-medium text-zinc-900 dark:text-white"
                >
                    ប្រវត្តិសំណើ
                </h2>

                <p class="mt-1 text-sm text-zinc-500">
                    បញ្ជីសំណើការឈប់សម្រាករបស់អ្នក។
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-5 py-4 font-medium">ប្រភេទច្បាប់</th>
                            <th class="px-5 py-4 font-medium">កាលបរិច្ឆេទ</th>
                            <th class="px-5 py-4 font-medium">ចំនួនថ្ងៃ</th>
                            <th class="px-5 py-4 font-medium">មូលហេតុ</th>
                            <th class="px-5 py-4 font-medium">ស្ថានភាព</th>
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

                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-5 py-4">
                                    <div
                                        class="font-medium text-zinc-900 dark:text-white"
                                    >
                                        {{ $leaveRequest->leaveType?->name ?? '—' }}
                                    </div>

                                    <div
                                        class="mt-1 font-mono text-xs text-zinc-500"
                                    >
                                        {{ $leaveRequest->leaveType?->code ?? '—' }}
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <div>
                                        {{ $leaveRequest->start_date?->format('d/m/Y') }}
                                        —
                                        {{ $leaveRequest->end_date?->format('d/m/Y') }}
                                    </div>

                                    <div class="mt-1 text-xs text-zinc-500">
                                        ដាក់សំណើ {{ $leaveRequest->created_at?->format('d/m/Y H:i') }}
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    {{ number_format((float) $leaveRequest->total_days, 1) }} ថ្ងៃ
                                </td>

                                <td class="px-5 py-4">
                                    <p
                                        class="max-w-72 truncate text-zinc-600 dark:text-zinc-300"
                                        title="{{ $leaveRequest->reason }}"
                                    >
                                        {{ $leaveRequest->reason ?: '—' }}
                                    </p>
                                </td>

                                <td class="px-5 py-4">
                                    <flux:badge
                                        size="sm"
                                        :color="$statusInfo['color']"
                                    >
                                        {{ $statusInfo['label'] }}
                                    </flux:badge>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-14 text-center">
                                    <div
                                        class="font-medium text-zinc-700 dark:text-zinc-200"
                                    >
                                        មិនទាន់មានសំណើ
                                    </div>

                                    <p class="mt-2 text-sm text-zinc-500">
                                        ដាក់សំណើការឈប់សម្រាកថ្មីតាមទម្រង់ខាងលើ។
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
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
