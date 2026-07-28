<?php

use App\Models\Company;
use App\Models\WorkShift;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('វេនការងារ')] class extends Component
{
    use WithPagination;

    public int $companyId;

    public ?int $shiftId = null;

    public bool $showForm = false;

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    public string $search = '';

    public string $filterActive = '';

    public string $filterNight = '';

    /*
    |--------------------------------------------------------------------------
    | Form fields
    |--------------------------------------------------------------------------
    */

    public string $name = '';

    public string $code = '';

    public string $start_time = '08:00';

    public string $end_time = '17:00';

    public int|string $break_minutes = 60;

    public int|string $late_grace_minutes = 5;

    public int|string $early_leave_grace_minutes = 5;

    public bool $is_night_shift = false;

    public bool $is_active = true;

    public function mount(): void
    {
        $company = Company::query()->firstOrFail();

        $this->companyId = $company->id;
    }

    /*
    |--------------------------------------------------------------------------
    | Livewire hooks
    |--------------------------------------------------------------------------
    */

    public function updated(
        string $property,
        mixed $value
    ): void {
        if (
            in_array(
                $property,
                [
                    'search',
                    'filterActive',
                    'filterNight',
                ],
                true
            )
        ) {
            $this->resetPage();

            unset($this->shifts);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'code' => [
                'required',
                'string',
                'max:50',

                Rule::unique(
                    'work_shifts',
                    'code'
                )->ignore($this->shiftId),
            ],

            'start_time' => [
                'required',
                'date_format:H:i',
            ],

            'end_time' => [
                'required',
                'date_format:H:i',
            ],

            'break_minutes' => [
                'required',
                'integer',
                'min:0',
                'max:600',
            ],

            'late_grace_minutes' => [
                'required',
                'integer',
                'min:0',
                'max:180',
            ],

            'early_leave_grace_minutes' => [
                'required',
                'integer',
                'min:0',
                'max:180',
            ],

            'is_night_shift' => [
                'boolean',
            ],

            'is_active' => [
                'boolean',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' =>
                'សូមបញ្ចូលឈ្មោះវេនការងារ។',

            'name.max' =>
                'ឈ្មោះវេនការងារមិនអាចលើសពី ១០០ តួអក្សរ។',

            'code.required' =>
                'សូមបញ្ចូលលេខកូដវេនការងារ។',

            'code.unique' =>
                'លេខកូដវេនការងារនេះត្រូវបានប្រើរួចហើយ។',

            'code.max' =>
                'លេខកូដមិនអាចលើសពី ៥០ តួអក្សរ។',

            'start_time.required' =>
                'សូមបញ្ចូលម៉ោងចាប់ផ្ដើម។',

            'start_time.date_format' =>
                'ម៉ោងចាប់ផ្ដើមមិនត្រឹមត្រូវ។',

            'end_time.required' =>
                'សូមបញ្ចូលម៉ោងបញ្ចប់។',

            'end_time.date_format' =>
                'ម៉ោងបញ្ចប់មិនត្រឹមត្រូវ។',

            'break_minutes.required' =>
                'សូមបញ្ចូលរយៈពេលសម្រាក។',

            'break_minutes.integer' =>
                'រយៈពេលសម្រាកត្រូវតែជាលេខគត់។',

            'break_minutes.min' =>
                'រយៈពេលសម្រាកមិនអាចតិចជាងសូន្យ។',

            'late_grace_minutes.required' =>
                'សូមបញ្ចូលរយៈពេលអនុញ្ញាតឱ្យយឺត។',

            'late_grace_minutes.integer' =>
                'រយៈពេលអនុញ្ញាតឱ្យយឺតត្រូវតែជាលេខគត់។',

            'early_leave_grace_minutes.required' =>
                'សូមបញ្ចូលរយៈពេលអនុញ្ញាតឱ្យចេញមុន។',

            'early_leave_grace_minutes.integer' =>
                'រយៈពេលអនុញ្ញាតឱ្យចេញមុនត្រូវតែជាលេខគត់។',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Statistics
    |--------------------------------------------------------------------------
    */

    #[Computed]
    public function statistics(): array
    {
        $query = WorkShift::query()
            ->where(
                'company_id',
                $this->companyId
            );

        return [
            'total' => (clone $query)->count(),

            'active' => (clone $query)
                ->where('is_active', true)
                ->count(),

            'night' => (clone $query)
                ->where('is_night_shift', true)
                ->count(),

            'inactive' => (clone $query)
                ->where('is_active', false)
                ->count(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Shift list
    |--------------------------------------------------------------------------
    */

    #[Computed]
    public function shifts()
    {
        return WorkShift::query()
            ->withCount('schedules')
            ->where(
                'company_id',
                $this->companyId
            )

            ->when(
                filled($this->search),
                function ($query): void {
                    $search = trim($this->search);

                    $query->where(
                        function ($query) use ($search): void {
                            $query
                                ->where(
                                    'name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'code',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )

            ->when(
                $this->filterActive !== '',
                fn ($query) => $query->where(
                    'is_active',
                    $this->filterActive === '1'
                )
            )

            ->when(
                $this->filterNight !== '',
                fn ($query) => $query->where(
                    'is_night_shift',
                    $this->filterNight === '1'
                )
            )

            ->orderByDesc('is_active')
            ->orderBy('start_time')
            ->orderBy('name')
            ->paginate(10);
    }

    /*
    |--------------------------------------------------------------------------
    | Form actions
    |--------------------------------------------------------------------------
    */

    public function openCreateForm(): void
    {
        $this->authorizeEditing();

        $this->resetForm();

        $this->showForm = true;
    }

    public function cancelForm(): void
    {
        $this->resetForm();

        $this->showForm = false;
    }

    public function save(): void
    {
        $this->authorizeEditing();

        $validated = $this->validate();

        $startMinutes = $this->convertTimeToMinutes(
            $validated['start_time']
        );

        $endMinutes = $this->convertTimeToMinutes(
            $validated['end_time']
        );

        $shiftMinutes = $endMinutes - $startMinutes;

        if ($validated['is_night_shift']) {
            if ($shiftMinutes <= 0) {
                $shiftMinutes += 1440;
            }
        } elseif ($shiftMinutes <= 0) {
            $this->addError(
                'end_time',
                'សម្រាប់វេនថ្ងៃ ម៉ោងបញ្ចប់ត្រូវតែក្រោយម៉ោងចាប់ផ្ដើម។'
            );

            return;
        }

        if (
            (int) $validated['break_minutes']
            >= $shiftMinutes
        ) {
            $this->addError(
                'break_minutes',
                'រយៈពេលសម្រាកត្រូវតែតិចជាងរយៈពេលវេនការងារ។'
            );

            return;
        }

        $validated['name'] =
            trim($validated['name']);

        $validated['code'] =
            strtoupper(trim($validated['code']));

        $validated['start_time'] =
            $this->normalizeDatabaseTime(
                $validated['start_time']
            );

        $validated['end_time'] =
            $this->normalizeDatabaseTime(
                $validated['end_time']
            );

        $validated['break_minutes'] =
            (int) $validated['break_minutes'];

        $validated['late_grace_minutes'] =
            (int) $validated['late_grace_minutes'];

        $validated['early_leave_grace_minutes'] =
            (int) $validated[
                'early_leave_grace_minutes'
            ];

        if ($this->shiftId !== null) {
            $shift = $this->findShift(
                $this->shiftId
            );

            $shift->update($validated);

            session()->flash(
                'status',
                'បានកែប្រែវេនការងារដោយជោគជ័យ។'
            );
        } else {
            WorkShift::query()->create([
                ...$validated,
                'company_id' => $this->companyId,
            ]);

            session()->flash(
                'status',
                'បានបង្កើតវេនការងារថ្មីដោយជោគជ័យ។'
            );
        }

        unset($this->shifts);
        unset($this->statistics);

        $this->resetPage();
        $this->resetForm();

        $this->showForm = false;
    }

    public function edit(int $shiftId): void
    {
        $this->authorizeEditing();

        $shift = $this->findShift($shiftId);

        $this->shiftId = $shift->id;

        $this->name = $shift->name;

        $this->code = $shift->code;

        $this->start_time =
            substr(
                (string) $shift->start_time,
                0,
                5
            );

        $this->end_time =
            substr(
                (string) $shift->end_time,
                0,
                5
            );

        $this->break_minutes =
            $shift->break_minutes;

        $this->late_grace_minutes =
            $shift->late_grace_minutes;

        $this->early_leave_grace_minutes =
            $shift->early_leave_grace_minutes;

        $this->is_night_shift =
            (bool) $shift->is_night_shift;

        $this->is_active =
            (bool) $shift->is_active;

        $this->resetValidation();

        $this->showForm = true;
    }

    public function toggleStatus(
        int $shiftId
    ): void {
        $this->authorizeEditing();

        $shift = $this->findShift($shiftId);

        $shift->update([
            'is_active' => ! $shift->is_active,
        ]);

        unset($this->shifts);
        unset($this->statistics);

        session()->flash(
            'status',
            $shift->fresh()->is_active
                ? 'បានបើកដំណើរការវេនការងារ។'
                : 'បានបិទដំណើរការវេនការងារ។'
        );
    }

    public function delete(int $shiftId): void
    {
        $this->authorizeEditing();

        $shift = $this->findShift($shiftId);

        if ($shift->schedules()->exists()) {
            session()->flash(
                'error',
                'មិនអាចលុបវេននេះបានទេ ព្រោះវាត្រូវបានប្រើក្នុងកាលវិភាគបុគ្គលិក។ អ្នកអាចបិទដំណើរការវាជំនួសវិញ។'
            );

            return;
        }

        $shift->delete();

        if ($this->shiftId === $shiftId) {
            $this->resetForm();

            $this->showForm = false;
        }

        unset($this->shifts);
        unset($this->statistics);

        $this->resetPage();

        session()->flash(
            'status',
            'បានលុបវេនការងារដោយជោគជ័យ។'
        );
    }

    public function clearFilters(): void
    {
        $this->reset([
            'search',
            'filterActive',
            'filterNight',
        ]);

        unset($this->shifts);

        $this->resetPage();
    }

    public function resetForm(): void
    {
        $this->reset([
            'shiftId',
            'name',
            'code',
            'start_time',
            'end_time',
            'break_minutes',
            'late_grace_minutes',
            'early_leave_grace_minutes',
            'is_night_shift',
            'is_active',
        ]);

        $this->start_time = '08:00';
        $this->end_time = '17:00';

        $this->break_minutes = 60;

        $this->late_grace_minutes = 5;

        $this->early_leave_grace_minutes = 5;

        $this->is_night_shift = false;

        $this->is_active = true;

        $this->resetValidation();
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function authorizeEditing(): void
    {
        abort_unless(
            auth()->user()?->can('shift.edit'),
            403
        );
    }

    private function findShift(
        int $shiftId
    ): WorkShift {
        return WorkShift::query()
            ->where(
                'company_id',
                $this->companyId
            )
            ->findOrFail($shiftId);
    }

    private function convertTimeToMinutes(
        string $time
    ): int {
        [$hours, $minutes] = array_map(
            'intval',
            explode(':', $time)
        );

        return ($hours * 60) + $minutes;
    }

    private function normalizeDatabaseTime(
        string $time
    ): string {
        return strlen($time) === 5
            ? $time . ':00'
            : $time;
    }
};
?>

<div class="w-full space-y-6 p-4 sm:p-6">
    {{-- Header --}}
    <div
        class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
    >
        <div>
            <h1
                class="text-2xl font-semibold text-zinc-900 dark:text-white"
            >
                វេនការងារ
            </h1>

            <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                កំណត់ម៉ោងចូល ម៉ោងចេញ ពេលសម្រាក
                និងរយៈពេលអនុញ្ញាតសម្រាប់ការយឺត។
            </p>
        </div>

        @can('shift.edit')
            <flux:button
                type="button"
                variant="primary"
                icon="plus"
                wire:click="openCreateForm"
            >
                បន្ថែមវេនការងារ
            </flux:button>
        @endcan
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

    {{-- Statistics --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500">
                វេនសរុប
            </p>

            <p
                class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white"
            >
                {{ number_format(
                    $this->statistics['total']
                ) }}
            </p>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500">
                កំពុងប្រើ
            </p>

            <p
                class="mt-2 text-3xl font-semibold text-green-600"
            >
                {{ number_format(
                    $this->statistics['active']
                ) }}
            </p>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500">
                វេនយប់
            </p>

            <p
                class="mt-2 text-3xl font-semibold text-blue-600"
            >
                {{ number_format(
                    $this->statistics['night']
                ) }}
            </p>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500">
                បានបិទ
            </p>

            <p
                class="mt-2 text-3xl font-semibold text-zinc-500"
            >
                {{ number_format(
                    $this->statistics['inactive']
                ) }}
            </p>
        </div>
    </div>

    {{-- Create/Edit form --}}
    @if ($showForm)
        <form
            wire:submit="save"
            class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6"
        >
            <div
                class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2
                        class="text-lg font-semibold text-zinc-900 dark:text-white"
                    >
                        {{ $shiftId
                            ? 'កែប្រែវេនការងារ'
                            : 'បង្កើតវេនការងារថ្មី' }}
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500">
                        បំពេញព័ត៌មានម៉ោងការងារ
                        និងច្បាប់អនុញ្ញាត។
                    </p>
                </div>

                <flux:button
                    type="button"
                    variant="ghost"
                    wire:click="cancelForm"
                >
                    បិទទម្រង់
                </flux:button>
            </div>

            <div
                class="grid gap-5 md:grid-cols-2 xl:grid-cols-4"
            >
                <flux:input
                    wire:model="name"
                    label="ឈ្មោះវេនការងារ"
                    placeholder="ឧទាហរណ៍៖ វេនព្រឹក"
                    required
                />

                <flux:input
                    wire:model="code"
                    label="លេខកូដវេន"
                    placeholder="ឧទាហរណ៍៖ SHIFT-AM"
                    required
                />

                <flux:input
                    wire:model="start_time"
                    type="time"
                    label="ម៉ោងចាប់ផ្ដើម"
                    required
                />

                <flux:input
                    wire:model="end_time"
                    type="time"
                    label="ម៉ោងបញ្ចប់"
                    required
                />

                <flux:input
                    wire:model="break_minutes"
                    type="number"
                    min="0"
                    max="600"
                    label="រយៈពេលសម្រាក (នាទី)"
                    required
                />

                <flux:input
                    wire:model="late_grace_minutes"
                    type="number"
                    min="0"
                    max="180"
                    label="អនុញ្ញាតឱ្យយឺត (នាទី)"
                    required
                />

                <flux:input
                    wire:model="early_leave_grace_minutes"
                    type="number"
                    min="0"
                    max="180"
                    label="អនុញ្ញាតឱ្យចេញមុន (នាទី)"
                    required
                />

                <div
                    class="flex flex-col justify-end gap-4 pb-2"
                >
                    <flux:checkbox
                        wire:model="is_night_shift"
                        label="ជាវេនយប់ ឬឆ្លងកាត់ពាក់កណ្ដាលអធ្រាត្រ"
                    />

                    <flux:checkbox
                        wire:model="is_active"
                        label="បើកដំណើរការវេននេះ"
                    />
                </div>
            </div>

            @if ($is_night_shift)
                <div
                    class="mt-5 rounded-xl bg-blue-50 p-4 text-sm text-blue-800 dark:bg-blue-950/30 dark:text-blue-200"
                >
                    វេនយប់អាចចាប់ផ្ដើមនៅថ្ងៃមួយ
                    ហើយបញ្ចប់នៅព្រឹកថ្ងៃបន្ទាប់។
                    ឧទាហរណ៍៖ 22:00 ដល់ 06:00។
                </div>
            @endif

            <div
                class="mt-6 flex flex-wrap justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700"
            >
                <flux:button
                    type="button"
                    variant="ghost"
                    wire:click="cancelForm"
                >
                    បោះបង់
                </flux:button>

                <flux:button
                    type="submit"
                    variant="primary"
                    wire:loading.attr="disabled"
                    wire:target="save"
                >
                    <span
                        wire:loading.remove
                        wire:target="save"
                    >
                        {{ $shiftId
                            ? 'រក្សាទុកការកែប្រែ'
                            : 'បង្កើតវេនការងារ' }}
                    </span>

                    <span
                        wire:loading
                        wire:target="save"
                    >
                        កំពុងរក្សាទុក...
                    </span>
                </flux:button>
            </div>
        </form>
    @endif

    {{-- List --}}
    <div
        class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
    >
        {{-- Filters --}}
        <div
            class="border-b border-zinc-200 p-5 dark:border-zinc-700"
        >
            <div
                class="grid gap-3 md:grid-cols-2 xl:grid-cols-[2fr_1fr_1fr_auto]"
            >
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    icon="magnifying-glass"
                    placeholder="ស្វែងរកឈ្មោះ ឬលេខកូដវេន..."
                    clearable
                />

                <flux:select
                    wire:model.live="filterActive"
                >
                    <flux:select.option value="">
                        ស្ថានភាពទាំងអស់
                    </flux:select.option>

                    <flux:select.option value="1">
                        កំពុងប្រើ
                    </flux:select.option>

                    <flux:select.option value="0">
                        បានបិទ
                    </flux:select.option>
                </flux:select>

                <flux:select
                    wire:model.live="filterNight"
                >
                    <flux:select.option value="">
                        ប្រភេទវេនទាំងអស់
                    </flux:select.option>

                    <flux:select.option value="0">
                        វេនថ្ងៃ
                    </flux:select.option>

                    <flux:select.option value="1">
                        វេនយប់
                    </flux:select.option>
                </flux:select>

                <flux:button
                    type="button"
                    variant="ghost"
                    icon="x-mark"
                    wire:click="clearFilters"
                >
                    សម្អាត
                </flux:button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-5 py-4 font-medium">
                            វេនការងារ
                        </th>

                        <th class="px-5 py-4 font-medium">
                            ម៉ោងការងារ
                        </th>

                        <th class="px-5 py-4 font-medium">
                            រយៈពេល
                        </th>

                        <th class="px-5 py-4 font-medium">
                            ច្បាប់អនុញ្ញាត
                        </th>

                        <th class="px-5 py-4 font-medium">
                            កាលវិភាគ
                        </th>

                        <th class="px-5 py-4 font-medium">
                            ស្ថានភាព
                        </th>

                        @can('shift.edit')
                            <th
                                class="px-5 py-4 text-right font-medium"
                            >
                                សកម្មភាព
                            </th>
                        @endcan
                    </tr>
                </thead>

                <tbody
                    class="divide-y divide-zinc-200 dark:divide-zinc-700"
                >
                    @forelse ($this->shifts as $shift)
                        @php
                            $start = substr(
                                (string) $shift->start_time,
                                0,
                                5
                            );

                            $end = substr(
                                (string) $shift->end_time,
                                0,
                                5
                            );

                            [$startHour, $startMinute] =
                                array_map(
                                    'intval',
                                    explode(':', $start)
                                );

                            [$endHour, $endMinute] =
                                array_map(
                                    'intval',
                                    explode(':', $end)
                                );

                            $startTotal =
                                ($startHour * 60)
                                + $startMinute;

                            $endTotal =
                                ($endHour * 60)
                                + $endMinute;

                            $totalMinutes =
                                $endTotal - $startTotal;

                            if (
                                $shift->is_night_shift
                                && $totalMinutes <= 0
                            ) {
                                $totalMinutes += 1440;
                            }

                            $workingMinutes = max(
                                0,
                                $totalMinutes
                                - (int) $shift->break_minutes
                            );

                            $workingHours = intdiv(
                                $workingMinutes,
                                60
                            );

                            $remainingMinutes =
                                $workingMinutes % 60;
                        @endphp

                        <tr
                            wire:key="work-shift-{{ $shift->id }}"
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        >
                            <td class="px-5 py-4">
                                <div
                                    class="font-medium text-zinc-900 dark:text-white"
                                >
                                    {{ $shift->name }}
                                </div>

                                <div
                                    class="mt-1 font-mono text-xs text-zinc-500"
                                >
                                    {{ $shift->code }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div
                                    class="font-medium text-zinc-900 dark:text-white"
                                >
                                    {{ $start }} – {{ $end }}
                                </div>

                                <div class="mt-1">
                                    @if ($shift->is_night_shift)
                                        <flux:badge
                                            size="sm"
                                            color="blue"
                                        >
                                            វេនយប់
                                        </flux:badge>
                                    @else
                                        <flux:badge
                                            size="sm"
                                            color="zinc"
                                        >
                                            វេនថ្ងៃ
                                        </flux:badge>
                                    @endif
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div
                                    class="font-medium text-zinc-900 dark:text-white"
                                >
                                    {{ $workingHours }} ម៉ោង

                                    @if ($remainingMinutes > 0)
                                        {{ $remainingMinutes }}
                                        នាទី
                                    @endif
                                </div>

                                <div
                                    class="mt-1 text-xs text-zinc-500"
                                >
                                    សម្រាក៖
                                    {{ $shift->break_minutes }}
                                    នាទី
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div>
                                    យឺត៖
                                    {{ $shift->late_grace_minutes }}
                                    នាទី
                                </div>

                                <div
                                    class="mt-1 text-xs text-zinc-500"
                                >
                                    ចេញមុន៖
                                    {{ $shift->early_leave_grace_minutes }}
                                    នាទី
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <div
                                    class="font-medium text-zinc-900 dark:text-white"
                                >
                                    {{ number_format(
                                        $shift->schedules_count
                                    ) }}
                                </div>

                                <div
                                    class="mt-1 text-xs text-zinc-500"
                                >
                                    កំណត់ត្រាកាលវិភាគ
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if ($shift->is_active)
                                    <flux:badge
                                        size="sm"
                                        color="green"
                                    >
                                        កំពុងប្រើ
                                    </flux:badge>
                                @else
                                    <flux:badge
                                        size="sm"
                                        color="zinc"
                                    >
                                        បានបិទ
                                    </flux:badge>
                                @endif
                            </td>

                            @can('shift.edit')
                                <td class="px-5 py-4">
                                    <div
                                        class="flex justify-end gap-1"
                                    >
                                        <flux:button
                                            type="button"
                                            size="sm"
                                            variant="ghost"
                                            icon="pencil-square"
                                            square
                                            wire:click="edit({{ $shift->id }})"
                                            title="កែប្រែ"
                                        />

                                        <flux:button
                                            type="button"
                                            size="sm"
                                            variant="ghost"
                                            :icon="$shift->is_active
                                                ? 'pause-circle'
                                                : 'play-circle'"
                                            square
                                            wire:click="toggleStatus({{ $shift->id }})"
                                            title="{{ $shift->is_active
                                                ? 'បិទដំណើរការ'
                                                : 'បើកដំណើរការ' }}"
                                        />

                                        <flux:button
                                            type="button"
                                            size="sm"
                                            variant="danger"
                                            icon="trash"
                                            square
                                            wire:click="delete({{ $shift->id }})"
                                            wire:confirm="តើអ្នកពិតជាចង់លុបវេនការងារនេះមែនទេ?"
                                            title="លុប"
                                        />
                                    </div>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="7"
                                class="px-5 py-14 text-center"
                            >
                                <div
                                    class="font-medium text-zinc-700 dark:text-zinc-200"
                                >
                                    មិនទាន់មានវេនការងារ
                                </div>

                                <p
                                    class="mt-2 text-sm text-zinc-500"
                                >
                                    បន្ថែមវេនព្រឹក វេនល្ងាច
                                    ឬវេនយប់ដើម្បីចាប់ផ្ដើម។
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->shifts->hasPages())
            <div
                class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-700"
            >
                {{ $this->shifts->links() }}
            </div>
        @endif
    </div>
</div>