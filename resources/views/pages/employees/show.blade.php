<?php

use App\Models\Employee;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('ព័ត៌មានលម្អិតបុគ្គលិក')] class extends Component
{
    public Employee $employee;

    public function mount(Employee $employee): void
    {
        $user = auth()->user();

        $canView = $user?->can('employee.view')
            || (
                $user?->can('employee.view-own')
                && $employee->user_id === auth()->id()
            );

        abort_unless($canView, 403);

        $this->employee = $employee
            ->load([
                'company',
                'branch',
                'department',
                'position',
                'employmentType',
                'user',
            ])
            ->loadCount([
                'documents',
                'employmentHistories',
            ]);
    }
};
?>

@php
    $employeeName =
        $employee->full_name_km
        ?: $employee->full_name_en
        ?: trim(
            $employee->first_name
            . ' '
            . $employee->last_name
        );

    $initial = mb_strtoupper(
        mb_substr($employeeName, 0, 1)
    );

    $genderLabel = match ($employee->gender) {
        'M' => 'ប្រុស',
        'F' => 'ស្រី',
        'Other' => 'ផ្សេងៗ',
        default => 'មិនបានកំណត់',
    };

    $statusLabel = match (
        $employee->employment_status
    ) {
        'Active' => 'កំពុងបម្រើការងារ',
        'On probation' => 'កំពុងសាកល្បងការងារ',
        'On leave' => 'កំពុងឈប់សម្រាក',
        'Suspended' => 'បានផ្អាកការងារ',
        'Resigned' => 'បានលាឈប់',
        'Terminated' => 'បានបញ្ចប់កិច្ចសន្យា',
        'Retired' => 'ចូលនិវត្តន៍',
        default => 'ព្រាង',
    };

    $statusColor = match (
        $employee->employment_status
    ) {
        'Active' => 'green',
        'On probation' => 'blue',
        'On leave' => 'amber',
        'Suspended' => 'red',

        'Resigned',
        'Terminated',
        'Retired' => 'zinc',

        default => 'zinc',
    };

    $age = $employee->date_of_birth
        ? $employee->date_of_birth->age
        : null;

    $profilePhotoUrl = $employee->profile_photo
        ? asset(
            'storage/'
            . ltrim($employee->profile_photo, '/')
        )
        : null;
@endphp

<div class="w-full space-y-6 p-4 sm:p-6">
    {{-- Header --}}
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

                <span>
                    {{ $employee->employee_code }}
                </span>
            </div>

            <h1
                class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white"
            >
                ព័ត៌មានលម្អិតបុគ្គលិក
            </h1>

            <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                ព័ត៌មានផ្ទាល់ខ្លួន ការងារ កិច្ចសន្យា
                និងព័ត៌មានទំនាក់ទំនង។
            </p>
        </div>

        <div class="flex flex-wrap gap-3">
            <flux:button
                type="button"
                variant="ghost"
                :href="route(
                    'employees.documents.index',
                    ['employee' => $employee]
                )"
            >
                ឯកសារ
                ({{ $employee->documents_count }})
            </flux:button>

            <flux:button
                type="button"
                variant="ghost"
                :href="route(
                    'employees.history.index',
                    ['employee' => $employee]
                )"
            >
                ប្រវត្តិការងារ
                ({{ $employee->employment_histories_count }})
            </flux:button>

            <flux:button
                type="button"
                variant="primary"
                :href="route('employees.index')"
                wire:navigate
            >
                ត្រឡប់ទៅបញ្ជី
            </flux:button>
        </div>
    </div>

    {{-- Employee summary --}}
    <div
        class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
    >
        <div class="p-5 sm:p-7">
            <div
                class="flex flex-col gap-6 lg:flex-row lg:items-center"
            >
                {{-- Profile photo --}}
                @if ($profilePhotoUrl)
                    <img
                        src="{{ $profilePhotoUrl }}"
                        alt="{{ $employeeName }}"
                        class="h-28 w-28 rounded-2xl object-cover ring-1 ring-zinc-200 dark:ring-zinc-700"
                    >
                @else
                    <div
                        class="flex h-28 w-28 shrink-0 items-center justify-center rounded-2xl bg-zinc-100 text-4xl font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                    >
                        {{ $initial }}
                    </div>
                @endif

                {{-- Name and status --}}
                <div class="min-w-0 flex-1">
                    <div
                        class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
                    >
                        <div>
                            <h2
                                class="text-2xl font-semibold text-zinc-900 dark:text-white"
                            >
                                {{ $employeeName }}
                            </h2>

                            @if (
                                $employee->full_name_en
                                && $employee->full_name_en
                                    !== $employeeName
                            )
                                <p
                                    class="mt-1 text-zinc-500 dark:text-zinc-400"
                                >
                                    {{ $employee->full_name_en }}
                                </p>
                            @endif

                            <div
                                class="mt-3 flex flex-wrap items-center gap-2"
                            >
                                <flux:badge
                                    size="sm"
                                    :color="$statusColor"
                                >
                                    {{ $statusLabel }}
                                </flux:badge>

                                @if ($employee->is_active)
                                    <flux:badge
                                        size="sm"
                                        color="green"
                                    >
                                        បើកដំណើរការ
                                    </flux:badge>
                                @else
                                    <flux:badge
                                        size="sm"
                                        color="zinc"
                                    >
                                        បានបិទ
                                    </flux:badge>
                                @endif
                            </div>
                        </div>

                        <div
                            class="rounded-xl bg-zinc-50 px-4 py-3 dark:bg-zinc-800"
                        >
                            <p class="text-xs text-zinc-500">
                                លេខកូដបុគ្គលិក
                            </p>

                            <p
                                class="mt-1 font-semibold text-zinc-900 dark:text-white"
                            >
                                {{ $employee->employee_code }}
                            </p>
                        </div>
                    </div>

                    <div
                        class="mt-6 grid gap-4 border-t border-zinc-200 pt-5 sm:grid-cols-2 xl:grid-cols-4 dark:border-zinc-700"
                    >
                        <div>
                            <p class="text-sm text-zinc-500">
                                មុខតំណែង
                            </p>

                            <p
                                class="mt-1 font-medium text-zinc-900 dark:text-white"
                            >
                                {{ $employee->position?->title
                                    ?? 'មិនទាន់កំណត់' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-zinc-500">
                                ផ្នែក
                            </p>

                            <p
                                class="mt-1 font-medium text-zinc-900 dark:text-white"
                            >
                                {{ $employee->department?->name
                                    ?? 'មិនទាន់កំណត់' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-zinc-500">
                                សាខា
                            </p>

                            <p
                                class="mt-1 font-medium text-zinc-900 dark:text-white"
                            >
                                {{ $employee->branch?->name
                                    ?? 'មិនទាន់កំណត់' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm text-zinc-500">
                                ថ្ងៃចូលធ្វើការ
                            </p>

                            <p
                                class="mt-1 font-medium text-zinc-900 dark:text-white"
                            >
                                {{ $employee->hire_date
                                    ? $employee->hire_date->format(
                                        'd/m/Y'
                                    )
                                    : 'មិនទាន់កំណត់' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        {{-- Left column --}}
        <div class="space-y-6 xl:col-span-2">
            {{-- Personal information --}}
            <section
                class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6"
            >
                <div
                    class="mb-5 flex items-center justify-between"
                >
                    <div>
                        <h2
                            class="text-lg font-semibold text-zinc-900 dark:text-white"
                        >
                            ព័ត៌មានផ្ទាល់ខ្លួន
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500">
                            ឈ្មោះ ភេទ ថ្ងៃកំណើត និងអត្តសញ្ញាណ។
                        </p>
                    </div>
                </div>

                <dl
                    class="grid gap-x-8 gap-y-5 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4"
                >
                    <div>
                        <dt class="text-sm text-zinc-500">
                            នាមខ្លួន
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->first_name }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            នាមត្រកូល
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->last_name }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            ឈ្មោះពេញជាភាសាខ្មែរ
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->full_name_km
                                ?: 'មិនទាន់កំណត់' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            ឈ្មោះពេញជាភាសាអង់គ្លេស
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->full_name_en
                                ?: 'មិនទាន់កំណត់' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            ភេទ
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $genderLabel }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            ថ្ងៃខែឆ្នាំកំណើត
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            @if ($employee->date_of_birth)
                                {{ $employee->date_of_birth->format(
                                    'd/m/Y'
                                ) }}

                                @if ($age !== null)
                                    <span
                                        class="ml-1 text-sm font-normal text-zinc-500"
                                    >
                                        ({{ $age }} ឆ្នាំ)
                                    </span>
                                @endif
                            @else
                                មិនទាន់កំណត់
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            លេខអត្តសញ្ញាណប័ណ្ណ
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->national_id
                                ?: 'មិនទាន់កំណត់' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            លេខលិខិតឆ្លងដែន
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->passport_number
                                ?: 'មិនទាន់កំណត់' }}
                        </dd>
                    </div>
                </dl>
            </section>

            {{-- Employment information --}}
            <section
                class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6"
            >
                <div class="mb-5">
                    <h2
                        class="text-lg font-semibold text-zinc-900 dark:text-white"
                    >
                        ព័ត៌មានការងារ
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500">
                        រចនាសម្ព័ន្ធក្រុមហ៊ុន និងស្ថានភាពការងារ។
                    </p>
                </div>

                <dl
                    class="grid gap-x-8 gap-y-5 sm:grid-cols-2 xl:grid-cols-4"
                >
                    <div>
                        <dt class="text-sm text-zinc-500">
                            ក្រុមហ៊ុន
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->company?->name
                                ?? 'មិនទាន់កំណត់' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            សាខា
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->branch?->name
                                ?? 'មិនទាន់កំណត់' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            ផ្នែក
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->department?->name
                                ?? 'មិនទាន់កំណត់' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            មុខតំណែង
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->position?->title
                                ?? 'មិនទាន់កំណត់' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            ប្រភេទការងារ
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->employmentType?->name
                                ?? 'មិនទាន់កំណត់' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            ស្ថានភាពការងារ
                        </dt>

                        <dd class="mt-1">
                            <flux:badge
                                size="sm"
                                :color="$statusColor"
                            >
                                {{ $statusLabel }}
                            </flux:badge>
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            ថ្ងៃចូលធ្វើការ
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->hire_date
                                ? $employee->hire_date->format(
                                    'd/m/Y'
                                )
                                : 'មិនទាន់កំណត់' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            ថ្ងៃបញ្ចប់សាកល្បង
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->probation_end_date
                                ? $employee
                                    ->probation_end_date
                                    ->format('d/m/Y')
                                : 'មិនទាន់កំណត់' }}
                        </dd>
                    </div>
                </dl>
            </section>

            {{-- Contract and salary --}}
            <section
                class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6"
            >
                <div class="mb-5">
                    <h2
                        class="text-lg font-semibold text-zinc-900 dark:text-white"
                    >
                        កិច្ចសន្យា និងប្រាក់ខែ
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500">
                        ព័ត៌មានកិច្ចសន្យា ប្រាក់ខែ
                        និងវិធីបើកប្រាក់។
                    </p>
                </div>

                <dl
                    class="grid gap-x-8 gap-y-5 sm:grid-cols-2 xl:grid-cols-3"
                >
                    <div>
                        <dt class="text-sm text-zinc-500">
                            ថ្ងៃចាប់ផ្ដើមកិច្ចសន្យា
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->contract_start_date
                                ? $employee
                                    ->contract_start_date
                                    ->format('d/m/Y')
                                : 'មិនទាន់កំណត់' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            ថ្ងៃបញ្ចប់កិច្ចសន្យា
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->contract_end_date
                                ? $employee
                                    ->contract_end_date
                                    ->format('d/m/Y')
                                : 'មិនទាន់កំណត់' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            ប្រាក់ខែគោល
                        </dt>

                        <dd
                            class="mt-1 text-lg font-semibold text-zinc-900 dark:text-white"
                        >
                            @if ($employee->base_salary !== null)
                                {{ number_format(
                                    (float) $employee->base_salary,
                                    2
                                ) }}

                                {{ $employee->salary_currency }}
                            @else
                                <span
                                    class="text-base font-medium"
                                >
                                    មិនទាន់កំណត់
                                </span>
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            វិធីបើកប្រាក់
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->payment_method
                                ?: 'មិនទាន់កំណត់' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            ធនាគារ
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->bank_name
                                ?: 'មិនទាន់កំណត់' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            ឈ្មោះគណនី
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->bank_account_name
                                ?: 'មិនទាន់កំណត់' }}
                        </dd>
                    </div>

                    <div class="sm:col-span-2 xl:col-span-3">
                        <dt class="text-sm text-zinc-500">
                            លេខគណនីធនាគារ
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->bank_account_number
                                ?: 'មិនទាន់កំណត់' }}
                        </dd>
                    </div>
                </dl>
            </section>
        </div>

        {{-- Right column --}}
        <div class="space-y-6">
            {{-- Contact --}}
            <section
                class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <h2
                    class="text-lg font-semibold text-zinc-900 dark:text-white"
                >
                    ព័ត៌មានទំនាក់ទំនង
                </h2>

                <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-zinc-500">
                            លេខទូរស័ព្ទ
                        </dt>

                        <dd
                            class="mt-1 break-words font-medium text-zinc-900 dark:text-white"
                        >
                            @if ($employee->phone)
                                <a
                                    href="tel:{{ $employee->phone }}"
                                    class="hover:underline"
                                >
                                    {{ $employee->phone }}
                                </a>
                            @else
                                មិនទាន់កំណត់
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            អ៊ីមែល
                        </dt>

                        <dd
                            class="mt-1 break-words font-medium text-zinc-900 dark:text-white"
                        >
                            @if ($employee->email)
                                <a
                                    href="mailto:{{ $employee->email }}"
                                    class="hover:underline"
                                >
                                    {{ $employee->email }}
                                </a>
                            @else
                                មិនទាន់កំណត់
                            @endif
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            រាជធានី/ខេត្ត
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->city
                                ?: 'មិនទាន់កំណត់' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            អាសយដ្ឋាន
                        </dt>

                        <dd
                            class="mt-1 whitespace-pre-line font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->address
                                ?: 'មិនទាន់កំណត់' }}
                        </dd>
                    </div>
                </dl>
            </section>

            {{-- Emergency contact --}}
            <section
                class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <h2
                    class="text-lg font-semibold text-zinc-900 dark:text-white"
                >
                    ទំនាក់ទំនងបន្ទាន់
                </h2>

                <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div>
                        <dt class="text-sm text-zinc-500">
                            ឈ្មោះអ្នកទំនាក់ទំនង
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->emergency_contact_name
                                ?: 'មិនទាន់កំណត់' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            លេខទូរស័ព្ទបន្ទាន់
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            @if (
                                $employee->emergency_contact_phone
                            )
                                <a
                                    href="tel:{{ $employee->emergency_contact_phone }}"
                                    class="hover:underline"
                                >
                                    {{ $employee->emergency_contact_phone }}
                                </a>
                            @else
                                មិនទាន់កំណត់
                            @endif
                        </dd>
                    </div>
                </dl>
            </section>

            {{-- Account --}}
            <section
                class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <h2
                    class="text-lg font-semibold text-zinc-900 dark:text-white"
                >
                    គណនីប្រើប្រាស់
                </h2>

                <div class="mt-5">
                    @if ($employee->user)
                        <div
                            class="rounded-xl bg-green-50 p-4 text-green-800 dark:bg-green-950/30 dark:text-green-200"
                        >
                            <p class="font-medium">
                                បានភ្ជាប់គណនី
                            </p>

                            <p class="mt-1 text-sm">
                                {{ $employee->user->email }}
                            </p>
                        </div>
                    @else
                        <div
                            class="rounded-xl bg-zinc-50 p-4 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                        >
                            <p class="font-medium">
                                មិនទាន់ភ្ជាប់គណនី
                            </p>

                            <p class="mt-1 text-sm text-zinc-500">
                                បុគ្គលិកនេះមិនទាន់មានគណនី
                                សម្រាប់ចូលប្រើប្រព័ន្ធ។
                            </p>
                        </div>
                    @endif
                </div>
            </section>

            {{-- Records --}}
            <section
                class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <h2
                    class="text-lg font-semibold text-zinc-900 dark:text-white"
                >
                    កំណត់ត្រាបុគ្គលិក
                </h2>

                <div class="mt-5 space-y-3">
                    <a
                        href="{{ route(
                            'employees.documents.index',
                            ['employee' => $employee]
                        ) }}"
                        class="flex items-center justify-between rounded-xl border border-zinc-200 p-4 transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800"
                    >
                        <div>
                            <p
                                class="font-medium text-zinc-900 dark:text-white"
                            >
                                ឯកសារបុគ្គលិក
                            </p>

                            <p
                                class="mt-1 text-sm text-zinc-500"
                            >
                                អត្តសញ្ញាណប័ណ្ណ កិច្ចសន្យា
                                និងវិញ្ញាបនបត្រ
                            </p>
                        </div>

                        <flux:badge size="sm" color="blue">
                            {{ $employee->documents_count }}
                        </flux:badge>
                    </a>

                    <a
                        href="{{ route(
                            'employees.history.index',
                            ['employee' => $employee]
                        ) }}"
                        class="flex items-center justify-between rounded-xl border border-zinc-200 p-4 transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800"
                    >
                        <div>
                            <p
                                class="font-medium text-zinc-900 dark:text-white"
                            >
                                ប្រវត្តិការងារ
                            </p>

                            <p
                                class="mt-1 text-sm text-zinc-500"
                            >
                                ការផ្លាស់ប្តូរតួនាទី ផ្នែក
                                និងប្រាក់ខែ
                            </p>
                        </div>

                        <flux:badge size="sm" color="blue">
                            {{ $employee->employment_histories_count }}
                        </flux:badge>
                    </a>
                </div>
            </section>

            {{-- System information --}}
            <section
                class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <h2
                    class="text-lg font-semibold text-zinc-900 dark:text-white"
                >
                    ព័ត៌មានប្រព័ន្ធ
                </h2>

                <dl class="mt-5 space-y-4">
                    <div>
                        <dt class="text-sm text-zinc-500">
                            បានបង្កើតនៅ
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->created_at
                                ? $employee->created_at->format(
                                    'd/m/Y H:i'
                                )
                                : 'មិនមានទិន្នន័យ' }}
                        </dd>
                    </div>

                    <div>
                        <dt class="text-sm text-zinc-500">
                            កែប្រែចុងក្រោយ
                        </dt>

                        <dd
                            class="mt-1 font-medium text-zinc-900 dark:text-white"
                        >
                            {{ $employee->updated_at
                                ? $employee->updated_at->format(
                                    'd/m/Y H:i'
                                )
                                : 'មិនមានទិន្នន័យ' }}
                        </dd>
                    </div>
                </dl>
            </section>
        </div>
    </div>
</div>