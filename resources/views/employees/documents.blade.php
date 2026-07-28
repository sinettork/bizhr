<x-layouts::app :title="'ឯកសារបុគ្គលិក'">
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

        $expiredCount = $documents
            ->filter(
                fn ($document) =>
                    $document->expiry_date
                    && $document->expiry_date->isBefore(
                        today()
                    )
            )
            ->count();

        $expiringSoonCount = $documents
            ->filter(
                fn ($document) =>
                    $document->expiry_date
                    && $document->expiry_date->isToday()
                    || (
                        $document->expiry_date
                        && $document->expiry_date->isFuture()
                        && $document->expiry_date->lte(
                            today()->addDays(30)
                        )
                    )
            )
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

                    <span>ឯកសារ</span>
                </div>

                <h1
                    class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white"
                >
                    ឯកសារបុគ្គលិក
                </h1>

                <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                    រក្សាទុក និងគ្រប់គ្រងអត្តសញ្ញាណប័ណ្ណ
                    កិច្ចសន្យា វិញ្ញាបនបត្រ និងឯកសារផ្សេងៗ។
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
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
        </div>

        {{-- Flash message --}}
        @if (session('status'))
            <div
                class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-green-800 dark:border-green-800 dark:bg-green-950/30 dark:text-green-200"
            >
                {{ match (session('status')) {
                    'Document uploaded successfully.' =>
                        'បានបញ្ចូលឯកសារដោយជោគជ័យ។',

                    'Document deleted successfully.' =>
                        'បានលុបឯកសារដោយជោគជ័យ។',

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
            </div>
        </div>

        {{-- Statistics --}}
        <div class="grid gap-4 sm:grid-cols-3">
            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500">
                    ឯកសារសរុប
                </p>

                <p
                    class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white"
                >
                    {{ number_format($documents->count()) }}
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500">
                    ជិតផុតកំណត់
                </p>

                <p
                    class="mt-2 text-3xl font-semibold text-amber-600"
                >
                    {{ number_format($expiringSoonCount) }}
                </p>

                <p class="mt-1 text-xs text-zinc-500">
                    ក្នុងរយៈពេល ៣០ ថ្ងៃ
                </p>
            </div>

            <div
                class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
            >
                <p class="text-sm text-zinc-500">
                    បានផុតកំណត់
                </p>

                <p
                    class="mt-2 text-3xl font-semibold text-red-600"
                >
                    {{ number_format($expiredCount) }}
                </p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-[380px_1fr]">
            {{-- Upload form --}}
            <div>
                <form
                    method="POST"
                    action="{{ route(
                        'employees.documents.store',
                        ['employee' => $employee]
                    ) }}"
                    enctype="multipart/form-data"
                    class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6"
                >
                    @csrf

                    <div>
                        <h2
                            class="text-lg font-semibold text-zinc-900 dark:text-white"
                        >
                            បញ្ចូលឯកសារថ្មី
                        </h2>

                        <p class="mt-1 text-sm text-zinc-500">
                            ទំហំអតិបរមា ១០ MB។
                        </p>
                    </div>

                    {{-- Document type --}}
                    <div>
                        <label
                            for="document_type"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-200"
                        >
                            ប្រភេទឯកសារ
                            <span class="text-red-600">*</span>
                        </label>

                        <input
                            id="document_type"
                            name="document_type"
                            type="text"
                            list="document-type-options"
                            value="{{ old('document_type') }}"
                            placeholder="ឧទាហរណ៍៖ អត្តសញ្ញាណប័ណ្ណ"
                            required
                            class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-zinc-900 outline-none focus:border-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        >

                        <datalist id="document-type-options">
                            <option value="អត្តសញ្ញាណប័ណ្ណ">
                            <option value="លិខិតឆ្លងដែន">
                            <option value="កិច្ចសន្យាការងារ">
                            <option value="បណ្ណបើកបរ">
                            <option value="វិញ្ញាបនបត្រ">
                            <option value="សញ្ញាបត្រ">
                            <option value="លិខិតតែងតាំង">
                            <option value="លិខិតដំឡើងប្រាក់ខែ">
                            <option value="លិខិតព្រមាន">
                            <option value="លិខិតលាឈប់">
                            <option value="ឯកសារផ្សេងៗ">
                        </datalist>

                        @error('document_type')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Document number --}}
                    <div>
                        <label
                            for="document_number"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-200"
                        >
                            លេខឯកសារ
                        </label>

                        <input
                            id="document_number"
                            name="document_number"
                            type="text"
                            value="{{ old('document_number') }}"
                            placeholder="ឧទាហរណ៍៖ ID-123456789"
                            class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-zinc-900 outline-none focus:border-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        >

                        @error('document_number')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- File --}}
                    <div>
                        <label
                            for="document"
                            class="block text-sm font-medium text-zinc-700 dark:text-zinc-200"
                        >
                            ជ្រើសរើសឯកសារ
                            <span class="text-red-600">*</span>
                        </label>

                        <input
                            id="document"
                            name="document"
                            type="file"
                            accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                            required
                            class="mt-2 block w-full rounded-xl border border-zinc-300 bg-white text-sm text-zinc-600 file:mr-3 file:border-0 file:bg-zinc-100 file:px-4 file:py-3 file:text-sm file:font-medium dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:file:bg-zinc-700"
                        >

                        <p class="mt-2 text-xs text-zinc-500">
                            អនុញ្ញាត៖ PDF, JPG, JPEG, PNG,
                            DOC និង DOCX
                        </p>

                        @error('document')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- Dates --}}
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-1">
                        <div>
                            <label
                                for="issued_date"
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-200"
                            >
                                ថ្ងៃចេញឯកសារ
                            </label>

                            <div
                                class="mt-2 flex w-full min-w-0 rounded-xl border border-zinc-300 bg-white px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-800"
                            >
                                <input
                                    id="issued_date"
                                    name="issued_date"
                                    type="date"
                                    value="{{ old('issued_date') }}"
                                    class="block w-full min-w-0 border-0 bg-transparent p-0 text-zinc-900 outline-none dark:text-white"
                                >
                            </div>

                            @error('issued_date')
                                <p class="mt-2 text-sm text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="expiry_date"
                                class="block text-sm font-medium text-zinc-700 dark:text-zinc-200"
                            >
                                ថ្ងៃផុតកំណត់
                            </label>

                            <div
                                class="mt-2 flex w-full min-w-0 rounded-xl border border-zinc-300 bg-white px-3 py-2.5 dark:border-zinc-700 dark:bg-zinc-800"
                            >
                                <input
                                    id="expiry_date"
                                    name="expiry_date"
                                    type="date"
                                    value="{{ old('expiry_date') }}"
                                    class="block w-full min-w-0 border-0 bg-transparent p-0 text-zinc-900 outline-none dark:text-white"
                                >
                            </div>

                            @error('expiry_date')
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
                            កំណត់សម្គាល់
                        </label>

                        <textarea
                            id="notes"
                            name="notes"
                            rows="4"
                            placeholder="ព័ត៌មានបន្ថែមអំពីឯកសារ..."
                            class="mt-2 w-full rounded-xl border border-zinc-300 bg-white px-3 py-2.5 text-zinc-900 outline-none focus:border-zinc-500 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white"
                        >{{ old('notes') }}</textarea>

                        @error('notes')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <flux:button
                        type="submit"
                        variant="primary"
                        icon="arrow-up-tray"
                        class="w-full"
                    >
                        បញ្ចូលឯកសារ
                    </flux:button>
                </form>
            </div>

            {{-- Document list --}}
            <div
                class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
            >
                <div
                    class="border-b border-zinc-200 p-5 dark:border-zinc-700"
                >
                    <div
                        class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                    >
                        <div>
                            <h2
                                class="text-lg font-semibold text-zinc-900 dark:text-white"
                            >
                                បញ្ជីឯកសារ
                            </h2>

                            <p class="mt-1 text-sm text-zinc-500">
                                ចំនួនសរុប៖
                                {{ $documents->count() }}
                            </p>
                        </div>
                    </div>
                </div>

                @if ($documents->isEmpty())
                    <div class="px-5 py-16 text-center">
                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-zinc-100 text-2xl dark:bg-zinc-800"
                        >
                            📄
                        </div>

                        <h3
                            class="mt-4 font-medium text-zinc-900 dark:text-white"
                        >
                            មិនទាន់មានឯកសារ
                        </h3>

                        <p class="mt-2 text-sm text-zinc-500">
                            ប្រើទម្រង់នៅខាងឆ្វេងដើម្បី
                            បញ្ចូលឯកសារដំបូង។
                        </p>
                    </div>
                @else
                    <div
                        class="divide-y divide-zinc-200 dark:divide-zinc-700"
                    >
                        @foreach ($documents as $document)
                            @php
                                $extension = strtolower(
                                    pathinfo(
                                        $document->original_name,
                                        PATHINFO_EXTENSION
                                    )
                                );

                                $expiryLabel = 'មិនកំណត់';
                                $expiryColor = 'zinc';

                                if ($document->expiry_date) {
                                    if (
                                        $document->expiry_date
                                            ->isBefore(today())
                                    ) {
                                        $expiryLabel =
                                            'បានផុតកំណត់';

                                        $expiryColor = 'red';
                                    } elseif (
                                        $document->expiry_date
                                            ->lte(
                                                today()
                                                    ->addDays(30)
                                            )
                                    ) {
                                        $expiryLabel =
                                            'ជិតផុតកំណត់';

                                        $expiryColor = 'amber';
                                    } else {
                                        $expiryLabel =
                                            'មានសុពលភាព';

                                        $expiryColor = 'green';
                                    }
                                }

                                $fileTypeLabel = match (
                                    $extension
                                ) {
                                    'pdf' => 'PDF',
                                    'doc' => 'DOC',
                                    'docx' => 'DOCX',
                                    'jpg',
                                    'jpeg' => 'JPG',
                                    'png' => 'PNG',
                                    default =>
                                        strtoupper($extension),
                                };
                            @endphp

                            <article
                                class="p-5 transition hover:bg-zinc-50 dark:hover:bg-zinc-800/40"
                            >
                                <div
                                    class="flex flex-col gap-4 sm:flex-row sm:items-start"
                                >
                                    {{-- File type --}}
                                    <div
                                        class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-zinc-100 text-xs font-semibold text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200"
                                    >
                                        {{ $fileTypeLabel ?: 'FILE' }}
                                    </div>

                                    {{-- Information --}}
                                    <div class="min-w-0 flex-1">
                                        <div
                                            class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between"
                                        >
                                            <div class="min-w-0">
                                                <h3
                                                    class="font-semibold text-zinc-900 dark:text-white"
                                                >
                                                    {{ $document->document_type }}
                                                </h3>

                                                <p
                                                    class="mt-1 truncate text-sm text-zinc-500"
                                                    title="{{ $document->original_name }}"
                                                >
                                                    {{ $document->original_name }}
                                                </p>
                                            </div>

                                            <flux:badge
                                                size="sm"
                                                :color="$expiryColor"
                                            >
                                                {{ $expiryLabel }}
                                            </flux:badge>
                                        </div>

                                        <dl
                                            class="mt-4 grid gap-4 text-sm sm:grid-cols-2 xl:grid-cols-4"
                                        >
                                            <div>
                                                <dt class="text-zinc-500">
                                                    លេខឯកសារ
                                                </dt>

                                                <dd
                                                    class="mt-1 font-medium text-zinc-900 dark:text-white"
                                                >
                                                    {{ $document->document_number
                                                        ?: 'មិនកំណត់' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-zinc-500">
                                                    ថ្ងៃចេញ
                                                </dt>

                                                <dd
                                                    class="mt-1 font-medium text-zinc-900 dark:text-white"
                                                >
                                                    {{ $document->issued_date
                                                        ? $document
                                                            ->issued_date
                                                            ->format(
                                                                'd/m/Y'
                                                            )
                                                        : 'មិនកំណត់' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-zinc-500">
                                                    ថ្ងៃផុតកំណត់
                                                </dt>

                                                <dd
                                                    class="mt-1 font-medium text-zinc-900 dark:text-white"
                                                >
                                                    {{ $document->expiry_date
                                                        ? $document
                                                            ->expiry_date
                                                            ->format(
                                                                'd/m/Y'
                                                            )
                                                        : 'មិនកំណត់' }}
                                                </dd>
                                            </div>

                                            <div>
                                                <dt class="text-zinc-500">
                                                    បានបញ្ចូលនៅ
                                                </dt>

                                                <dd
                                                    class="mt-1 font-medium text-zinc-900 dark:text-white"
                                                >
                                                    {{ $document->created_at
                                                        ? $document
                                                            ->created_at
                                                            ->format(
                                                                'd/m/Y H:i'
                                                            )
                                                        : '—' }}
                                                </dd>
                                            </div>
                                        </dl>

                                        @if ($document->notes)
                                            <div
                                                class="mt-4 rounded-xl bg-zinc-50 p-3 text-sm text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300"
                                            >
                                                <span class="font-medium">
                                                    កំណត់សម្គាល់៖
                                                </span>

                                                {{ $document->notes }}
                                            </div>
                                        @endif

                                        {{-- Actions --}}
                                        <div
                                            class="mt-4 flex flex-wrap items-center gap-3"
                                        >
                                            <a
                                                href="{{ route(
                                                    'employees.documents.download',
                                                    [
                                                        'employee' =>
                                                            $employee,

                                                        'document' =>
                                                            $document,
                                                    ]
                                                ) }}"
                                                class="inline-flex items-center justify-center rounded-lg bg-zinc-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                                            >
                                                ទាញយកឯកសារ
                                            </a>

                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'employees.documents.destroy',
                                                    [
                                                        'employee' =>
                                                            $employee,

                                                        'document' =>
                                                            $document,
                                                    ]
                                                ) }}"
                                                onsubmit="return confirm(
                                                    'តើអ្នកពិតជាចង់លុបឯកសារនេះមែនទេ?'
                                                )"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="inline-flex items-center justify-center rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950/30"
                                                >
                                                    លុបឯកសារ
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts::app>