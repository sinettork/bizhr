<x-layouts::app>
    @php
        $statusInformation = static function (
            string $status
        ): array {
            return match ($status) {
                'pending' => [
                    'label' => 'រង់ចាំអ្នកគ្រប់គ្រង',
                    'color' => 'amber',
                ],

                'manager_approved' => [
                    'label' => 'រង់ចាំ HR',
                    'color' => 'blue',
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
                    ពិនិត្យសំណើការឈប់សម្រាក
                </h1>

                <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                    អនុម័ត ឬបដិសេធសំណើការឈប់សម្រាករបស់បុគ្គលិក។
                </p>
            </div>

            <flux:button
                variant="ghost"
                icon="calendar-days"
                :href="route('leave.requests.index')"
            >
                សំណើរបស់ខ្ញុំ
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
                <p class="text-sm text-zinc-500">រង់ចាំអ្នកគ្រប់គ្រង</p>
                <p class="mt-2 text-3xl font-semibold text-amber-600">
                    {{ number_format($statistics['pending'] ?? 0) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500">រង់ចាំ HR</p>
                <p class="mt-2 text-3xl font-semibold text-blue-600">
                    {{ number_format($statistics['manager_approved'] ?? 0) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500">អនុម័តថ្ងៃនេះ</p>
                <p class="mt-2 text-3xl font-semibold text-green-600">
                    {{ number_format($statistics['approved_today'] ?? 0) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500">បដិសេធថ្ងៃនេះ</p>
                <p class="mt-2 text-3xl font-semibold text-red-600">
                    {{ number_format($statistics['rejected_today'] ?? 0) }}
                </p>
            </div>
        </div>

        <div
            class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
        >
            <div
                class="border-b border-zinc-200 p-5 dark:border-zinc-700"
            >
                <h2
                    class="font-medium text-zinc-900 dark:text-white"
                >
                    សំណើកំពុងរង់ចាំ
                </h2>

                <p class="mt-1 text-sm text-zinc-500">
                    សរុប {{ number_format($requests->total()) }} សំណើ។
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-5 py-4 font-medium">បុគ្គលិក</th>
                            <th class="px-5 py-4 font-medium">ប្រភេទច្បាប់</th>
                            <th class="px-5 py-4 font-medium">កាលបរិច្ឆេទ</th>
                            <th class="px-5 py-4 font-medium">មូលហេតុ</th>
                            <th class="px-5 py-4 font-medium">ស្ថានភាព</th>
                            <th class="px-5 py-4 text-right font-medium">ពិនិត្យ</th>
                        </tr>
                    </thead>

                    <tbody
                        class="divide-y divide-zinc-200 dark:divide-zinc-700"
                    >
                        @forelse ($requests as $leaveRequest)
                            @php
                                $employeeName = $leaveRequest->employee?->full_name_km
                                    ?: $leaveRequest->employee?->full_name_en
                                    ?: trim(
                                        ($leaveRequest->employee?->first_name ?? '')
                                        .' '.
                                        ($leaveRequest->employee?->last_name ?? '')
                                    );

                                $statusInfo = $statusInformation(
                                    $leaveRequest->status
                                );
                            @endphp

                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                <td class="px-5 py-4">
                                    <div
                                        class="font-medium text-zinc-900 dark:text-white"
                                    >
                                        {{ $employeeName ?: 'មិនមានឈ្មោះ' }}
                                    </div>

                                    <div class="mt-1 text-xs text-zinc-500">
                                        {{ $leaveRequest->employee?->employee_code ?? '—' }}
                                        ·
                                        {{ $leaveRequest->employee?->department?->name ?? 'មិនមានផ្នែក' }}
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <div>
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
                                        {{ number_format((float) $leaveRequest->total_days, 1) }} ថ្ងៃ
                                    </div>
                                </td>

                                <td class="px-5 py-4">
                                    <p
                                        class="max-w-64 truncate text-zinc-600 dark:text-zinc-300"
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

                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        <form
                                            method="POST"
                                            action="{{ route('leave.requests.reject', $leaveRequest) }}"
                                            class="flex items-center gap-2"
                                        >
                                            @csrf

                                            <input
                                                type="text"
                                                name="note"
                                                placeholder="មូលហេតុបដិសេធ"
                                                class="w-44 rounded-lg border-zinc-300 text-sm dark:border-zinc-700 dark:bg-zinc-950"
                                            >

                                            <flux:button
                                                type="submit"
                                                size="sm"
                                                variant="danger"
                                                icon="x-mark"
                                                square
                                                title="បដិសេធ"
                                            />
                                        </form>

                                        <form
                                            method="POST"
                                            action="{{ route('leave.requests.approve', $leaveRequest) }}"
                                        >
                                            @csrf

                                            <flux:button
                                                type="submit"
                                                size="sm"
                                                variant="primary"
                                                icon="check"
                                                square
                                                title="អនុម័ត"
                                            />
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-14 text-center">
                                    <div
                                        class="font-medium text-zinc-700 dark:text-zinc-200"
                                    >
                                        មិនមានសំណើកំពុងរង់ចាំ
                                    </div>

                                    <p class="mt-2 text-sm text-zinc-500">
                                        សំណើថ្មីនឹងបង្ហាញនៅទីនេះ។
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
