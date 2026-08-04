<?php

use App\Models\Company;
use App\Models\PayrollPeriod;
use App\Models\PayrollSetting;
use App\Services\PayrollCalculatorService;
use App\Services\PayrollWorkflowService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('បញ្ជីប្រាក់ខែ')] class extends Component
{
    use WithPagination;

    public string $name = '';
    public string $startDate = '';
    public string $endDate = '';
    public string $paymentDate = '';
    public string $notes = '';
    public string $search = '';
    public string $status = '';
    public bool $showForm = false;
    public float $exchangeRate = 4000;
    public ?int $paymentPeriodId = null;
    public string $paymentMethod = 'bank_transfer';
    public string $paymentReference = '';
    public string $actualPaidAt = '';
    public string $paymentNotes = '';
    public string $taxExchangeRate = '';
    public string $taxRateDate = '';
    public string $taxRateSource = 'https://www.tax.gov.kh/en/exchange-rate';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('payroll.view'), 403);
        $companyId = Company::query()->value('id');
        if ($companyId) {
            $this->exchangeRate = (float) PayrollSetting::forCompany($companyId)->khr_per_usd;
        }
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->endOfMonth()->toDateString();
        $this->paymentDate = now()->endOfMonth()->toDateString();
        $this->name = 'ប្រាក់ខែ '.now()->format('m/Y');
        $this->actualPaidAt = now()->format('Y-m-d\TH:i');
        $this->taxExchangeRate = (string) $this->exchangeRate;
        $this->taxRateDate = now()->toDateString();
    }

    #[Computed]
    public function periods()
    {
        return PayrollPeriod::query()
            ->withCount('items')
            ->withSum(['items as net_salary_usd' => fn ($query) => $query->where('currency', 'USD')], 'net_salary')
            ->withSum(['items as net_salary_khr' => fn ($query) => $query->where('currency', 'KHR')], 'net_salary')
            ->withSum('items', 'exception_count')
            ->when($this->search, fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->latest('start_date')
            ->paginate(12);
    }

    public function updatedSearch(): void { $this->resetPage(); }
    public function updatedStatus(): void { $this->resetPage(); }

    public function createPeriod(): void
    {
        abort_unless(auth()->user()?->can('payroll.edit'), 403);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:150'],
            'startDate' => ['required', 'date'],
            'endDate' => ['required', 'date', 'after_or_equal:startDate'],
            'paymentDate' => ['nullable', 'date', 'after_or_equal:endDate'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'taxExchangeRate' => ['required', 'numeric', 'min:1', 'max:100000'],
            'taxRateDate' => ['required', 'date'],
            'taxRateSource' => ['required', 'url', 'max:255'],
        ], [
            'name.required' => 'សូមបញ្ចូលឈ្មោះវគ្គប្រាក់ខែ។',
            'endDate.after_or_equal' => 'ថ្ងៃបញ្ចប់ត្រូវនៅក្រោយថ្ងៃចាប់ផ្ដើម។',
            'paymentDate.after_or_equal' => 'ថ្ងៃបើកប្រាក់ត្រូវនៅក្រោយថ្ងៃបញ្ចប់។',
        ]);

        $companyId = Company::query()->value('id');
        abort_unless($companyId, 422, 'សូមបង្កើតព័ត៌មានក្រុមហ៊ុនជាមុន។');

        $overlaps = PayrollPeriod::query()
            ->where('company_id', $companyId)
            ->whereDate('start_date', '<=', $data['endDate'])
            ->whereDate('end_date', '>=', $data['startDate'])
            ->exists();

        if ($overlaps) {
            $this->addError('startDate', 'មានវគ្គប្រាក់ខែជាន់គ្នាជាមួយកាលបរិច្ឆេទនេះរួចហើយ។');
            return;
        }

        PayrollPeriod::query()->create([
            'company_id' => $companyId,
            'name' => $data['name'],
            'start_date' => $data['startDate'],
            'end_date' => $data['endDate'],
            'payment_date' => $data['paymentDate'] ?: null,
            'notes' => $data['notes'] ?: null,
            'tax_exchange_rate_khr' => $data['taxExchangeRate'],
            'tax_rate_date' => $data['taxRateDate'],
            'tax_rate_source' => $data['taxRateSource'],
        ]);

        $this->showForm = false;
        session()->flash('success', 'បានបង្កើតវគ្គប្រាក់ខែដោយជោគជ័យ។');
        unset($this->periods);
    }

    public function generatePayroll(int $periodId, PayrollCalculatorService $calculator): void
    {
        abort_unless(auth()->user()?->can('payroll.process'), 403);
        $period = PayrollPeriod::query()->findOrFail($periodId);
        abort_if(in_array($period->status, ['approved', 'paid', 'closed']), 422, 'វគ្គនេះមិនអាចគណនាឡើងវិញបានទេ។');
        abort_unless((float) $period->tax_exchange_rate_khr > 0 && $period->tax_rate_date && $period->tax_rate_source, 422, 'សូមកំណត់អត្រាប្តូរប្រាក់ផ្លូវការរបស់ GDT មុនគណនា។');
        $period->update(['status' => 'processing', 'processed_by' => auth()->id(), 'processed_at' => now(), 'tax_rate_locked_at' => $period->tax_rate_locked_at ?: now()]);
        $count = $calculator->generate($period);
        $period->update(['status' => 'awaiting_approval']);
        session()->flash('success', "បានគណនាប្រាក់ខែបុគ្គលិក {$count} នាក់។");
        unset($this->periods);
    }

    public function approvePayroll(int $periodId, PayrollWorkflowService $workflow): void
    {
        abort_unless(auth()->user()?->can('payroll.approve'), 403);
        $workflow->approve(PayrollPeriod::query()->findOrFail($periodId), auth()->user());
        session()->flash('success', 'បានអនុម័តវគ្គប្រាក់ខែដោយជោគជ័យ។');
        unset($this->periods);
    }

    public function openPayment(int $periodId): void
    {
        abort_unless(auth()->user()?->can('payroll.process'), 403);
        abort_unless(PayrollPeriod::query()->whereKey($periodId)->where('status', 'approved')->exists(), 422);
        $this->paymentPeriodId = $periodId;
        $this->actualPaidAt = now()->format('Y-m-d\TH:i');
    }

    public function recordPayment(PayrollWorkflowService $workflow): void
    {
        abort_unless(auth()->user()?->can('payroll.process'), 403);
        $data = $this->validate([
            'paymentPeriodId' => ['required', 'integer', 'exists:payroll_periods,id'],
            'paymentMethod' => ['required', 'in:bank_transfer,cash,cheque,mobile_banking,other'],
            'paymentReference' => ['nullable', 'string', 'max:100', 'unique:payroll_payments,reference_number'],
            'actualPaidAt' => ['required', 'date'],
            'paymentNotes' => ['nullable', 'string', 'max:2000'],
        ]);
        $workflow->recordPayment(
            PayrollPeriod::query()->findOrFail($data['paymentPeriodId']),
            auth()->user(),
            [
                'payment_method' => $data['paymentMethod'],
                'reference_number' => trim($data['paymentReference']) ?: null,
                'paid_at' => $data['actualPaidAt'],
                'notes' => trim($data['paymentNotes']) ?: null,
            ],
        );
        $this->reset('paymentPeriodId', 'paymentReference', 'paymentNotes');
        session()->flash('success', 'បានកត់ត្រាការបើកប្រាក់ដោយជោគជ័យ។');
        unset($this->periods);
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">បញ្ជីប្រាក់ខែ</flux:heading>
            <flux:subheading>គ្រប់គ្រងវគ្គគណនា អនុម័ត និងបើកប្រាក់ខែ</flux:subheading>
        </div>
        @can('payroll.edit')
            <flux:button variant="primary" icon="plus" wire:click="$toggle('showForm')">បង្កើតវគ្គប្រាក់ខែ</flux:button>
        @endcan
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-green-800">{{ session('success') }}</div>
    @endif

    @if ($paymentPeriodId)
        <form wire:submit="recordPayment" class="grid gap-4 rounded-2xl border border-blue-200 bg-blue-50 p-5 dark:border-blue-800 dark:bg-blue-950/30 sm:grid-cols-2">
            <flux:heading size="lg" class="sm:col-span-2">កត់ត្រាការបើកប្រាក់</flux:heading>
            <flux:select wire:model="paymentMethod" label="មធ្យោបាយបើកប្រាក់">
                <option value="bank_transfer">ផ្ទេរតាមធនាគារ</option>
                <option value="cash">សាច់ប្រាក់</option>
                <option value="cheque">មូលប្បទានប័ត្រ</option>
                <option value="mobile_banking">ធនាគារតាមទូរស័ព្ទ</option>
                <option value="other">ផ្សេងៗ</option>
            </flux:select>
            <flux:input wire:model="actualPaidAt" type="datetime-local" label="ថ្ងៃ និងម៉ោងបើកប្រាក់ពិតប្រាកដ" />
            <flux:input wire:model="paymentReference" label="លេខយោងប្រតិបត្តិការ" />
            <flux:textarea wire:model="paymentNotes" label="កំណត់សម្គាល់" rows="2" />
            <div class="flex gap-2 sm:col-span-2">
                <flux:button type="submit" variant="primary">បញ្ជាក់ការបើកប្រាក់</flux:button>
                <flux:button type="button" wire:click="$set('paymentPeriodId', null)">បោះបង់</flux:button>
            </div>
        </form>
    @endif

    @if ($showForm)
        <form wire:submit="createPeriod" class="grid gap-4 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:grid-cols-2">
            <flux:input wire:model="name" label="ឈ្មោះវគ្គប្រាក់ខែ" />
            <flux:input wire:model="paymentDate" type="date" label="ថ្ងៃបើកប្រាក់" />
            <flux:input wire:model="startDate" type="date" label="ថ្ងៃចាប់ផ្ដើម" />
            <flux:input wire:model="endDate" type="date" label="ថ្ងៃបញ្ចប់" />
            <flux:input wire:model="taxExchangeRate" type="number" step="0.01" label="អត្រាផ្លូវការ GDT: 1 USD ជា KHR" />
            <flux:input wire:model="taxRateDate" type="date" label="កាលបរិច្ឆេទអត្រា GDT" />
            <div class="sm:col-span-2"><flux:input wire:model="taxRateSource" type="url" label="ប្រភពអត្រាប្តូរប្រាក់" /></div>
            <div class="sm:col-span-2"><flux:textarea wire:model="notes" label="កំណត់សម្គាល់" rows="2" /></div>
            <div class="flex justify-end gap-2 sm:col-span-2">
                <flux:button type="button" variant="ghost" wire:click="$set('showForm', false)">បោះបង់</flux:button>
                <flux:button type="submit" variant="primary">រក្សាទុក</flux:button>
            </div>
        </form>
    @endif

    <div class="grid gap-3 sm:grid-cols-[1fr_220px]">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="ស្វែងរកវគ្គប្រាក់ខែ..." />
        <flux:select wire:model.live="status">
            <option value="">ស្ថានភាពទាំងអស់</option>
            <option value="draft">ព្រាង</option>
            <option value="processing">កំពុងគណនា</option>
            <option value="awaiting_approval">រង់ចាំអនុម័ត</option>
            <option value="approved">បានអនុម័ត</option>
            <option value="paid">បានបើកប្រាក់</option>
            <option value="closed">បានបិទ</option>
        </flux:select>
    </div>

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                    <tr class="text-left text-sm">
                        <th class="px-5 py-3">វគ្គប្រាក់ខែ</th><th class="px-5 py-3">កាលបរិច្ឆេទ</th>
                        <th class="px-5 py-3">បុគ្គលិក</th><th class="px-5 py-3">សរុបសុទ្ធ (1$ = 4,000៛)</th><th class="px-5 py-3">ស្ថានភាព</th><th class="px-5 py-3 text-right">សកម្មភាព</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($this->periods as $period)
                        <tr>
                            <td class="px-5 py-4 font-medium">{{ $period->name }}</td>
                            <td class="px-5 py-4 text-sm">{{ $period->start_date->format('d/m/Y') }} – {{ $period->end_date->format('d/m/Y') }}</td>
                            <td class="px-5 py-4">{{ $period->items_count }}</td>
                            @php
                                $totalUsd = (float) ($period->net_salary_usd ?? 0);
                                $totalKhr = (float) ($period->net_salary_khr ?? 0);
                                $usdEquivalent = $totalUsd + ($totalKhr / max(1, $exchangeRate));
                            @endphp
                            <td class="px-5 py-4">
                                <div class="font-medium">${{ number_format($usdEquivalent, 2) }} <span class="text-xs text-zinc-500">សរុបបម្លែង</span></div>
                                <div class="mt-1 text-xs text-zinc-500">${{ number_format($totalUsd, 2) }} + {{ number_format($totalKhr, 0) }}៛</div>
                                @if ((int) $period->items_sum_exception_count > 0)
                                    <div class="mt-1 text-xs font-medium text-red-600">
                                        ត្រូវពិនិត្យ {{ (int) $period->items_sum_exception_count }} បញ្ហា
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4"><flux:badge>{{ $period->status }}</flux:badge></td>
                            <td class="px-5 py-4"><div class="flex justify-end gap-2">
                                @can('payroll.process')
                                    @if (in_array($period->status, ['draft', 'processing', 'awaiting_approval']))
                                        <flux:button size="sm" icon="calculator" wire:click="generatePayroll({{ $period->id }})" wire:confirm="តើអ្នកចង់គណនាប្រាក់ខែសម្រាប់វគ្គនេះមែនទេ?">គណនា</flux:button>
                                    @elseif ($period->status === 'approved')
                                        <flux:button size="sm" variant="primary" icon="banknotes" wire:click="openPayment({{ $period->id }})">កត់ត្រាបើកប្រាក់</flux:button>
                                    @endif
                                @endcan
                                @can('payroll.approve')
                                    @if ($period->status === 'awaiting_approval')
                                        <flux:button size="sm" variant="primary" icon="check" wire:click="approvePayroll({{ $period->id }})" wire:confirm="តើអ្នកចង់អនុម័តវគ្គប្រាក់ខែនេះមែនទេ?">អនុម័ត</flux:button>
                                    @endif
                                @endcan
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-zinc-500">មិនទាន់មានវគ្គប្រាក់ខែទេ។</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $this->periods->links() }}</div>
    </div>
</div>
