<?php

use App\Models\Company;
use App\Models\LeaveBalance;
use App\Models\LeaveType;
use App\Services\LeaveBalanceService;
use Flux\Flux;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('សមតុល្យការឈប់សម្រាក')] class extends Component
{
    use WithPagination;

    public int $companyId = 0;
    public int $year;
    public string $search = '';
    public string $leaveTypeFilter = '';
    public ?int $editingBalanceId = null;
    public string $adjustmentDays = '0';
    public bool $showAdjustmentForm = false;

    public function mount(): void
    {
        abort_unless(
            auth()->user()?->can('leave.report')
            || auth()->user()?->can('leave.manage'),
            403
        );

        $this->companyId = Company::query()->firstOrFail()->id;
        $this->year = (int) now()->year;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->balances);
    }

    public function updatedYear(): void
    {
        $this->resetPage();
        unset($this->balances, $this->statistics);
    }

    public function updatedLeaveTypeFilter(): void
    {
        $this->resetPage();
        unset($this->balances, $this->statistics);
    }

    #[Computed]
    public function leaveTypes()
    {
        return LeaveType::query()
            ->where('company_id', $this->companyId)
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function balances()
    {
        return LeaveBalance::query()
            ->with([
                'employee:id,company_id,employee_code,full_name_km,full_name_en,first_name,last_name,branch_id,department_id',
                'employee.branch:id,name',
                'employee.department:id,name',
                'leaveType:id,name,code',
            ])
            ->where('year', $this->year)
            ->whereHas(
                'employee',
                fn ($query) => $query->where('company_id', $this->companyId)
            )
            ->when(
                filled($this->leaveTypeFilter),
                fn ($query) => $query->where(
                    'leave_type_id',
                    (int) $this->leaveTypeFilter
                )
            )
            ->when(
                filled($this->search),
                function ($query): void {
                    $search = trim($this->search);

                    $query->whereHas(
                        'employee',
                        fn ($employeeQuery) => $employeeQuery->where(
                            fn ($nameQuery) => $nameQuery
                                ->where('employee_code', 'like', "%{$search}%")
                                ->orWhere('full_name_km', 'like', "%{$search}%")
                                ->orWhere('full_name_en', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                        )
                    );
                }
            )
            ->orderBy('employee_id')
            ->orderBy('leave_type_id')
            ->paginate(20);
    }

    #[Computed]
    public function statistics(): array
    {
        $query = LeaveBalance::query()
            ->where('year', $this->year)
            ->whereHas(
                'employee',
                fn ($employeeQuery) => $employeeQuery->where(
                    'company_id',
                    $this->companyId
                )
            )
            ->when(
                filled($this->leaveTypeFilter),
                fn ($balanceQuery) => $balanceQuery->where(
                    'leave_type_id',
                    (int) $this->leaveTypeFilter
                )
            );

        return [
            'records' => (clone $query)->count(),
            'earned' => (float) (clone $query)->sum('earned_days'),
            'used' => (float) (clone $query)->sum('used_days'),
            'remaining' => (float) (clone $query)->sum('remaining_days'),
        ];
    }

    public function initializeYear(LeaveBalanceService $service): void
    {
        $this->authorizeManage();

        $created = $service->initializeYearForCompany(
            $this->companyId,
            $this->year
        );

        $this->refreshData();

        Flux::toast(
            variant: 'success',
            text: $created > 0
                ? "បានបង្កើតសមតុល្យថ្មី {$created} កំណត់ត្រា។"
                : 'សមតុល្យសម្រាប់ឆ្នាំនេះមានរួចហើយ។'
        );
    }

    public function synchronize(LeaveBalanceService $service): void
    {
        $this->authorizeManage();

        $count = $service->syncCompanyYear(
            $this->companyId,
            $this->year
        );

        $this->refreshData();

        Flux::toast(
            variant: 'success',
            text: "បានធ្វើបច្ចុប្បន្នភាពសមតុល្យ {$count} កំណត់ត្រា។"
        );
    }

    public function editAdjustment(int $balanceId): void
    {
        $this->authorizeManage();

        $balance = $this->findBalance($balanceId);

        $this->editingBalanceId = $balance->id;
        $this->adjustmentDays = (string) $balance->adjustment_days;
        $this->showAdjustmentForm = true;
        $this->resetValidation();
    }

    public function saveAdjustment(LeaveBalanceService $service): void
    {
        $this->authorizeManage();

        $validated = $this->validate([
            'adjustmentDays' => ['required', 'numeric', 'between:-365,365'],
        ], [
            'adjustmentDays.required' => 'សូមបញ្ចូលចំនួនថ្ងៃកែតម្រូវ។',
            'adjustmentDays.numeric' => 'ចំនួនថ្ងៃកែតម្រូវត្រូវតែជាលេខ។',
            'adjustmentDays.between' => 'ចំនួនថ្ងៃត្រូវស្ថិតនៅចន្លោះ -365 និង 365។',
        ]);

        $balance = $this->findBalance((int) $this->editingBalanceId);

        $service->setAdjustment(
            $balance,
            (float) $validated['adjustmentDays']
        );

        $this->cancelAdjustment();
        $this->refreshData();

        Flux::toast(
            variant: 'success',
            text: 'បានកែតម្រូវសមតុល្យដោយជោគជ័យ។'
        );
    }

    public function cancelAdjustment(): void
    {
        $this->editingBalanceId = null;
        $this->adjustmentDays = '0';
        $this->showAdjustmentForm = false;
        $this->resetValidation();
    }

    protected function findBalance(int $balanceId): LeaveBalance
    {
        return LeaveBalance::query()
            ->whereKey($balanceId)
            ->where('year', $this->year)
            ->whereHas(
                'employee',
                fn ($query) => $query->where('company_id', $this->companyId)
            )
            ->firstOrFail();
    }

    protected function authorizeManage(): void
    {
        abort_unless(auth()->user()?->can('leave.manage'), 403);
    }

    protected function refreshData(): void
    {
        unset($this->balances, $this->statistics);
        $this->resetPage();
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div>
            <flux:heading size="xl">សមតុល្យការឈប់សម្រាក</flux:heading>
            <flux:subheading class="mt-1">
                គ្រប់គ្រងថ្ងៃឈប់សម្រាកដែលទទួលបាន ប្រើប្រាស់ និងនៅសល់
            </flux:subheading>
        </div>

        @can('leave.manage')
            <div class="flex flex-wrap gap-2">
                <flux:button
                    type="button"
                    variant="ghost"
                    icon="arrow-path"
                    wire:click="synchronize"
                    wire:loading.attr="disabled"
                >
                    ធ្វើបច្ចុប្បន្នភាព
                </flux:button>

                <flux:button
                    type="button"
                    variant="primary"
                    icon="plus"
                    wire:click="initializeYear"
                    wire:loading.attr="disabled"
                >
                    បង្កើតសមតុល្យឆ្នាំ
                </flux:button>
            </div>
        @endcan
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['កំណត់ត្រា', $this->statistics['records'], 'blue'],
            ['ទទួលបាន', number_format($this->statistics['earned'], 1).' ថ្ងៃ', 'violet'],
            ['បានប្រើ', number_format($this->statistics['used'], 1).' ថ្ងៃ', 'amber'],
            ['នៅសល់', number_format($this->statistics['remaining'], 1).' ថ្ងៃ', 'green'],
        ] as [$label, $value, $color])
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-sm text-zinc-500 dark:text-zinc-400">{{ $label }}</div>
                <div class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">
                    {{ $value }}
                </div>
            </div>
        @endforeach
    </div>

    <div class="grid gap-4 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 md:grid-cols-3">
        <flux:input
            wire:model.live.debounce.400ms="search"
            label="ស្វែងរកបុគ្គលិក"
            placeholder="ឈ្មោះ ឬលេខកូដបុគ្គលិក"
            icon="magnifying-glass"
        />

        <flux:select wire:model.live="leaveTypeFilter" label="ប្រភេទច្បាប់">
            <option value="">គ្រប់ប្រភេទ</option>
            @foreach ($this->leaveTypes as $leaveType)
                <option value="{{ $leaveType->id }}">
                    {{ $leaveType->name }} ({{ $leaveType->code }})
                </option>
            @endforeach
        </flux:select>

        <flux:input
            wire:model.live="year"
            type="number"
            min="2000"
            max="2100"
            label="ឆ្នាំ"
        />
    </div>

    @if ($showAdjustmentForm)
        <form wire:submit="saveAdjustment" class="rounded-2xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-800 dark:bg-blue-950/30">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <flux:input
                        wire:model="adjustmentDays"
                        type="number"
                        step="0.5"
                        min="-365"
                        max="365"
                        label="កែតម្រូវចំនួនថ្ងៃ"
                        description="ប្រើលេខវិជ្ជមានដើម្បីបន្ថែម និងលេខអវិជ្ជមានដើម្បីកាត់"
                    />
                </div>

                <div class="flex gap-2">
                    <flux:button type="button" variant="ghost" wire:click="cancelAdjustment">
                        បោះបង់
                    </flux:button>
                    <flux:button type="submit" variant="primary">
                        រក្សាទុក
                    </flux:button>
                </div>
            </div>
        </form>
    @endif

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1100px] text-left text-sm">
                <thead class="bg-zinc-50 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200">
                    <tr>
                        <th class="px-5 py-3.5 font-medium">បុគ្គលិក</th>
                        <th class="px-5 py-3.5 font-medium">ប្រភេទច្បាប់</th>
                        <th class="px-5 py-3.5 text-right font-medium">ដើមឆ្នាំ</th>
                        <th class="px-5 py-3.5 text-right font-medium">ទទួលបាន</th>
                        <th class="px-5 py-3.5 text-right font-medium">បានប្រើ</th>
                        <th class="px-5 py-3.5 text-right font-medium">កែតម្រូវ</th>
                        <th class="px-5 py-3.5 text-right font-medium">នៅសល់</th>
                        @can('leave.manage')
                            <th class="px-5 py-3.5 text-right font-medium">សកម្មភាព</th>
                        @endcan
                    </tr>
                </thead>

                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->balances as $balance)
                        @php
                            $employeeName = $balance->employee->full_name_km
                                ?: $balance->employee->full_name_en
                                ?: trim($balance->employee->first_name.' '.$balance->employee->last_name);
                        @endphp

                        <tr wire:key="leave-balance-{{ $balance->id }}" class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="px-5 py-4">
                                <div class="font-medium text-zinc-900 dark:text-white">{{ $employeeName }}</div>
                                <div class="mt-1 text-xs text-zinc-500">
                                    {{ $balance->employee->employee_code }}
                                    @if ($balance->employee->branch)
                                        · {{ $balance->employee->branch->name }}
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="text-zinc-900 dark:text-white">{{ $balance->leaveType->name }}</div>
                                <div class="mt-1 font-mono text-xs text-zinc-500">{{ $balance->leaveType->code }}</div>
                            </td>
                            <td class="px-5 py-4 text-right">{{ number_format((float) $balance->opening_balance, 1) }}</td>
                            <td class="px-5 py-4 text-right">{{ number_format((float) $balance->earned_days, 1) }}</td>
                            <td class="px-5 py-4 text-right text-amber-600">{{ number_format((float) $balance->used_days, 1) }}</td>
                            <td class="px-5 py-4 text-right">{{ number_format((float) $balance->adjustment_days, 1) }}</td>
                            <td class="px-5 py-4 text-right">
                                <flux:badge :color="$balance->remaining_days > 0 ? 'green' : 'red'">
                                    {{ number_format((float) $balance->remaining_days, 1) }} ថ្ងៃ
                                </flux:badge>
                            </td>
                            @can('leave.manage')
                                <td class="px-5 py-4 text-right">
                                    <flux:button
                                        type="button"
                                        size="sm"
                                        variant="ghost"
                                        icon="pencil-square"
                                        square
                                        wire:click="editAdjustment({{ $balance->id }})"
                                        title="កែតម្រូវ"
                                    />
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-14 text-center">
                                <div class="font-medium text-zinc-700 dark:text-zinc-200">
                                    មិនទាន់មានសមតុល្យសម្រាប់ឆ្នាំ {{ $year }}
                                </div>
                                <p class="mt-2 text-sm text-zinc-500">
                                    ចុច “បង្កើតសមតុល្យឆ្នាំ” ដើម្បីបង្កើតកំណត់ត្រា។
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->balances->hasPages())
            <div class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-700">
                {{ $this->balances->links() }}
            </div>
        @endif
    </div>
</div>
