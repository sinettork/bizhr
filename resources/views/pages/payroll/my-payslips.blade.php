<?php

use App\Models\PayrollItem;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('ប្រាក់ខែរបស់ខ្ញុំ')] class extends Component
{
    use WithPagination;

    public string $year = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('payroll.view-own'), 403);
        abort_unless(auth()->user()?->employee, 403, 'គណនីនេះមិនទាន់ភ្ជាប់ជាមួយបុគ្គលិកទេ។');
        $this->year = now()->format('Y');
    }

    #[Computed]
    public function payslips()
    {
        $employeeId = auth()->user()->employee->id;

        return PayrollItem::query()
            ->with('period')
            ->where('employee_id', $employeeId)
            ->whereHas('period', function ($query): void {
                $query->whereIn('status', ['approved', 'paid', 'closed'])
                    ->when($this->year, fn ($period) => $period->whereYear('end_date', $this->year));
            })
            ->latest('id')
            ->paginate(12);
    }

    public function updatedYear(): void
    {
        $this->resetPage();
        unset($this->payslips);
    }

    public function money(float|string|null $amount, string $currency): string
    {
        $value = (float) $amount;

        return strtoupper($currency) === 'KHR'
            ? number_format($value, 0).'៛'
            : '$'.number_format($value, 2);
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="xl">ប្រាក់ខែរបស់ខ្ញុំ</flux:heading>
            <flux:subheading>មើលព័ត៌មានប្រាក់ខែផ្ទាល់ខ្លួនដែលបានអនុម័តរួច</flux:subheading>
        </div>
        <div class="w-full sm:w-44">
            <flux:input wire:model.live="year" type="number" min="2020" max="2100" label="ឆ្នាំ" />
        </div>
    </div>

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                    <tr class="text-left text-sm">
                        <th class="px-5 py-3">វគ្គប្រាក់ខែ</th>
                        <th class="px-5 py-3">ប្រាក់ខែគោល/គិតបាន</th>
                        <th class="px-5 py-3">ថែមម៉ោង</th>
                        <th class="px-5 py-3">ប្រាក់បន្ថែម</th>
                        <th class="px-5 py-3">កាត់កង</th>
                        <th class="px-5 py-3">ប្រាក់ខែសុទ្ធ</th>
                        <th class="px-5 py-3">ស្ថានភាព</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($this->payslips as $item)
                        @php
                            $currency = strtoupper($item->currency);
                            $extra = (float) $item->allowance_amount
                                + (float) $item->bonus_amount
                                + (float) $item->commission_amount;
                            $deductions = (float) $item->deduction_amount
                                + (float) $item->loan_deduction
                                + (float) $item->advance_deduction
                                + (float) $item->tax_amount
                                + (float) $item->nssf_employee_amount;
                        @endphp
                        <tr>
                            <td class="px-5 py-4">
                                <div class="font-medium">{{ $item->period->name }}</div>
                                <div class="mt-1 text-xs text-zinc-500">
                                    {{ $item->period->start_date->format('d/m/Y') }}
                                    –
                                    {{ $item->period->end_date->format('d/m/Y') }}
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div>{{ $this->money($item->base_salary, $currency) }}</div>
                                <div class="mt-1 text-xs text-zinc-500">
                                    គិតបាន {{ $this->money($item->payable_base_amount, $currency) }}
                                </div>
                                <div class="mt-1 text-xs text-zinc-500">
                                    ធ្វើការ {{ number_format((float) $item->worked_minutes / 60, 2) }}
                                    / {{ number_format((float) $item->scheduled_minutes / 60, 2) }} ម៉ោង
                                </div>
                            </td>
                            <td class="px-5 py-4">
                                <div>{{ $this->money($item->overtime_amount, $currency) }}</div>
                                <div class="text-xs text-zinc-500">{{ number_format((float) $item->overtime_hours, 2) }} ម៉ោង</div>
                            </td>
                            <td class="px-5 py-4 text-green-700 dark:text-green-400">
                                +{{ $this->money($extra, $currency) }}
                            </td>
                            <td class="px-5 py-4 text-red-700 dark:text-red-400">
                                -{{ $this->money($deductions, $currency) }}
                            </td>
                            <td class="px-5 py-4 text-lg font-semibold">
                                {{ $this->money($item->net_salary, $currency) }}
                            </td>
                            <td class="px-5 py-4">
                                <flux:badge :color="$item->payment_status === 'paid' ? 'green' : 'amber'">
                                    {{ $item->payment_status === 'paid' ? 'បានបើកប្រាក់' : 'មិនទាន់បើក' }}
                                </flux:badge>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-12 text-center text-zinc-500">
                                មិនទាន់មានបញ្ជីប្រាក់ខែដែលបានអនុម័តទេ។
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $this->payslips->links() }}</div>
    </div>
</div>
