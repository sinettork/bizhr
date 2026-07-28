<?php

use App\Models\User;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

new #[Title('គ្រប់គ្រងអ្នកប្រើប្រាស់')] class extends Component
{
    use WithPagination;

    public ?int $userId = null;

    public bool $showForm = false;

    public string $search = '';

    public array $selectedRoles = [];

    public function mount(): void
    {
        abort_unless(
            auth()->user()?->can('user.manage'),
            403
        );
    }

    public function updatedSearch(): void
    {
        $this->resetPage();

        unset($this->users);
    }

    #[Computed]
    public function users()
    {
        return User::query()
            ->with([
                'roles',
                'employee.branch',
                'employee.department',
            ])
            ->when(
                filled($this->search),
                function ($query): void {
                    $search = trim($this->search);

                    $query->where(
                        function ($query) use ($search): void {
                            $query
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhereHas(
                                    'employee',
                                    fn ($employeeQuery) => $employeeQuery
                                        ->where('employee_code', 'like', "%{$search}%")
                                        ->orWhere('full_name_km', 'like', "%{$search}%")
                                        ->orWhere('full_name_en', 'like', "%{$search}%")
                                )
                                ->orWhereHas(
                                    'roles',
                                    fn ($roleQuery) => $roleQuery
                                        ->where('name', 'like', "%{$search}%")
                                );
                        }
                    );
                }
            )
            ->orderBy('name')
            ->paginate(15);
    }

    #[Computed]
    public function roles()
    {
        return Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function selectedUser(): ?User
    {
        if (! $this->userId) {
            return null;
        }

        return User::query()
            ->with([
                'roles',
                'employee.branch',
                'employee.department',
            ])
            ->find($this->userId);
    }

    #[Computed]
    public function statistics(): array
    {
        $total = User::query()->count();

        $withRoles = User::query()
            ->whereHas('roles')
            ->count();

        return [
            'total' => $total,

            'with_roles' => $withRoles,

            'without_roles' => max(
                0,
                $total - $withRoles
            ),

            'linked_employees' => User::query()
                ->whereHas('employee')
                ->count(),
        ];
    }

    public function edit(int $userId): void
    {
        $user = User::query()
            ->with('roles')
            ->findOrFail($userId);

        $this->userId = $user->id;

        $this->selectedRoles = $user
            ->roles
            ->pluck('name')
            ->all();

        $this->showForm = true;
    }

    public function saveRoles(): void
    {
        abort_unless(
            auth()->user()?->can('user.manage'),
            403
        );

        $validated = $this->validate(
            [
                'userId' => [
                    'required',
                    'integer',
                    'exists:users,id',
                ],

                'selectedRoles' => [
                    'array',
                ],

                'selectedRoles.*' => [
                    'string',
                    'exists:roles,name',
                ],
            ],
            [
                'userId.required' =>
                    'សូមជ្រើសរើសអ្នកប្រើប្រាស់។',

                'userId.exists' =>
                    'អ្នកប្រើប្រាស់នេះមិនមានក្នុងប្រព័ន្ធទេ។',

                'selectedRoles.*.exists' =>
                    'តួនាទីដែលបានជ្រើសរើសមិនត្រឹមត្រូវ។',
            ]
        );

        $user = User::query()
            ->findOrFail($validated['userId']);

        $user->syncRoles(
            $validated['selectedRoles']
        );

        unset(
            $this->users,
            $this->selectedUser,
            $this->statistics
        );

        Flux::toast(
            variant: 'success',
            text: 'បានរក្សាទុកតួនាទីអ្នកប្រើប្រាស់ដោយជោគជ័យ។'
        );

        $this->cancelForm();
    }

    public function cancelForm(): void
    {
        $this->reset([
            'userId',
            'selectedRoles',
        ]);

        $this->showForm = false;

        $this->resetValidation();

        unset($this->selectedUser);
    }

    public function roleLabel(string $roleName): string
    {
        return match ($roleName) {
            'Super Admin' => 'អ្នកគ្រប់គ្រងប្រព័ន្ធ',
            'Owner' => 'ម្ចាស់អាជីវកម្ម',
            'HR Manager' => 'អ្នកគ្រប់គ្រងធនធានមនុស្ស',
            'Manager' => 'អ្នកគ្រប់គ្រង',
            'Employee' => 'បុគ្គលិក',
            default => $roleName,
        };
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
                គ្រប់គ្រងអ្នកប្រើប្រាស់
            </h1>

            <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                គ្រប់គ្រងគណនី និងកំណត់តួនាទីសម្រាប់អ្នកប្រើប្រាស់។
            </p>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500">
                អ្នកប្រើប្រាស់សរុប
            </p>

            <p
                class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white"
            >
                {{ number_format($this->statistics['total']) }}
            </p>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500">
                មានតួនាទី
            </p>

            <p class="mt-2 text-3xl font-semibold text-green-600">
                {{ number_format($this->statistics['with_roles']) }}
            </p>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500">
                មិនទាន់មានតួនាទី
            </p>

            <p class="mt-2 text-3xl font-semibold text-amber-600">
                {{ number_format($this->statistics['without_roles']) }}
            </p>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500">
                បានភ្ជាប់បុគ្គលិក
            </p>

            <p class="mt-2 text-3xl font-semibold text-blue-600">
                {{ number_format($this->statistics['linked_employees']) }}
            </p>
        </div>
    </div>

    {{-- Role assignment form --}}
    @if ($showForm && $this->selectedUser)
        <form
            wire:submit="saveRoles"
            class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:p-6"
        >
            <div
                class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2
                        class="text-lg font-semibold text-zinc-900 dark:text-white"
                    >
                        កំណត់តួនាទី
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500">
                        ជ្រើសរើសតួនាទីដែលសមស្របសម្រាប់គណនីនេះ។
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
                class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,2fr)]"
            >
                <div
                    class="rounded-xl bg-zinc-50 p-4 dark:bg-zinc-800"
                >
                    <p class="text-sm text-zinc-500">
                        អ្នកប្រើប្រាស់
                    </p>

                    <p
                        class="mt-2 font-medium text-zinc-900 dark:text-white"
                    >
                        {{ $this->selectedUser->name }}
                    </p>

                    <p class="mt-1 text-sm text-zinc-500">
                        {{ $this->selectedUser->email }}
                    </p>

                    @if ($this->selectedUser->employee)
                        <div
                            class="mt-4 border-t border-zinc-200 pt-4 text-sm dark:border-zinc-700"
                        >
                            <p class="text-zinc-500">
                                {{ $this->selectedUser->employee->employee_code }}
                            </p>

                            <p
                                class="mt-1 text-zinc-700 dark:text-zinc-300"
                            >
                                {{ $this->selectedUser->employee->branch?->name ?? 'មិនមានសាខា' }}
                                ·
                                {{ $this->selectedUser->employee->department?->name ?? 'មិនមានផ្នែក' }}
                            </p>
                        </div>
                    @endif
                </div>

                <div>
                    <h3
                        class="mb-4 font-medium text-zinc-900 dark:text-white"
                    >
                        តួនាទី
                    </h3>

                    <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                        @forelse ($this->roles as $role)
                            <label
                                class="flex cursor-pointer items-start gap-3 rounded-xl border border-zinc-200 p-4 transition hover:bg-zinc-50 dark:border-zinc-700 dark:hover:bg-zinc-800"
                            >
                                <flux:checkbox
                                    wire:model="selectedRoles"
                                    value="{{ $role->name }}"
                                />

                                <span class="min-w-0">
                                    <span
                                        class="block font-medium text-zinc-900 dark:text-white"
                                    >
                                        {{ $this->roleLabel($role->name) }}
                                    </span>

                                    <span
                                        class="mt-1 block truncate text-xs text-zinc-500"
                                    >
                                        {{ $role->name }}
                                    </span>
                                </span>
                            </label>
                        @empty
                            <p class="text-sm text-zinc-500">
                                មិនទាន់មានតួនាទី។
                            </p>
                        @endforelse
                    </div>

                    @error('selectedRoles.*')
                        <p class="mt-3 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

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
                    wire:target="saveRoles"
                >
                    <span wire:loading.remove wire:target="saveRoles">
                        រក្សាទុកតួនាទី
                    </span>

                    <span wire:loading wire:target="saveRoles">
                        កំពុងរក្សាទុក...
                    </span>
                </flux:button>
            </div>
        </form>
    @endif

    {{-- User list --}}
    <div
        class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
    >
        <div
            class="border-b border-zinc-200 p-5 dark:border-zinc-700"
        >
            <div
                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <h2
                        class="font-medium text-zinc-900 dark:text-white"
                    >
                        បញ្ជីអ្នកប្រើប្រាស់
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500">
                        ជ្រើសរើសគណនីដើម្បីកំណត់តួនាទី។
                    </p>
                </div>

                <div class="w-full sm:w-80">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        icon="magnifying-glass"
                        placeholder="ស្វែងរកឈ្មោះ អ៊ីមែល ឬតួនាទី..."
                        clearable
                    />
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-5 py-4 font-medium">
                            អ្នកប្រើប្រាស់
                        </th>

                        <th class="px-5 py-4 font-medium">
                            បុគ្គលិក
                        </th>

                        <th class="px-5 py-4 font-medium">
                            តួនាទី
                        </th>

                        <th class="px-5 py-4 font-medium">
                            ស្ថានភាពអ៊ីមែល
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
                    @forelse ($this->users as $user)
                        <tr
                            wire:key="user-{{ $user->id }}"
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        >
                            <td class="px-5 py-4">
                                <div
                                    class="font-medium text-zinc-900 dark:text-white"
                                >
                                    {{ $user->name }}
                                </div>

                                <div class="mt-1 text-xs text-zinc-500">
                                    {{ $user->email }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if ($user->employee)
                                    <div>
                                        {{ $user->employee->full_name_km
                                            ?: $user->employee->full_name_en
                                            ?: trim($user->employee->first_name.' '.$user->employee->last_name) }}
                                    </div>

                                    <div class="mt-1 text-xs text-zinc-500">
                                        {{ $user->employee->employee_code }}
                                    </div>
                                @else
                                    <span class="text-zinc-400">
                                        មិនទាន់ភ្ជាប់
                                    </span>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex max-w-md flex-wrap gap-1.5">
                                    @forelse ($user->roles as $role)
                                        <flux:badge
                                            size="sm"
                                            color="blue"
                                        >
                                            {{ $this->roleLabel($role->name) }}
                                        </flux:badge>
                                    @empty
                                        <flux:badge
                                            size="sm"
                                            color="amber"
                                        >
                                            មិនទាន់មានតួនាទី
                                        </flux:badge>
                                    @endforelse
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                @if ($user->email_verified_at)
                                    <flux:badge size="sm" color="green">
                                        បានបញ្ជាក់
                                    </flux:badge>
                                @else
                                    <flux:badge size="sm" color="amber">
                                        មិនទាន់បញ្ជាក់
                                    </flux:badge>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex justify-end gap-1">
                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="ghost"
                                        icon="pencil-square"
                                        square
                                        wire:click="edit({{ $user->id }})"
                                        title="កំណត់តួនាទី"
                                    />
                                </div>
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
                                    មិនមានអ្នកប្រើប្រាស់
                                </div>

                                <p class="mt-2 text-sm text-zinc-500">
                                    សូមប្តូរពាក្យស្វែងរក។
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->users->hasPages())
            <div
                class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-700"
            >
                {{ $this->users->links() }}
            </div>
        @endif
    </div>
</div>
