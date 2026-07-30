<?php

use App\Models\EmployeeGoal;
use App\Services\EmployeeGoalService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('គោលដៅរបស់ខ្ញុំ')] class extends Component
{
    public array $values = [];
    public array $notes = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->employee && auth()->user()?->can('performance.view-own'), 403);
    }

    #[Computed]
    public function goals()
    {
        return EmployeeGoal::query()->where('employee_id', auth()->user()->employee->id)
            ->whereNotIn('status', ['draft', 'cancelled'])->latest('due_date')->get();
    }

    public function submit(int $id, EmployeeGoalService $service): void
    {
        $this->validate(["values.$id" => ['required','numeric','min:0'], "notes.$id" => ['nullable','string','max:1000']]);
        try {
            $service->submitProgress(EmployeeGoal::findOrFail($id), auth()->user(), (float)$this->values[$id], $this->notes[$id] ?? null);
        } catch (\DomainException $e) {
            $this->addError("values.$id", $e->getMessage()); return;
        }
        unset($this->values[$id], $this->notes[$id], $this->goals);
        session()->flash('success', 'បានបញ្ជូនវឌ្ឍនភាពទៅអ្នកគ្រប់គ្រង។');
    }
};
?>
<div class="space-y-6">
    <div><flux:heading size="xl">គោលដៅរបស់ខ្ញុំ</flux:heading><flux:subheading>តាមដានគោលដៅ និងបញ្ជូនលទ្ធផលជាក់ស្តែងសម្រាប់ការពិនិត្យ</flux:subheading></div>
    @if(session('success'))<div class="rounded-xl border border-green-200 bg-green-50 p-4 text-green-800">{{ session('success') }}</div>@endif
    <div class="grid gap-4 xl:grid-cols-2">@forelse($this->goals as $goal)
        <article class="space-y-4 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-start justify-between gap-3"><div><flux:heading size="lg">{{ $goal->title }}</flux:heading><div class="mt-1 text-sm text-zinc-500">{{ $goal->start_date->format('d/m/Y') }} – {{ $goal->due_date->format('d/m/Y') }}</div></div><flux:badge>{{ $goal->status }}</flux:badge></div>
            <div class="grid grid-cols-2 gap-3 rounded-xl bg-zinc-50 p-4 dark:bg-zinc-800/60"><div><div class="text-xs text-zinc-500">គោលដៅ</div><div class="font-semibold">{{ number_format($goal->target_value,2) }}</div></div><div><div class="text-xs text-zinc-500">បានទទួលស្គាល់</div><div class="font-semibold">{{ number_format($goal->current_value,2) }}</div></div></div>
            @if(in_array($goal->status,['active','returned']))<div class="grid gap-3 sm:grid-cols-2"><flux:input wire:model="values.{{ $goal->id }}" type="number" step="0.01" min="0" label="លទ្ធផលជាក់ស្តែង *" /><flux:input wire:model="notes.{{ $goal->id }}" label="កំណត់សម្គាល់" /><div class="sm:col-span-2 flex justify-end"><flux:button variant="primary" icon="paper-airplane" wire:click="submit({{ $goal->id }})">បញ្ជូនពិនិត្យ</flux:button></div></div>@endif
            @if($goal->manager_note)<div class="text-sm text-zinc-500">មតិអ្នកគ្រប់គ្រង: {{ $goal->manager_note }}</div>@endif
        </article>
    @empty<div class="rounded-2xl border border-dashed border-zinc-300 p-12 text-center text-zinc-500 dark:border-zinc-700 xl:col-span-2">មិនទាន់មានគោលដៅសម្រាប់អ្នកទេ។</div>@endforelse</div>
</div>
