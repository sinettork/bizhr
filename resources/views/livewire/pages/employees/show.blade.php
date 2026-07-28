<x-layouts::app :title="'Employee profile'">
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <flux:button :href="route('employees.index')" icon="arrow-left" variant="ghost" wire:navigate>Back</flux:button>
                <div>
                    <flux:heading size="xl">{{ $employee->getFullName() }}</flux:heading>
                    <flux:text>{{ $employee->employee_code }} · {{ $employee->position?->title ?? '-' }}</flux:text>
                </div>
            </div>

            <div class="flex items-center gap-2">
                @can('employee.edit')
                    <flux:button :href="route('employees.index')" variant="ghost" icon="pencil">Edit</flux:button>
                @endcan
                <flux:button :href="route('employees.documents.index', $employee)" icon="paper-clip" wire:navigate>Documents</flux:button>
            </div>
        </div>

        <div class="space-y-4">
            <flux:card>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="col-span-1">
                        <div class="space-y-3">
                            <img src="{{ $employee->profile_photo ? asset('storage/'.$employee->profile_photo) : asset('images/avatar-placeholder.png') }}" alt="Profile" class="w-32 h-32 rounded object-cover" />
                            <div class="text-sm text-zinc-500">{{ $employee->email }}</div>
                            <div class="text-sm text-zinc-500">{{ $employee->phone }}</div>
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <div class="text-xs text-zinc-500">Position</div>
                                <div class="font-medium">{{ $employee->position?->title ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-zinc-500">Department</div>
                                <div class="font-medium">{{ $employee->department?->name ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-zinc-500">Branch</div>
                                <div class="font-medium">{{ $employee->branch?->name ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-zinc-500">Employment type</div>
                                <div class="font-medium">{{ $employee->employmentType?->name ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-zinc-500">Hire date</div>
                                <div class="font-medium">{{ optional($employee->hire_date)->format('d/m/Y') ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-zinc-500">Status</div>
                                <flux:badge>{{ $employee->employment_status }}</flux:badge>
                            </div>
                        </div>
                    </div>
                </div>
            </flux:card>

            <flux:card>
                <div class="flex items-center gap-4 border-b pb-3">
                    <nav class="flex gap-2">
                        <flux:button :href="route('employees.show', $employee)" variant="ghost" size="sm" wire:navigate>Overview</flux:button>
                        <flux:button :href="route('employees.documents.index', $employee)" variant="ghost" size="sm" wire:navigate>Documents</flux:button>
                        <flux:button :href="route('employees.history.index', $employee)" variant="ghost" size="sm" wire:navigate>History</flux:button>
                        <flux:button :href="route('attendance.checkinout')" variant="ghost" size="sm" wire:navigate>Attendance</flux:button>
                        <flux:button variant="ghost" size="sm" disabled>Leave</flux:button>
                        <flux:button variant="ghost" size="sm" disabled>Payroll</flux:button>
                    </nav>
                </div>

                <div class="mt-4">
                    <h3 class="text-lg font-semibold">Contact</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                        <div>
                            <div class="text-xs text-zinc-500">Phone</div>
                            <div class="font-medium">{{ $employee->phone ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-zinc-500">Email</div>
                            <div class="font-medium">{{ $employee->email ?? '-' }}</div>
                        </div>
                        <div>
                            <div class="text-xs text-zinc-500">Emergency contact</div>
                            <div class="font-medium">{{ $employee->emergency_contact_name ? $employee->emergency_contact_name.' · '.$employee->emergency_contact_phone : '-' }}</div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <h3 class="text-lg font-semibold">Bank & Payment</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                            <div>
                                <div class="text-xs text-zinc-500">Payment method</div>
                                <div class="font-medium">{{ $employee->payment_method ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-zinc-500">Bank</div>
                                <div class="font-medium">{{ $employee->bank_name ?? '-' }}</div>
                            </div>
                            <div>
                                <div class="text-xs text-zinc-500">Account</div>
                                <div class="font-medium">{{ $employee->bank_account_number ? $employee->bank_account_name.' · '.$employee->bank_account_number : '-' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </flux:card>
        </div>
    </div>
</x-layouts::app>
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <flux:button :href="route('employees.index')" icon="arrow-left" variant="ghost" wire:navigate>Back</flux:button>
        <div>
            <flux:heading size="xl">{{ $employee->getFullNameKm() }}</flux:heading>
            <flux:text>{{ $employee->employee_code }}</flux:text>
        </div>
        @can('employee.edit')
            <flux:spacer />
            <flux:button :href="route('employees.documents.index', $employee)" icon="document-text">Documents</flux:button>
            <flux:button :href="route('employees.history.index', $employee)" icon="clock">History</flux:button>
        @endcan
    </div>

    <flux:card>
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center">
            <flux:avatar :name="$employee->getFullName()" :src="$employee->profile_photo ? asset('storage/'.$employee->profile_photo) : null" size="xl" />
            <div class="space-y-1">
                <flux:heading size="lg">{{ $employee->getFullName() }}</flux:heading>
                <flux:text>{{ $employee->position?->title ?? 'No position assigned' }} · {{ $employee->department?->name ?? 'No department assigned' }}</flux:text>
                <flux:badge :color="$employee->is_active ? 'green' : 'zinc'">{{ $employee->employment_status }}</flux:badge>
            </div>
        </div>
    </flux:card>

    <div class="grid gap-6 lg:grid-cols-2">
        <flux:card class="space-y-4">
            <flux:heading size="lg">Contact</flux:heading>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div><dt class="text-sm text-zinc-500">Phone</dt><dd>{{ $employee->phone ?: '—' }}</dd></div>
                <div><dt class="text-sm text-zinc-500">Email</dt><dd>{{ $employee->email ?: '—' }}</dd></div>
                <div class="sm:col-span-2"><dt class="text-sm text-zinc-500">Address</dt><dd>{{ collect([$employee->address, $employee->city])->filter()->join(', ') ?: '—' }}</dd></div>
            </dl>
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="lg">Employment</flux:heading>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div><dt class="text-sm text-zinc-500">Branch</dt><dd>{{ $employee->branch?->name ?? '—' }}</dd></div>
                <div><dt class="text-sm text-zinc-500">Employment type</dt><dd>{{ $employee->employmentType?->name ?? '—' }}</dd></div>
                <div><dt class="text-sm text-zinc-500">Hire date</dt><dd>{{ $employee->hire_date?->format('d/m/Y') ?? '—' }}</dd></div>
                <div><dt class="text-sm text-zinc-500">Contract ends</dt><dd>{{ $employee->contract_end_date?->format('d/m/Y') ?? '—' }}</dd></div>
            </dl>
        </flux:card>
    </div>

    @can('employee.view-sensitive')
        <flux:card class="space-y-4">
            <flux:heading size="lg">Sensitive employment information</flux:heading>
            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div><dt class="text-sm text-zinc-500">Salary</dt><dd>{{ $employee->base_salary ? number_format((float) $employee->base_salary, 2).' '.$employee->salary_currency : '—' }}</dd></div>
                <div><dt class="text-sm text-zinc-500">Bank</dt><dd>{{ $employee->bank_name ?: '—' }}</dd></div>
                <div><dt class="text-sm text-zinc-500">Account holder</dt><dd>{{ $employee->bank_account_name ?: '—' }}</dd></div>
            </dl>
        </flux:card>
    @endcan
</div>
