<?php

use App\Models\Company;
use App\Models\PayrollSetting;
use App\Models\PublicHoliday;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('គោលការណ៍ប្រាក់ខែ')] class extends Component
{
    use WithPagination;

    public int $companyId;
    public string $khrPerUsd = '4000';
    public int $workingDaysPerMonth = 26;
    public string $hoursPerDay = '8';
    public string $defaultOvertimeMultiplier = '1.5';
    public bool $requireOvertimeApproval = true;
    public bool $deductUnpaidAbsence = true;
    public string $holidayName = '';
    public string $holidayDate = '';
    public bool $holidayIsPaid = true;
    public string $holidayNotes = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('payroll.view'), 403);
        $this->companyId = (int) Company::query()->value('id');
        abort_unless($this->companyId, 422, 'សូមបង្កើតព័ត៌មានក្រុមហ៊ុនជាមុន។');

        $settings = PayrollSetting::forCompany($this->companyId);
        $this->khrPerUsd = (string) $settings->khr_per_usd;
        $this->workingDaysPerMonth = $settings->working_days_per_month;
        $this->hoursPerDay = (string) $settings->hours_per_day;
        $this->defaultOvertimeMultiplier = (string) $settings->default_overtime_multiplier;
        $this->requireOvertimeApproval = $settings->require_overtime_approval;
        $this->deductUnpaidAbsence = $settings->deduct_unpaid_absence;
    }

    public function saveSettings(): void
    {
        abort_unless(auth()->user()?->can('payroll.approve'), 403);

        $data = $this->validate([
            'khrPerUsd' => ['required', 'numeric', 'min:1', 'max:100000'],
            'workingDaysPerMonth' => ['required', 'integer', 'min:1', 'max:31'],
            'hoursPerDay' => ['required', 'numeric', 'min:0.25', 'max:24'],
            'defaultOvertimeMultiplier' => ['required', 'numeric', 'min:1', 'max:10'],
            'requireOvertimeApproval' => ['boolean'],
            'deductUnpaidAbsence' => ['boolean'],
        ]);

        PayrollSetting::query()->updateOrCreate(
            ['company_id' => $this->companyId],
            [
                'khr_per_usd' => $data['khrPerUsd'],
                'working_days_per_month' => $data['workingDaysPerMonth'],
                'hours_per_day' => $data['hoursPerDay'],
                'default_overtime_multiplier' => $data['defaultOvertimeMultiplier'],
                'require_overtime_approval' => $data['requireOvertimeApproval'],
                'deduct_unpaid_absence' => $data['deductUnpaidAbsence'],
                'updated_by' => auth()->id(),
            ]
        );

        session()->flash('success', 'បានរក្សាទុកគោលការណ៍ប្រាក់ខែ។');
    }

    public function addHoliday(): void
    {
        abort_unless(auth()->user()?->canAny(['payroll.approve', 'leave.manage']), 403);

        $data = $this->validate([
            'holidayName' => ['required', 'string', 'max:150'],
            'holidayDate' => ['required', 'date'],
            'holidayIsPaid' => ['boolean'],
            'holidayNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        PublicHoliday::query()->updateOrCreate(
            ['company_id' => $this->companyId, 'holiday_date' => $data['holidayDate']],
            ['name' => trim($data['holidayName']), 'is_paid' => $data['holidayIsPaid'], 'notes' => $data['holidayNotes'] ?: null]
        );

        $this->reset('holidayName', 'holidayDate', 'holidayNotes');
        $this->holidayIsPaid = true;
        session()->flash('success', 'បានរក្សាទុកថ្ងៃឈប់សម្រាកសាធារណៈ។');
        unset($this->holidays);
    }

    public function deleteHoliday(int $holidayId): void
    {
        abort_unless(auth()->user()?->canAny(['payroll.approve', 'leave.manage']), 403);
        PublicHoliday::query()->where('company_id', $this->companyId)->findOrFail($holidayId)->delete();
        session()->flash('success', 'បានលុបថ្ងៃឈប់សម្រាកសាធារណៈ។');
        unset($this->holidays);
    }

    #[Computed]
    public function holidays()
    {
        return PublicHoliday::query()
            ->where('company_id', $this->companyId)
            ->orderByDesc('holiday_date')
            ->paginate(12);
    }
};
?>

<div class="space-y-6">
    <div>
        <flux:heading size="xl">គោលការណ៍ប្រាក់ខែ</flux:heading>
        <flux:subheading>កំណត់អត្រាប្តូរប្រាក់ ពេលធ្វើការ ម៉ោងបន្ថែម និងថ្ងៃឈប់សម្រាក</flux:subheading>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit="saveSettings" class="grid gap-4 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:grid-cols-2 xl:grid-cols-4">
        <flux:input wire:model="khrPerUsd" type="number" step="0.01" label="អត្រាប្តូរ 1 USD ជា KHR" />
        <flux:input wire:model="workingDaysPerMonth" type="number" label="ថ្ងៃធ្វើការស្តង់ដារក្នុងមួយខែ" />
        <flux:input wire:model="hoursPerDay" type="number" step="0.25" label="ម៉ោងធ្វើការក្នុងមួយថ្ងៃ" />
        <flux:input wire:model="defaultOvertimeMultiplier" type="number" step="0.01" label="មេគុណម៉ោងបន្ថែម" />
        <flux:checkbox wire:model="requireOvertimeApproval" label="ម៉ោងបន្ថែមត្រូវការអនុម័ត" />
        <flux:checkbox wire:model="deductUnpaidAbsence" label="កាត់ប្រាក់ពេលអវត្តមាន/ឈប់គ្មានប្រាក់" />
        <div class="flex items-end sm:col-span-2">
            <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                រក្សាទុកគោលការណ៍
            </flux:button>
        </div>
    </form>

    <div class="grid gap-6 xl:grid-cols-[380px_1fr]">
        <form wire:submit="addHoliday" class="space-y-4 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:heading size="lg">បន្ថែមថ្ងៃឈប់សម្រាក</flux:heading>
            <flux:input wire:model="holidayName" label="ឈ្មោះថ្ងៃឈប់សម្រាក" />
            <flux:input wire:model="holidayDate" type="date" label="កាលបរិច្ឆេទ" />
            <flux:checkbox wire:model="holidayIsPaid" label="ថ្ងៃឈប់សម្រាកមានប្រាក់ឈ្នួល" />
            <flux:textarea wire:model="holidayNotes" label="កំណត់សម្គាល់" rows="2" />
            <flux:button type="submit" variant="primary" class="w-full">រក្សាទុក</flux:button>
        </form>

        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 text-left text-xs text-zinc-500 dark:bg-zinc-800/60">
                        <tr><th class="px-5 py-3">កាលបរិច្ឆេទ</th><th class="px-5 py-3">ឈ្មោះ</th><th class="px-5 py-3">ប្រភេទ</th><th class="px-5 py-3"></th></tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($this->holidays as $holiday)
                            <tr wire:key="holiday-{{ $holiday->id }}">
                                <td class="px-5 py-4">{{ $holiday->holiday_date->format('d/m/Y') }}</td>
                                <td class="px-5 py-4 font-medium">{{ $holiday->name }}</td>
                                <td class="px-5 py-4"><flux:badge :color="$holiday->is_paid ? 'green' : 'zinc'">{{ $holiday->is_paid ? 'មានប្រាក់ឈ្នួល' : 'គ្មានប្រាក់ឈ្នួល' }}</flux:badge></td>
                                <td class="px-5 py-4 text-right"><flux:button size="sm" variant="danger" icon="trash" wire:click="deleteHoliday({{ $holiday->id }})" wire:confirm="តើអ្នកប្រាកដថាចង់លុបមែនទេ?" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-5 py-10 text-center text-zinc-500">មិនទាន់មានថ្ងៃឈប់សម្រាកទេ។</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($this->holidays->hasPages())
                <div class="border-t border-zinc-200 p-4 dark:border-zinc-700">{{ $this->holidays->links() }}</div>
            @endif
        </div>
    </div>
</div>
