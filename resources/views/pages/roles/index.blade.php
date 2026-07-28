<?php

use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

new #[Title('គ្រប់គ្រងតួនាទី')] class extends Component
{
    public ?int $roleId = null;

    public bool $showForm = false;

    public string $search = '';

    public string $name = '';

    public array $selectedPermissions = [];

    public function mount(): void
    {
        abort_unless(
            auth()->user()?->can('role.manage'),
            403
        );
    }

    protected function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:125',
                'regex:/^[\pL\pN _-]+$/u',

                Rule::unique('roles', 'name')
                    ->where(
                        fn ($query) => $query->where(
                            'guard_name',
                            'web'
                        )
                    )
                    ->ignore($this->roleId),
            ],

            'selectedPermissions' => [
                'array',
            ],

            'selectedPermissions.*' => [
                'string',
                'exists:permissions,name',
            ],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' =>
                'សូមបញ្ចូលឈ្មោះតួនាទី។',

            'name.max' =>
                'ឈ្មោះតួនាទីមិនអាចលើសពី ១២៥ តួអក្សរ។',

            'name.regex' =>
                'ឈ្មោះតួនាទីអាចប្រើអក្សរ លេខ ដកឃ្លា សញ្ញា - និង _ ប៉ុណ្ណោះ។',

            'name.unique' =>
                'ឈ្មោះតួនាទីនេះត្រូវបានប្រើរួចហើយ។',

            'selectedPermissions.*.exists' =>
                'សិទ្ធិដែលបានជ្រើសរើសមិនត្រឹមត្រូវ។',
        ];
    }

    public function updatedSearch(): void
    {
        unset($this->roles);
    }

    #[Computed]
    public function roles()
    {
        return Role::query()
            ->withCount([
                'permissions',
                'users',
            ])
            ->where('guard_name', 'web')
            ->when(
                filled($this->search),
                fn ($query) => $query->where(
                    'name',
                    'like',
                    '%'.trim($this->search).'%'
                )
            )
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function permissionGroups()
    {
        return Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->get()
            ->groupBy(
                fn (Permission $permission) =>
                    (string) str($permission->name)
                        ->before('.')
            );
    }

    #[Computed]
    public function statistics(): array
    {
        return [
            'roles' => Role::query()
                ->where('guard_name', 'web')
                ->count(),

            'permissions' => Permission::query()
                ->where('guard_name', 'web')
                ->count(),

            'assigned_users' => Role::query()
                ->where('guard_name', 'web')
                ->withCount('users')
                ->get()
                ->sum('users_count'),

            'selected' => count($this->selectedPermissions),
        ];
    }

    public function openCreateForm(): void
    {
        $this->resetForm();

        $this->showForm = true;
    }

    public function edit(int $roleId): void
    {
        $role = Role::query()
            ->with('permissions')
            ->where('guard_name', 'web')
            ->findOrFail($roleId);

        $this->roleId = $role->id;
        $this->name = $role->name;

        $this->selectedPermissions = $role
            ->permissions
            ->pluck('name')
            ->all();

        $this->resetValidation();

        $this->showForm = true;
    }

    public function save(): void
    {
        abort_unless(
            auth()->user()?->can('role.manage'),
            403
        );

        $validated = $this->validate();

        if ($this->roleId) {
            $role = Role::query()
                ->where('guard_name', 'web')
                ->findOrFail($this->roleId);

            $role->update([
                'name' => trim($validated['name']),
            ]);

            $message =
                'បានកែប្រែតួនាទីដោយជោគជ័យ។';
        } else {
            $role = Role::query()->create([
                'name' => trim($validated['name']),
                'guard_name' => 'web',
            ]);

            $message =
                'បានបង្កើតតួនាទីថ្មីដោយជោគជ័យ។';
        }

        $permissions = Permission::query()
            ->where('guard_name', 'web')
            ->whereIn(
                'name',
                $validated['selectedPermissions']
            )
            ->get();

        $role->syncPermissions($permissions);

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        unset(
            $this->roles,
            $this->statistics,
            $this->permissionGroups
        );

        Flux::toast(
            variant: 'success',
            text: $message
        );

        $this->cancelForm();
    }

    public function delete(int $roleId): void
    {
        abort_unless(
            auth()->user()?->can('role.manage'),
            403
        );

        $role = Role::query()
            ->where('guard_name', 'web')
            ->findOrFail($roleId);

        if ($this->isProtectedRole($role->name)) {
            Flux::toast(
                variant: 'danger',
                text: 'មិនអាចលុបតួនាទីស្នូលរបស់ប្រព័ន្ធបានទេ។'
            );

            return;
        }

        $role->delete();

        app(PermissionRegistrar::class)
            ->forgetCachedPermissions();

        if ($this->roleId === $roleId) {
            $this->cancelForm();
        }

        unset(
            $this->roles,
            $this->statistics
        );

        Flux::toast(
            variant: 'success',
            text: 'បានលុបតួនាទីដោយជោគជ័យ។'
        );
    }

    public function toggleGroupPermissions(
        string $group
    ): void {
        $permissions = $this->permissionGroups
            ->get($group, collect())
            ->pluck('name')
            ->all();

        if ($permissions === []) {
            return;
        }

        $selectedCount = count(
            array_intersect(
                $permissions,
                $this->selectedPermissions
            )
        );

        if ($selectedCount === count($permissions)) {
            $this->selectedPermissions = array_values(
                array_diff(
                    $this->selectedPermissions,
                    $permissions
                )
            );
        } else {
            $this->selectedPermissions = array_values(
                array_unique([
                    ...$this->selectedPermissions,
                    ...$permissions,
                ])
            );
        }
    }

    public function cancelForm(): void
    {
        $this->resetForm();

        $this->showForm = false;
    }

    public function resetForm(): void
    {
        $this->reset([
            'roleId',
            'name',
            'selectedPermissions',
        ]);

        $this->resetValidation();
    }

    public function groupLabel(string $group): string
    {
        return match ($group) {
            'company' => 'ក្រុមហ៊ុន',
            'branch' => 'សាខា',
            'department' => 'ផ្នែក',
            'position' => 'មុខតំណែង',
            'employment-type' => 'ប្រភេទការងារ',
            'employee' => 'បុគ្គលិក',
            'attendance' => 'វត្តមាន',
            'schedule' => 'កាលវិភាគ',
            'shift' => 'វេនការងារ',
            'leave' => 'ការឈប់សម្រាក',
            'payroll' => 'ប្រាក់ខែ',
            'performance' => 'ការវាយតម្លៃ',
            'task' => 'ភារកិច្ច',
            'report' => 'របាយការណ៍',
            'user' => 'អ្នកប្រើប្រាស់',
            'role' => 'តួនាទី',
            'audit' => 'កំណត់ត្រាសកម្មភាព',
            default => $group,
        };
    }

    public function isProtectedRole(
        string $roleName
    ): bool {
        return in_array(
            $roleName,
            [
                'Super Admin',
                'Owner',
                'HR Manager',
                'Manager',
                'Employee',
            ],
            true
        );
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
                គ្រប់គ្រងតួនាទី និងសិទ្ធិ
            </h1>

            <p class="mt-1 text-zinc-600 dark:text-zinc-300">
                បង្កើតតួនាទី និងកំណត់សិទ្ធិប្រើប្រាស់ប្រព័ន្ធ។
            </p>
        </div>

        <flux:button
            type="button"
            variant="primary"
            icon="plus"
            wire:click="openCreateForm"
        >
            បន្ថែមតួនាទី
        </flux:button>
    </div>

    {{-- Statistics --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500">
                តួនាទីសរុប
            </p>

            <p
                class="mt-2 text-3xl font-semibold text-zinc-900 dark:text-white"
            >
                {{ number_format($this->statistics['roles']) }}
            </p>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500">
                សិទ្ធិសរុប
            </p>

            <p class="mt-2 text-3xl font-semibold text-blue-600">
                {{ number_format($this->statistics['permissions']) }}
            </p>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500">
                ការផ្ដល់តួនាទី
            </p>

            <p class="mt-2 text-3xl font-semibold text-green-600">
                {{ number_format($this->statistics['assigned_users']) }}
            </p>
        </div>

        <div
            class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900"
        >
            <p class="text-sm text-zinc-500">
                សិទ្ធិក្នុងទម្រង់
            </p>

            <p class="mt-2 text-3xl font-semibold text-amber-600">
                {{ number_format(count($selectedPermissions)) }}
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
                        {{ $roleId
                            ? 'កែប្រែតួនាទី'
                            : 'បន្ថែមតួនាទីថ្មី' }}
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500">
                        កំណត់ឈ្មោះតួនាទី និងសិទ្ធិដែលអាចប្រើបាន។
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

            <div class="max-w-xl">
                <flux:input
                    wire:model="name"
                    label="ឈ្មោះតួនាទី"
                    placeholder="ឧទាហរណ៍៖ Accountant"
                    required
                />
            </div>

            <div class="mt-6">
                <div
                    class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
                >
                    <div>
                        <h3
                            class="font-medium text-zinc-900 dark:text-white"
                        >
                            សិទ្ធិប្រើប្រាស់
                        </h3>

                        <p class="mt-1 text-sm text-zinc-500">
                            បានជ្រើសរើស {{ count($selectedPermissions) }} សិទ្ធិ។
                        </p>
                    </div>
                </div>

                <div class="grid gap-4 xl:grid-cols-2">
                    @foreach ($this->permissionGroups as $group => $permissions)
                        @php
                            $permissionNames = $permissions
                                ->pluck('name')
                                ->all();

                            $selectedInGroup = count(
                                array_intersect(
                                    $permissionNames,
                                    $selectedPermissions
                                )
                            );
                        @endphp

                        <div
                            class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700"
                        >
                            <div
                                class="flex items-center justify-between gap-3 border-b border-zinc-200 pb-3 dark:border-zinc-700"
                            >
                                <div>
                                    <h4
                                        class="font-medium text-zinc-900 dark:text-white"
                                    >
                                        {{ $this->groupLabel($group) }}
                                    </h4>

                                    <p class="mt-1 text-xs text-zinc-500">
                                        {{ $selectedInGroup }} / {{ $permissions->count() }}
                                    </p>
                                </div>

                                <flux:button
                                    type="button"
                                    size="sm"
                                    variant="ghost"
                                    wire:click="toggleGroupPermissions('{{ $group }}')"
                                >
                                    {{ $selectedInGroup === $permissions->count()
                                        ? 'ដកទាំងអស់'
                                        : 'ជ្រើសទាំងអស់' }}
                                </flux:button>
                            </div>

                            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                @foreach ($permissions as $permission)
                                    <flux:checkbox
                                        wire:model="selectedPermissions"
                                        value="{{ $permission->name }}"
                                        label="{{ $permission->name }}"
                                    />
                                @endforeach
                            </div>
                        </div>
                    @endforeach
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
                    wire:target="save"
                >
                    <span wire:loading.remove wire:target="save">
                        {{ $roleId
                            ? 'រក្សាទុកការកែប្រែ'
                            : 'បង្កើតតួនាទី' }}
                    </span>

                    <span wire:loading wire:target="save">
                        កំពុងរក្សាទុក...
                    </span>
                </flux:button>
            </div>
        </form>
    @endif

    {{-- Role list --}}
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
                        បញ្ជីតួនាទី
                    </h2>

                    <p class="mt-1 text-sm text-zinc-500">
                        គ្រប់គ្រងតួនាទី និងចំនួនសិទ្ធិរបស់តួនាទីនីមួយៗ។
                    </p>
                </div>

                <div class="w-full sm:w-80">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        icon="magnifying-glass"
                        placeholder="ស្វែងរកតួនាទី..."
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
                            តួនាទី
                        </th>

                        <th class="px-5 py-4 font-medium">
                            ចំនួនសិទ្ធិ
                        </th>

                        <th class="px-5 py-4 font-medium">
                            អ្នកប្រើប្រាស់
                        </th>

                        <th class="px-5 py-4 font-medium">
                            ប្រភេទ
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
                    @forelse ($this->roles as $role)
                        <tr
                            wire:key="role-{{ $role->id }}"
                            class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50"
                        >
                            <td class="px-5 py-4">
                                <div
                                    class="font-medium text-zinc-900 dark:text-white"
                                >
                                    {{ $role->name }}
                                </div>

                                <div class="mt-1 text-xs text-zinc-500">
                                    guard: {{ $role->guard_name }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                {{ number_format($role->permissions_count) }}
                            </td>

                            <td class="px-5 py-4">
                                {{ number_format($role->users_count) }}
                            </td>

                            <td class="px-5 py-4">
                                @if ($this->isProtectedRole($role->name))
                                    <flux:badge size="sm" color="blue">
                                        តួនាទីស្នូល
                                    </flux:badge>
                                @else
                                    <flux:badge size="sm" color="zinc">
                                        តួនាទីបន្ថែម
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
                                        wire:click="edit({{ $role->id }})"
                                        title="កែប្រែ"
                                    />

                                    @unless ($this->isProtectedRole($role->name))
                                        <flux:button
                                            type="button"
                                            size="sm"
                                            variant="danger"
                                            icon="trash"
                                            square
                                            wire:click="delete({{ $role->id }})"
                                            wire:confirm="តើអ្នកពិតជាចង់លុបតួនាទីនេះមែនទេ?"
                                            title="លុប"
                                        />
                                    @endunless
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
                                    មិនមានតួនាទី
                                </div>

                                <p class="mt-2 text-sm text-zinc-500">
                                    បន្ថែមតួនាទីថ្មី ឬប្តូរពាក្យស្វែងរក។
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
