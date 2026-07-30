<?php

use App\Models\Attendance;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('ពិនិត្យប្រាក់ខែ និងម៉ោងបន្ថែម')] class extends Component
{
    use WithPagination;

    public string $tab = 'overtime';
    public string $overtimeStatus = 'pending';
    public string $periodId = '';
    public array $reviewNotes = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('payroll.approve'), 403);
    }

    public function updated($property): void
    {
        if (in_array($property, ['tab', 'overtimeStatus', 'periodId'], true)) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function overtimeRows()
    {
        $user = auth()->user();
        $actor = $user?->employee;

        return Attendance::query()
            ->with(['employee.branch', 'employee.department'])
            ->where('overtime_minutes', '>', 0)
            ->when($this->overtimeStatus, fn ($query) => $query->where('overtime_review_status', $this->overtimeStatus))
            ->when($user?->hasRole('Manager'), fn ($query) => $query->whereHas('employee', fn (Builder $employee) => $employee
                ->where('company_id', $actor?->company_id)
                ->where('department_id', $actor?->department_id)
                ->where('id', '!=', $actor?->id)))
            ->when($actor && ! $user?->hasRole('Manager'), fn ($query) => $query->whereHas('employee', fn (Builder $employee) => $employee
                ->where('company_id', $actor->company_id)))
            ->latest('work_date')
            ->paginate(15);
    }

    #[Computed]
    public function periods()
    {
        return PayrollPeriod::query()->latest('start_date')->limit(24)->get();
    }

    #[Computed]
    public function exceptionItems()
    {
        return PayrollItem::query()
            ->with(['employee', 'period'])
            ->where('exception_count', '>', 0)
            ->when($this->periodId, fn ($query) => $query->where('payroll_period_id', $this->periodId))
            ->latest('id')
            ->paginate(15);
    }

    public function approveOvertime(int $attendanceId): void
    {
        $attendance = $this->authorizedAttendance($attendanceId);
        $attendance->forceFill([
            'overtime_approved' => true,
            'overtime_review_status' => 'approved',
            'overtime_review_note' => $this->reviewNotes[$attendanceId] ?? null,
            'overtime_approved_by' => auth()->id(),
            'overtime_approved_at' => now(),
        ])->save();

        unset($this->overtimeRows);
        session()->flash('success', 'បានអនុម័តម៉ោងបន្ថែម។ សូមគណនាប្រាក់ខែឡើងវិញ។');
    }

    public function rejectOvertime(int $attendanceId): void
    {
        $note = trim($this->reviewNotes[$attendanceId] ?? '');
        if (mb_strlen($note) < 3) {
            $this->addError("reviewNotes.{$attendanceId}", 'សូមបញ្ចូលមូលហេតុយ៉ាងតិច 3 តួអក្សរ។');
            return;
        }

        $attendance = $this->authorizedAttendance($attendanceId);
        $attendance->forceFill([
            'overtime_approved' => false,
            'overtime_review_status' => 'rejected',
            'overtime_review_note' => $note,
            'overtime_approved_by' => auth()->id(),
            'overtime_approved_at' => now(),
        ])->save();

        unset($this->overtimeRows);
        session()->flash('success', 'បានបដិសេធម៉ោងបន្ថែម។');
    }

    private function authorizedAttendance(int $attendanceId): Attendance
    {
        abort_unless(auth()->user()?->can('payroll.approve'), 403);
        $attendance = Attendance::query()->with('employee')->findOrFail($attendanceId);
        $user = auth()->user();
        $actor = $user->employee;

        if ($actor) {
            abort_unless($attendance->employee->company_id === $actor->company_id, 403);
        }

        if ($user->hasRole('Manager')) {
            abort_unless($actor && $attendance->employee->department_id === $actor->department_id, 403);
            abort_if($attendance->employee_id === $actor->id, 403);
        }

        return $attendance;
    }
};
?>

<div class="space-y-6">
    <div>
        <flux:heading size="xl">ពិនិត្យប្រាក់ខែ និងម៉ោងបន្ថែម</flux:heading>
        <flux:subheading>ដោះស្រាយម៉ោងបន្ថែម និងទិន្នន័យដែលរារាំងការអនុម័តប្រាក់ខែ</flux:subheading>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap gap-2">
        <flux:button :variant="$tab === 'overtime' ? 'primary' : 'ghost'" wire:click="$set('tab', 'overtime')">អនុម័តម៉ោងបន្ថែម</flux:button>
        <flux:button :variant="$tab === 'exceptions' ? 'primary' : 'ghost'" wire:click="$set('tab', 'exceptions')">បញ្ហាការគណនាប្រាក់ខែ</flux:button>
    </div>

    @if ($tab === 'overtime')
        <div class="max-w-xs"><flux:select wire:model.live="overtimeStatus"><option value="pending">កំពុងរង់ចាំ</option><option value="approved">បានអនុម័ត</option><option value="rejected">បានបដិសេធ</option><option value="">ទាំងអស់</option></flux:select></div>
        <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 text-left text-xs text-zinc-500 dark:bg-zinc-800/60"><tr><th class="px-5 py-3">បុគ្គលិក</th><th class="px-5 py-3">ថ្ងៃ</th><th class="px-5 py-3">ម៉ោងបន្ថែម</th><th class="px-5 py-3">កំណត់សម្គាល់</th><th class="px-5 py-3"></th></tr></thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($this->overtimeRows as $row)
                            <tr wire:key="ot-{{ $row->id }}">
                                <td class="px-5 py-4"><div class="font-medium">{{ $row->employee?->full_name_km ?: $row->employee?->full_name_en }}</div><div class="text-xs text-zinc-500">{{ $row->employee?->department?->name }}</div></td>
                                <td class="px-5 py-4">{{ $row->work_date->format('d/m/Y') }}</td>
                                <td class="px-5 py-4 font-medium">{{ number_format($row->overtime_minutes / 60, 2) }} ម៉ោង</td>
                                <td class="min-w-64 px-5 py-4"><flux:input wire:model="reviewNotes.{{ $row->id }}" placeholder="កំណត់សម្គាល់..." /></td>
                                <td class="whitespace-nowrap px-5 py-4 text-right">
                                    @if ($row->overtime_review_status === 'pending')
                                        <flux:button size="sm" variant="primary" icon="check" wire:click="approveOvertime({{ $row->id }})">អនុម័ត</flux:button>
                                        <flux:button size="sm" variant="danger" icon="x-mark" wire:click="rejectOvertime({{ $row->id }})">បដិសេធ</flux:button>
                                    @else
                                        <flux:badge>{{ $row->overtime_review_status }}</flux:badge>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-12 text-center text-zinc-500">មិនមានម៉ោងបន្ថែមត្រូវពិនិត្យទេ។</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($this->overtimeRows->hasPages())<div class="border-t border-zinc-200 p-4 dark:border-zinc-700">{{ $this->overtimeRows->links() }}</div>@endif
        </div>
    @else
        <div class="max-w-sm"><flux:select wire:model.live="periodId"><option value="">គ្រប់វគ្គប្រាក់ខែ</option>@foreach ($this->periods as $period)<option value="{{ $period->id }}">{{ $period->name }}</option>@endforeach</flux:select></div>
        <div class="grid gap-4">
            @forelse ($this->exceptionItems as $item)
                <div wire:key="exception-{{ $item->id }}" class="rounded-2xl border border-red-200 bg-white p-5 dark:border-red-900 dark:bg-zinc-900">
                    <div class="flex flex-col gap-2 sm:flex-row sm:justify-between"><div><div class="font-semibold">{{ $item->employee?->full_name_km ?: $item->employee?->full_name_en }}</div><div class="text-sm text-zinc-500">{{ $item->period?->name }}</div></div><flux:badge color="red">{{ $item->exception_count }} បញ្ហា</flux:badge></div>
                    <ul class="mt-4 list-disc space-y-1 ps-5 text-sm text-red-700 dark:text-red-300">@foreach (($item->calculation_details['exceptions'] ?? []) as $exception)<li>{{ $exception }}</li>@endforeach</ul>
                    <p class="mt-4 text-xs text-zinc-500">កែតម្រូវកាលវិភាគ វត្តមាន ឬម៉ោងបន្ថែម រួចគណនាវគ្គប្រាក់ខែឡើងវិញ។</p>
                </div>
            @empty
                <div class="rounded-2xl border border-zinc-200 bg-white p-10 text-center text-zinc-500 dark:border-zinc-700 dark:bg-zinc-900">មិនមានបញ្ហាការគណនាទេ។</div>
            @endforelse
            @if ($this->exceptionItems->hasPages()){{ $this->exceptionItems->links() }}@endif
        </div>
    @endif
</div>
