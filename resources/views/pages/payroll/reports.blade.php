<?php

use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('របាយការណ៍ប្រាក់ខែ')] class extends Component
{
    use WithPagination;

    public ?int $periodId = null;

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('payroll.report'), 403);
        $this->periodId = PayrollPeriod::query()
            ->whereIn('status', ['awaiting_approval', 'approved', 'paid', 'closed'])
            ->latest('end_date')
            ->value('id');
    }

    public function updatedPeriodId(): void
    {
        $this->resetPage();
        unset($this->summary, $this->items);
    }

    #[Computed]
    public function periods()
    {
        return PayrollPeriod::query()
            ->whereIn('status', ['awaiting_approval', 'approved', 'paid', 'closed'])
            ->latest('end_date')
            ->get();
    }

    #[Computed]
    public function summary(): array
    {
        if (! $this->periodId) {
            return [];
        }

        $items = PayrollItem::query()->where('payroll_period_id', $this->periodId);

        return [
            'employees' => (clone $items)->count(),
            'usd' => $this->currencySummary(clone $items, 'USD'),
            'khr' => $this->currencySummary(clone $items, 'KHR'),
        ];
    }

    #[Computed]
    public function items()
    {
        return PayrollItem::query()
            ->with(['employee', 'period'])
            ->where('payroll_period_id', $this->periodId)
            ->orderBy('employee_id')
            ->paginate(20);
    }

    public function exportCsv()
    {
        abort_unless(auth()->user()?->can('payroll.report'), 403);
        $period = PayrollPeriod::query()->findOrFail($this->periodId);
        $items = PayrollItem::query()
            ->with('employee')
            ->where('payroll_period_id', $period->id)
            ->orderBy('employee_id')
            ->get();

        return response()->streamDownload(function () use ($items): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'លេខកូដ', 'ឈ្មោះ', 'រូបិយប័ណ្ណ', 'ប្រាក់ខែគោល', 'ប្រាក់ខែគិតបាន',
                'ថែមម៉ោង', 'ប្រាក់បន្ថែម', 'កាត់កង', 'ពន្ធ', 'ប.ស.ស. បុគ្គលិក',
                'ប.ស.ស. និយោជក', 'អត្ថប្រយោជន៍បន្ថែម', 'ពន្ធអត្ថប្រយោជន៍បន្ថែម',
                'ចំណាយសរុបនិយោជក', 'ប្រាក់ខែសុទ្ធ', 'ស្ថានភាពបើកប្រាក់',
            ]);

            foreach ($items as $item) {
                $extra = (float) $item->allowance_amount + (float) $item->bonus_amount + (float) $item->commission_amount;
                fputcsv($output, [
                    $this->safeCsv($item->employee->employee_code),
                    $this->safeCsv($item->employee->getFullNameKm()),
                    $item->currency,
                    $item->base_salary,
                    $item->payable_base_amount,
                    $item->overtime_amount,
                    $extra,
                    $item->deduction_amount + $item->loan_deduction + $item->advance_deduction,
                    $item->tax_amount,
                    $item->nssf_employee_amount,
                    $item->nssf_employer_amount,
                    $item->fringe_benefit_amount,
                    $item->fringe_benefit_tax_amount,
                    $item->employer_total_cost,
                    $item->net_salary,
                    $item->payment_status,
                ]);
            }

            fclose($output);
        }, 'payroll-'.$period->end_date->format('Y-m').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function money(float|string|null $amount, string $currency): string
    {
        return strtoupper($currency) === 'KHR'
            ? number_format((float) $amount, 0).'៛'
            : '$'.number_format((float) $amount, 2);
    }

    private function currencySummary($query, string $currency): array
    {
        $query->where('currency', $currency);

        return [
            'net' => (float) (clone $query)->sum('net_salary'),
            'tax' => (float) (clone $query)->sum('tax_amount'),
            'employee_nssf' => (float) (clone $query)->sum('nssf_employee_amount'),
            'employer_nssf' => (float) (clone $query)->sum('nssf_employer_amount'),
            'employer_cost' => (float) (clone $query)->sum('employer_total_cost'),
            'fringe_benefit' => (float) (clone $query)->sum('fringe_benefit_amount'),
            'fringe_benefit_tax' => (float) (clone $query)->sum('fringe_benefit_tax_amount'),
        ];
    }

    private function safeCsv(?string $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <flux:heading size="xl">របាយការណ៍ប្រាក់ខែ</flux:heading>
            <flux:subheading>ពន្ធ ប.ស.ស. ប្រាក់ខែសុទ្ធ និងចំណាយសរុបរបស់និយោជក</flux:subheading>
        </div>
        @if ($periodId)
            <flux:button icon="arrow-down-tray" wire:click="exportCsv">ទាញយក CSV</flux:button>
        @endif
    </div>

    <flux:select wire:model.live="periodId" label="ជ្រើសរើសវគ្គប្រាក់ខែ">
        <option value="">ជ្រើសរើស...</option>
        @foreach ($this->periods as $period)
            <option value="{{ $period->id }}">{{ $period->name }} — {{ $period->status }}</option>
        @endforeach
    </flux:select>

    @if ($periodId && $this->summary)
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach (['USD' => $this->summary['usd'], 'KHR' => $this->summary['khr']] as $currency => $summary)
                <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="text-sm text-zinc-500">{{ $currency }}</div>
                    <div class="mt-2 text-2xl font-semibold">{{ $this->money($summary['net'], $currency) }}</div>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between"><dt>ពន្ធលើប្រាក់បៀវត្ស</dt><dd>{{ $this->money($summary['tax'], $currency) }}</dd></div>
                        <div class="flex justify-between"><dt>ប.ស.ស. បុគ្គលិក</dt><dd>{{ $this->money($summary['employee_nssf'], $currency) }}</dd></div>
                        <div class="flex justify-between"><dt>ប.ស.ស. និយោជក</dt><dd>{{ $this->money($summary['employer_nssf'], $currency) }}</dd></div>
                        <div class="flex justify-between"><dt>អត្ថប្រយោជន៍បន្ថែម</dt><dd>{{ $this->money($summary['fringe_benefit'], $currency) }}</dd></div>
                        <div class="flex justify-between"><dt>ពន្ធអត្ថប្រយោជន៍បន្ថែម</dt><dd>{{ $this->money($summary['fringe_benefit_tax'], $currency) }}</dd></div>
                        <div class="flex justify-between font-medium"><dt>ចំណាយសរុបនិយោជក</dt><dd>{{ $this->money($summary['employer_cost'], $currency) }}</dd></div>
                    </dl>
                </div>
            @endforeach
            <div class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                <div class="text-sm text-zinc-500">បុគ្គលិកក្នុងវគ្គ</div>
                <div class="mt-2 text-3xl font-semibold">{{ $this->summary['employees'] }} នាក់</div>
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 text-left text-xs text-zinc-500 dark:bg-zinc-800/60">
                        <tr><th class="px-5 py-3">បុគ្គលិក</th><th class="px-5 py-3">រូបិយប័ណ្ណ</th><th class="px-5 py-3">ពន្ធ</th><th class="px-5 py-3">ប.ស.ស.</th><th class="px-5 py-3">សុទ្ធ</th><th class="px-5 py-3">ចំណាយនិយោជក</th></tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach ($this->items as $item)
                            <tr wire:key="report-item-{{ $item->id }}">
                                <td class="px-5 py-4"><div class="font-medium">{{ $item->employee->getFullNameKm() }}</div><div class="text-xs text-zinc-500">{{ $item->employee->employee_code }}</div></td>
                                <td class="px-5 py-4">{{ $item->currency }}</td>
                                <td class="px-5 py-4">{{ $this->money($item->tax_amount, $item->currency) }}</td>
                                <td class="px-5 py-4">{{ $this->money($item->nssf_employee_amount, $item->currency) }}</td>
                                <td class="px-5 py-4 font-semibold">{{ $this->money($item->net_salary, $item->currency) }}</td>
                                <td class="px-5 py-4">{{ $this->money($item->employer_total_cost, $item->currency) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $this->items->links() }}</div>
        </div>
    @else
        <div class="rounded-2xl border border-dashed border-zinc-300 p-12 text-center text-zinc-500">មិនទាន់មានវគ្គប្រាក់ខែសម្រាប់របាយការណ៍ទេ។</div>
    @endif
</div>
