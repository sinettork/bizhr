<x-layouts::app :title="'ប្រវត្តិការងារ'">
    @php
        $employeeName =
            $employee->full_name_km
            ?: $employee->full_name_en
            ?: trim(
                $employee->first_name
                . ' '
                . $employee->last_name
            );

        $profilePhotoUrl = $employee->profile_photo
            ? asset(
                'storage/'
                . ltrim($employee->profile_photo, '/')
            )
            : null;

        $initial = mb_strtoupper(
            mb_substr($employeeName, 0, 1)
        );

        $eventLabels = [
            'Hired' => 'ចូលបម្រើការងារ',
            'Transfer' => 'ផ្ទេរសាខា ឬផ្នែក',
            'Promotion' => 'ដំឡើងតំណែង',
            'Position change' => 'ផ្លាស់ប្ដូរមុខតំណែង',
            'Salary adjustment' => 'កែប្រែប្រាក់ខែ',
            'Contract renewal' => 'បន្តកិច្ចសន្យា',
            'Status change' => 'ផ្លាស់ប្ដូរស្ថានភាព',
            'Other' => 'ផ្សេងៗ',
        ];

        $eventColors = [
            'Hired' => 'green',
            'Transfer' => 'blue',
            'Promotion' => 'purple',
            'Position change' => 'indigo',
            'Salary adjustment' => 'amber',
            'Contract renewal' => 'cyan',
            'Status change' => 'orange',
            'Other' => 'zinc',
        ];

        $promotionCount = $histories
            ->where('event_type', 'Promotion')
            ->count();

        $transferCount = $histories
            ->where('event_type', 'Transfer')
            ->count();

        $salaryAdjustmentCount = $histories
            ->where('event_type', 'Salary adjustment')
            ->count();
    @endphp

    <div class="w-full space-y-6 p-4 sm:p-6">
        {{-- Page header --}}
        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
        >
            <div>
                <div
                    class="flex flex-wrap items-center gap-2 text-sm text-zinc-500"
                >
                    <a
                        href="{{ route('employees.index') }}"
                        wire:navigate
                        class="hover:text-zinc-900 dark:hover:text-white"
                    >
                        បញ្ជីបុគ្គលិក
                    </a>

                    <span>/</span>

                    <a
                        href="{{ route(
                            'employees.show',
                            ['employee' => $employee]
                        ) }}"
                        wire:navigate
                        class="hover:text-zinc-900 dark:hover:text-white"
                    >
                        {{ $employeeName }}
                    </a>

                    <span>/</span>

                    <span>ប្រវត្តិការងារ</span>
                </div>

                <h1
                    class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white"
                >
                    ប្រវត្តិការងារ
                </h1>

                <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                    កត់ត្រាការចូលធ្វើការ ការផ្ទេរ ការដំឡើងតំណែង
                    ការកែប្រែប្រាក់ខែ និងព្រឹត្តិការណ៍ការងារផ្សេងៗ។
                </p>
            </div>

            <flux:button
                type="button"
                variant="ghost"
                icon="arrow-left"
                :href="route(
                    'employees.show',
                    ['employee' => $employee]
                )"
                wire:navigate
            >
                ត្រឡប់ទៅប្រវត្តិរូប
            </flux:button>
        </div>

        {{-- Flash message --}}
        @if (session('status'))
            <div
                class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800 dark:border-green-800 dark:bg-green-950/30 dark:text-green-200"
            >
                {{ match (session('status')) {
                    'Employment history entry added.' =>
                        'បានបន្ថែមប្រវត្តិការងារដោយជោគជ័យ។',

                    'Employment history entry removed.' =>
                        'បានលុបប្រវត្តិការងារដោយជោគជ័យ។',

                    default => session('status'),
                } }}
            </div>
        @endif

        {{-- Employee summary --}}
        <div
            class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
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

                    <p class="mt-1 text-sm text-zinc-500">
                        {{ $employee->employee_code }}
                    </p>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <flux:badge size="sm" color="blue">
                            {{ $employee->position?->title
                                ?? 'មិនទាន់មានមុខតំណែង' }}
                        </flux:badge>

                        <flux:badge size="sm" color="zinc">
                            {{ $employee->department?->name
                                ?? 'មិនទាន់មានផ្នែក' }}
                        </flux:badge>

                        <flux:badge size="sm" color="zinc">
                            {{ $employee->branch?->name
                                ?? 'មិនទាន់មានសាខា' }}
                        </flux:badge>
                    </div>
                </div>

                <div
                    class="grid grid-cols-2 gap-4 sm:grid-cols-3"
                >
                    <div
                        class="rounded-xl bg-zinc-50 px-4 py-3 text-center dark:bg-zinc-800"
                    >
                        <p class="text-xs text-zinc-500">
                            ប្រាក់ខែគោល
                        </p>

                        <p
                            class="mt-1 font-semibold text-zinc-900 dark:text-white"
                        >
                            @if ($employee->base_salary !== null)
                                {{ number_format(
                                    (float) $employee->base_salary,
                                    2
                                ) }}

                                {{ $employee->salary_currency }}
                            @else
                                —
                            @endif
                        </p>
                    </div>

                    <div
                        class="rounded-xl bg-zinc-50 px-4 py-3 text-center dark:bg-zinc-800"
                    >
                        <p class="text-xs text-zinc-500">
                            ប្រភេទការងារ
                        </p>

                        <p
                            class="mt-1 font-semibold text-zinc-900 dark:text-white"
                        >
                            {{ $employee->employmentType?->name
                                ?? '—' }}
                        </p>
                    </div>

                    <div
                        class="col-span-2 rounded-xl bg-zinc-50 px-4 py-3 text-center sm:col-span-1 dark:bg-zinc-800"
                    >
                        <p class="text-xs text-zinc-500">
                            ថ្ងៃចូលធ្វើការ
                        </p>

                        <p
                            class="mt-1 font-semibold text-zinc-900 dark:text-white"
                        >
                            {{ $employee->hire_date
                                ? $employee->hire_date->format(
                                    'd/m/Y'
                                )
                                : '—' }}
                        </p>
                    </div>
                </div>
            </div>
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
                    {{ number_format($histories->count()) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500">
                    ដំឡើងតំណែង
                </p>

                <p
                    class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white"
                >
                    {{ number_format($promotionCount) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500">
                    ការផ្ទេរ
                </p>

                <p
                    class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white"
                >
                    {{ number_format($transferCount) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500">
                    កែប្រែប្រាក់ខែ
                </p>

                <p
                    class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white"
                >
                    {{ number_format($salaryAdjustmentCount) }}
                </p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[400px_1fr]">
            {{-- Add history form --}}
            <div>
                <form
                    method="POST"
                    action="{{ route(
                        'employees.history.store',
                        ['employee' => $employee]
                    ) }}"
                    class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6"
                >
                    @csrf

                    <div>
                        <h2
                            class="text-lg font-semibold text-zinc-900 dark:text-white"
                        >
                            បន្ថែមប្រវត្តិការងារ
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500">
                            បញ្ចូលព័ត៌មានដែលមានប្រសិទ្ធភាព
                            ចាប់ពីកាលបរិច្ឆេទដែលបានកំណត់។
                        </p>
                    </div>

                    {{-- Event type --}}
                    <div>
                        <label
                            for="event_type"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-200"
                        >
                            ប្រភេទព្រឹត្តិការណ៍
                            <span class="text-red-600">*</span>
                        </label>

                        <select
                            id="event_type"
                            name="event_type"
                            required
                            class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-zinc-900 outline-none focus:border-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        >
                            <option value="">
                                ជ្រើសរើសប្រភេទ
                            </option>

                            @foreach ($eventLabels as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    @selected(
                                        old('event_type') === $value
                                    )
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>

                        @error('event_type')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Effective date --}}
                    <div>
                        <label
                            for="effective_date"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-200"
                        >
                            ថ្ងៃមានប្រសិទ្ធភាព
                            <span class="text-red-600">*</span>
                        </label>

                        <div
                            class="mt-2 flex w-full min-w-0 rounded-xl border border-zinc-300 bg-white px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-800"
                        >
                            <input
                                id="effective_date"
                                name="effective_date"
                                type="date"
                                value="{{ old(
                                    'effective_date',
                                    now()->format('Y-m-d')
                                ) }}"
                                required
                                class="block w-full min-w-0 border-0 bg-transparent p-0 text-zinc-900 outline-none dark:text-white"
                            >
                        </div>

                        @error('effective_date')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Branch --}}
                    <div>
                        <label
                            for="branch_id"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-200"
                        >
                            សាខា
                        </label>

                        <select
                            id="branch_id"
                            name="branch_id"
                            class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-zinc-900 outline-none focus:border-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        >
                            <option value="">
                                មិនកំណត់
                            </option>

                            @foreach ($branches as $branch)
                                <option
                                    value="{{ $branch->id }}"
                                    @selected(
                                        (string) old(
                                            'branch_id',
                                            $employee->branch_id
                                        )
                                        === (string) $branch->id
                                    )
                                >
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>

                        @error('branch_id')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Department --}}
                    <div>
                        <label
                            for="department_id"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-200"
                        >
                            ផ្នែក
                        </label>

                        <select
                            id="department_id"
                            name="department_id"
                            class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-zinc-900 outline-none focus:border-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        >
                            <option value="">
                                មិនកំណត់
                            </option>

                            @foreach ($departments as $department)
                                <option
                                    value="{{ $department->id }}"
                                    @selected(
                                        (string) old(
                                            'department_id',
                                            $employee->department_id
                                        )
                                        === (string) $department->id
                                    )
                                >
                                    {{ $department->name }}

                                    @if ($department->branch_id)
                                        —
                                        {{ $department->branch?->name }}
                                    @endif
                                </option>
                            @endforeach
                        </select>

                        @error('department_id')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Position --}}
                    <div>
                        <label
                            for="position_id"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-200"
                        >
                            មុខតំណែង
                        </label>

                        <select
                            id="position_id"
                            name="position_id"
                            class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-zinc-900 outline-none focus:border-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        >
                            <option value="">
                                មិនកំណត់
                            </option>

                            @foreach ($positions as $position)
                                <option
                                    value="{{ $position->id }}"
                                    @selected(
                                        (string) old(
                                            'position_id',
                                            $employee->position_id
                                        )
                                        === (string) $position->id
                                    )
                                >
                                    {{ $position->title }}
                                </option>
                            @endforeach
                        </select>

                        @error('position_id')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Employment type --}}
                    <div>
                        <label
                            for="employment_type"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-200"
                        >
                            ប្រភេទការងារ
                        </label>

                        <input
                            id="employment_type"
                            name="employment_type"
                            type="text"
                            value="{{ old(
                                'employment_type',
                                $employee->employmentType?->name
                            ) }}"
                            placeholder="ឧទាហរណ៍៖ ពេញម៉ោង"
                            class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-zinc-900 outline-none focus:border-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        >

                        @error('employment_type')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Salary --}}
                    <div class="grid grid-cols-[1fr_110px] gap-3">
                        <div>
                            <label
                                for="base_salary"
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-200"
                            >
                                ប្រាក់ខែគោល
                            </label>

                            <input
                                id="base_salary"
                                name="base_salary"
                                type="number"
                                min="0"
                                step="0.01"
                                value="{{ old(
                                    'base_salary',
                                    $employee->base_salary
                                ) }}"
                                placeholder="0.00"
                                class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-zinc-900 outline-none focus:border-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            >

                            @error('base_salary')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="salary_currency"
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-200"
                            >
                                រូបិយប័ណ្ណ
                            </label>

                            <select
                                id="salary_currency"
                                name="salary_currency"
                                class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-zinc-900 outline-none focus:border-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                            >
                                <option
                                    value="USD"
                                    @selected(
                                        old(
                                            'salary_currency',
                                            $employee->salary_currency
                                        ) === 'USD'
                                    )
                                >
                                    USD
                                </option>

                                <option
                                    value="KHR"
                                    @selected(
                                        old(
                                            'salary_currency',
                                            $employee->salary_currency
                                        ) === 'KHR'
                                    )
                                >
                                    KHR
                                </option>
                            </select>

                            @error('salary_currency')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    {{-- Notes --}}
                    <div>
                        <label
                            for="notes"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-200"
                        >
                            ហេតុផល និងកំណត់សម្គាល់
                        </label>

                        <textarea
                            id="notes"
                            name="notes"
                            rows="4"
                            placeholder="ពិពណ៌នាមូលហេតុ ឬព័ត៌មានបន្ថែម..."
                            class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-zinc-900 outline-none focus:border-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        >{{ old('notes') }}</textarea>

                        @error('notes')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div
                        class="rounded-xl bg-amber-50 p-4 text-sm text-amber-800 dark:bg-amber-950/30 dark:text-amber-200"
                    >
                        កំណត់ត្រានេះរក្សាទុកតែព័ត៌មានប្រវត្តិ។
                        វាមិនកែប្រែព័ត៌មានបច្ចុប្បន្នរបស់បុគ្គលិក
                        ដោយស្វ័យប្រវត្តិទេ។
                    </div>

                    <flux:button
                        type="submit"
                        variant="primary"
                        icon="plus"
                        class="w-full"
                    >
                        បន្ថែមកំណត់ត្រា
                    </flux:button>
                </form>
            </div>

            {{-- History timeline --}}
            <div
                class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
            >
                <div
                    class="border-b border-zinc-200 p-5 dark:border-zinc-700"
                >
                    <h2
                        class="text-lg font-semibold text-zinc-900 dark:text-white"
                    >
                        បញ្ជីប្រវត្តិការងារ
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500">
                        រៀបតាមថ្ងៃមានប្រសិទ្ធភាពថ្មីបំផុត។
                    </p>
                </div>

                @if ($histories->isEmpty())
                    <div class="px-5 py-16 text-center">
                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-zinc-100 text-2xl dark:bg-zinc-800"
                        >
                            🕘
                        </div>

                        <h3
                            class="mt-4 font-medium text-zinc-900 dark:text-white"
                        >
                            មិនទាន់មានប្រវត្តិការងារ
                        </h3>

                        <p class="mt-2 text-sm text-zinc-500">
                            បន្ថែមកំណត់ត្រាដំបូងតាមទម្រង់
                            នៅខាងឆ្វេង។
                        </p>
                    </div>
                @else
                    <div class="p-5 sm:p-6">
                        <div class="relative space-y-6">
                            <div
                                class="absolute bottom-4 left-[19px] top-4 w-px bg-zinc-200 dark:bg-zinc-700"
                            ></div>

                            @foreach ($histories as $history)
                                @php
                                    $eventLabel =
                                        $eventLabels[
                                            $history->event_type
                                        ]
                                        ?? $history->event_type;

                                    $eventColor =
                                        $eventColors[
                                            $history->event_type
                                        ]
                                        ?? 'zinc';
                                @endphp

                                <article
                                    class="relative flex gap-4"
                                >
                                    <div
                                        class="relative z-10 mt-1 flex h-10 w-10 shrink-0 items-center justify-center rounded-full border-4 border-white bg-zinc-900 text-xs font-semibold text-white dark:border-zinc-900 dark:bg-white dark:text-zinc-900"
                                    >
                                        {{ str_pad(
                                            $loop->iteration,
                                            2,
                                            '0',
                                            STR_PAD_LEFT
                                        ) }}
                                    </div>

                                    <div
                                        class="min-w-0 flex-1 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700 sm:p-5"
                                    >
                                        <div
                                            class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                                        >
                                            <div>
                                                <div
                                                    class="flex flex-wrap items-center gap-2"
                                                >
                                                    <flux:badge
                                                        size="sm"
                                                        :color="$eventColor"
                                                    >
                                                        {{ $eventLabel }}
                                                    </flux:badge>

                                                    <span
                                                        class="text-sm font-medium text-zinc-900 dark:text-white"
                                                    >
                                                        {{ $history
                                                            ->effective_date
                                                            ->format(
                                                                'd/m/Y'
                                                            ) }}
                                                    </span>
                                                </div>

                                                <p
                                                    class="mt-2 text-xs text-zinc-500"
                                                >
                                                    បានកត់ត្រាដោយ៖

                                                    {{ $history->recordedBy?->name
                                                        ?? 'គណនីដែលបានលុប' }}

                                                    @if ($history->created_at)
                                                        ·

                                                        {{ $history
                                                            ->created_at
                                                            ->format(
                                                                'd/m/Y H:i'
                                                            ) }}
                                                    @endif
                                                </p>
                                            </div>

                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'employees.history.destroy',
                                                    [
                                                        'employee' =>
                                                            $employee,

                                                        'history' =>
                                                            $history,
                                                    ]
                                                ) }}"
                                                onsubmit="return confirm(
                                                    'តើអ្នកពិតជាចង់លុបកំណត់ត្រាប្រវត្តិនេះមែនទេ?'
                                                )"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center justify-center rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/30"
                                                >
                                                    លុប
                                                </button>
                                            </form>
                                        </div>

                                        <dl
                                            class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-3"
                                        >
                                            <div>
                                                <dt
                                                    class="text-sm text-zinc-500"
                                                >
                                                    សាខា
                                                </dt>

                                                <dd
                                                    class="mt-1 font-medium text-zinc-900 dark:text-white"
                                                >
                                                    {{ $history->branch?->name
                                                        ?? 'មិនកំណត់' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt
                                                    class="text-sm text-zinc-500"
                                                >
                                                    ផ្នែក
                                                </dt>

                                                <dd
                                                    class="mt-1 font-medium text-zinc-900 dark:text-white"
                                                >
                                                    {{ $history
                                                        ->department?->name
                                                        ?? 'មិនកំណត់' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt
                                                    class="text-sm text-zinc-500"
                                                >
                                                    មុខតំណែង
                                                </dt>

                                                <dd
                                                    class="mt-1 font-medium text-zinc-900 dark:text-white"
                                                >
                                                    {{ $history
                                                        ->position?->title
                                                        ?? 'មិនកំណត់' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt
                                                    class="text-sm text-zinc-500"
                                                >
                                                    ប្រភេទការងារ
                                                </dt>

                                                <dd
                                                    class="mt-1 font-medium text-zinc-900 dark:text-white"
                                                >
                                                    {{ $history
                                                        ->employment_type
                                                        ?: 'មិនកំណត់' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt
                                                    class="text-sm text-zinc-500"
                                                >
                                                    ប្រាក់ខែគោល
                                                </dt>

                                                <dd
                                                    class="mt-1 font-semibold text-zinc-900 dark:text-white"
                                                >
                                                    @if (
                                                        $history->base_salary
                                                        !== null
                                                    )
                                                        {{ number_format(
                                                            (float) $history
                                                                ->base_salary,
                                                            2
                                                        ) }}

                                                        {{ $history
                                                            ->salary_currency
                                                            ?? '' }}
                                                    @else
                                                        មិនកំណត់
                                                    @endif
                                                </dd>
                                            </div>

                                            <div>
                                                <dt
                                                    class="text-sm text-zinc-500"
                                                >
                                                    ថ្ងៃមានប្រសិទ្ធភាព
                                                </dt>

                                                <dd
                                                    class="mt-1 font-medium text-zinc-900 dark:text-white"
                                                >
                                                    {{ $history
                                                        ->effective_date
                                                        ->format(
                                                            'd/m/Y'
                                                        ) }}
                                                </dd>
                                            </div>
                                        </dl>

                                        @if ($history->notes)
                                            <div
                                                class="mt-5 rounded-xl bg-zinc-50 p-4 text-sm text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300"
                                            >
                                                <p
                                                    class="font-medium text-zinc-900 dark:text-white"
                                                >
                                                    ហេតុផល និងកំណត់សម្គាល់
                                                </p>

                                                <p
                                                    class="mt-2 whitespace-pre-line"
                                                >
                                                    {{ $history->notes }}
                                                </p>
                                            </div>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>