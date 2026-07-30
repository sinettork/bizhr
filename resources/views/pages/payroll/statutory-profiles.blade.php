<?php

use App\Models\Employee;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('ពន្ធ និង ប.ស.ស. បុគ្គលិក')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $employeeId = null;
    public bool $isTaxResident = true;
    public int $taxDependents = 0;
    public string $nssfNumber = '';
    public bool $nssfEnrolled = false;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function edit(int $employeeId): void
    {
        $employee = Employee::query()->findOrFail($employeeId);
        $this->employeeId = $employee->id;
        $this->isTaxResident = (bool) $employee->is_tax_resident;
        $this->taxDependents = (int) $employee->tax_dependents;
        $this->nssfNumber = (string) $employee->nssf_number;
        $this->nssfEnrolled = (bool) $employee->nssf_enrolled;
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('payroll.approve'), 403);

        $data = $this->validate([
            'employeeId' => ['required', 'integer', 'exists:employees,id'],
            'isTaxResident' => ['boolean'],
            'taxDependents' => ['required', 'integer', 'min:0', 'max:30'],
            'nssfNumber' => ['nullable', 'string', 'max:50'],
            'nssfEnrolled' => ['boolean'],
        ]);

        Employee::query()->findOrFail($data['employeeId'])->forceFill([
            'is_tax_resident' => $data['isTaxResident'],
            'tax_dependents' => $data['taxDependents'],
            'nssf_number' => trim($data['nssfNumber']) ?: null,
            'nssf_enrolled' => $data['nssfEnrolled'],
        ])->save();

        $this->employeeId = null;
        session()->flash('success', 'បានរក្សាទុកព័ត៌មានពន្ធ និង ប.ស.ស. របស់បុគ្គលិក។');
    }

    #[Computed]
    public function employees()
    {
        return Employee::query()
            ->when($this->search, fn ($query) => $query->where(fn ($q) => $q
                ->where('employee_code', 'like', "%{$this->search}%")
                ->orWhere('full_name_km', 'like', "%{$this->search}%")
                ->orWhere('full_name_en', 'like', "%{$this->search}%")))
            ->orderBy('employee_code')
            ->paginate(15);
    }
};
?>

<div class="space-y-6">
    <div>
        <flux:heading size="xl">ពន្ធ និង ប.ស.ស. បុគ្គលិក</flux:heading>
        <flux:subheading>កំណត់ស្ថានភាពពន្ធ អ្នកក្នុងបន្ទុក និងការចុះបញ្ជី ប.ស.ស. មុនបង្កើតប្រាក់ខែ</flux:subheading>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="ស្វែងរកតាមលេខកូដ ឬឈ្មោះ..." />

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 text-left text-xs text-zinc-500 dark:bg-zinc-800/60">
                    <tr>
                        <th class="px-5 py-3">បុគ្គលិក</th>
                        <th class="px-5 py-3">ពន្ធ</th>
                        <th class="px-5 py-3">អ្នកក្នុងបន្ទុក</th>
                        <th class="px-5 py-3">ប.ស.ស.</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($this->employees as $employee)
                        <tr wire:key="statutory-{{ $employee->id }}">
                            <td class="px-5 py-4">
                                <div class="font-medium">{{ $employee->getFullNameKm() }}</div>
                                <div class="text-xs text-zinc-500">{{ $employee->employee_code }}</div>
                            </td>
                            <td class="px-5 py-4">{{ $employee->is_tax_resident ? 'និវាសនជន' : 'អនិវាសនជន' }}</td>
                            <td class="px-5 py-4">{{ $employee->tax_dependents }}</td>
                            <td class="px-5 py-4">
                                <flux:badge :color="$employee->nssf_enrolled ? 'green' : 'zinc'">
                                    {{ $employee->nssf_enrolled ? ($employee->nssf_number ?: 'បានចុះបញ្ជី') : 'មិនទាន់ចុះបញ្ជី' }}
                                </flux:badge>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <flux:button size="sm" icon="pencil-square" wire:click="edit({{ $employee->id }})">កែប្រែ</flux:button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-zinc-500">រកមិនឃើញបុគ្គលិកទេ។</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($this->employees->hasPages())
            <div class="border-t border-zinc-200 p-4 dark:border-zinc-700">{{ $this->employees->links() }}</div>
        @endif
    </div>

    @if ($employeeId)
        <form wire:submit="save" class="grid gap-4 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:grid-cols-2">
            <flux:heading size="lg" class="sm:col-span-2">កែព័ត៌មានច្បាប់របស់បុគ្គលិក</flux:heading>
            <flux:checkbox wire:model="isTaxResident" label="ជានិវាសនជនសម្រាប់ពន្ធលើប្រាក់បៀវត្ស" />
            <flux:input wire:model="taxDependents" type="number" min="0" label="ចំនួនអ្នកក្នុងបន្ទុកស្របច្បាប់" />
            <flux:checkbox wire:model="nssfEnrolled" label="បានចុះបញ្ជី ប.ស.ស." />
            <flux:input wire:model="nssfNumber" label="លេខសមាជិក ប.ស.ស." />
            <div class="flex gap-2 sm:col-span-2">
                <flux:button type="submit" variant="primary">រក្សាទុក</flux:button>
                <flux:button type="button" wire:click="$set('employeeId', null)">បោះបង់</flux:button>
            </div>
        </form>
    @endif
</div>
