<?php

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeGoal;
use App\Models\KpiTemplateItem;
use App\Services\EmployeeGoalService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('គោលដៅបុគ្គលិក')] class extends Component
{
    use WithPagination;

    public bool $showForm = false;
    public string $search = '';
    public string $status = '';
    public string $employeeId = '';
    public string $templateItemId = '';
    public string $title = '';
    public string $description = '';
    public string $measurementUnit = 'number';
    public string $targetValue = '';
    public string $weight = '100';
    public string $scoringDirection = 'higher_is_better';
    public string $startDate = '';
    public string $dueDate = '';
    public array $reviewNotes = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('performance.view'), 403);
        $this->startDate = now()->toDateString();
    }

    #[Computed]
    public function goals()
    {
        return EmployeeGoal::query()->with('employee')
            ->when($this->search, function ($q): void {
                $term = '%'.$this->search.'%';
                $q->where(fn ($q) => $q->where('title', 'like', $term)
                    ->orWhereHas('employee', fn ($e) => $e->where('full_name_km', 'like', $term)
                        ->orWhere('full_name_en', 'like', $term)->orWhere('employee_code', 'like', $term)));
            })
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest('id')->paginate(15);
    }

    #[Computed] public function employees() { return Employee::query()->active()->orderBy('employee_code')->get(); }
    #[Computed] public function templateItems() { return KpiTemplateItem::query()->whereHas('template', fn ($q) => $q->where('is_active', true))->with('template')->orderBy('name')->get(); }

    public function updatedTemplateItemId($id): void
    {
        if (! $id || ! ($item = KpiTemplateItem::find($id))) return;
        $this->title = $item->name;
        $this->description = $item->description ?? '';
        $this->measurementUnit = $item->measurement_unit;
        $this->targetValue = (string) $item->target_value;
        $this->weight = (string) $item->weight;
        $this->scoringDirection = $item->scoring_direction;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('performance.create'), 403);
        $data = $this->validate([
            'employeeId' => ['required', 'exists:employees,id'],
            'templateItemId' => ['nullable', 'exists:kpi_template_items,id'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'measurementUnit' => ['required', Rule::in(['number','percent','currency_usd','currency_khr','days','hours','score'])],
            'targetValue' => ['required', 'numeric', 'min:0'],
            'weight' => ['required', 'numeric', 'gt:0', 'max:100'],
            'scoringDirection' => ['required', Rule::in(['higher_is_better','lower_is_better','target_is_best'])],
            'startDate' => ['required', 'date'],
            'dueDate' => ['required', 'date', 'after_or_equal:startDate'],
        ]);
        $employee = Employee::findOrFail($data['employeeId']);
        EmployeeGoal::create([
            'company_id' => $employee->company_id, 'employee_id' => $employee->id,
            'kpi_template_item_id' => $data['templateItemId'] ?: null, 'title' => $data['title'],
            'description' => $data['description'] ?: null, 'measurement_unit' => $data['measurementUnit'],
            'target_value' => $data['targetValue'], 'weight' => $data['weight'],
            'scoring_direction' => $data['scoringDirection'], 'start_date' => $data['startDate'],
            'due_date' => $data['dueDate'], 'status' => 'active',
            'assigned_by' => auth()->id(), 'activated_at' => now(),
        ]);
        session()->flash('success', 'បានកំណត់គោលដៅជូនបុគ្គលិក។');
        $this->reset(['showForm','employeeId','templateItemId','title','description','targetValue','dueDate']);
        unset($this->goals);
    }

    public function review(int $id, bool $approved, EmployeeGoalService $service): void
    {
        abort_unless(auth()->user()->can('performance.review'), 403);
        try {
            $service->review(EmployeeGoal::findOrFail($id), auth()->user(), $approved, $this->reviewNotes[$id] ?? null);
        } catch (\DomainException $e) {
            $this->addError('review', $e->getMessage()); return;
        }
        unset($this->reviewNotes[$id], $this->goals);
        session()->flash('success', $approved ? 'បានទទួលស្គាល់វឌ្ឍនភាព។' : 'បានបញ្ជូនត្រឡប់ឱ្យកែប្រែ។');
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div><flux:heading size="xl">គោលដៅបុគ្គលិក</flux:heading><flux:subheading>កំណត់គោលដៅ តាមដានវឌ្ឍនភាព និងពិនិត្យលទ្ធផល</flux:subheading></div>
        @can('performance.create')<flux:button variant="primary" icon="plus" wire:click="$set('showForm', true)">កំណត់គោលដៅ</flux:button>@endcan
    </div>
    @if(session('success'))<div class="rounded-xl border border-green-200 bg-green-50 p-4 text-green-800">{{ session('success') }}</div>@endif
    @error('review')<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">{{ $message }}</div>@enderror

    @if($showForm)
        <form wire:submit="save" class="grid gap-4 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:grid-cols-2 xl:grid-cols-3">
            <flux:select wire:model="employeeId" label="បុគ្គលិក *"><option value="">ជ្រើសរើស</option>@foreach($this->employees as $e)<option value="{{ $e->id }}">{{ $e->employee_code }} — {{ $e->full_name_km ?: $e->full_name_en }}</option>@endforeach</flux:select>
            <flux:select wire:model.live="templateItemId" label="យកពីគំរូ KPI"><option value="">កំណត់ដោយខ្លួនឯង</option>@foreach($this->templateItems as $i)<option value="{{ $i->id }}">{{ $i->template->name }} — {{ $i->name }}</option>@endforeach</flux:select>
            <flux:input wire:model="title" label="ឈ្មោះគោលដៅ *" />
            <flux:select wire:model="measurementUnit" label="ឯកតាវាស់ *"><option value="number">ចំនួន</option><option value="percent">ភាគរយ</option><option value="currency_usd">USD</option><option value="currency_khr">KHR</option><option value="days">ថ្ងៃ</option><option value="hours">ម៉ោង</option><option value="score">ពិន្ទុ</option></flux:select>
            <flux:input wire:model="targetValue" type="number" step="0.01" min="0" label="គោលដៅ *" />
            <flux:input wire:model="weight" type="number" step="0.01" min="0.01" max="100" label="ទម្ងន់ % *" />
            <flux:select wire:model="scoringDirection" label="វិធីគណនា *"><option value="higher_is_better">ខ្ពស់ជាង កាន់តែល្អ</option><option value="lower_is_better">ទាបជាង កាន់តែល្អ</option><option value="target_is_best">ជិតគោលដៅ កាន់តែល្អ</option></flux:select>
            <flux:input wire:model="startDate" type="date" label="ថ្ងៃចាប់ផ្តើម *" />
            <flux:input wire:model="dueDate" type="date" label="ថ្ងៃកំណត់ *" />
            <div class="sm:col-span-2 xl:col-span-3"><flux:textarea wire:model="description" label="ពិពណ៌នា" rows="2" /></div>
            <div class="flex justify-end gap-2 sm:col-span-2 xl:col-span-3"><flux:button type="button" variant="ghost" wire:click="$set('showForm', false)">បោះបង់</flux:button><flux:button type="submit" variant="primary">រក្សាទុក</flux:button></div>
        </form>
    @endif

    <div class="grid gap-3 sm:grid-cols-[1fr_220px]"><flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="ស្វែងរកគោលដៅ ឬបុគ្គលិក..." /><flux:select wire:model.live="status"><option value="">ស្ថានភាពទាំងអស់</option><option value="active">កំពុងអនុវត្ត</option><option value="pending_review">រង់ចាំពិនិត្យ</option><option value="returned">ត្រូវកែប្រែ</option><option value="completed">បានសម្រេច</option></flux:select></div>
    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"><div class="overflow-x-auto"><table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
        <thead class="bg-zinc-50 dark:bg-zinc-800/60"><tr class="text-left text-sm"><th class="px-5 py-3">បុគ្គលិក / គោលដៅ</th><th class="px-5 py-3">រយៈពេល</th><th class="px-5 py-3">គោលដៅ / បច្ចុប្បន្ន</th><th class="px-5 py-3">ស្ថានភាព</th><th class="px-5 py-3 text-right">ពិនិត្យ</th></tr></thead>
        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">@forelse($this->goals as $goal)<tr>
            <td class="px-5 py-4"><div class="font-medium">{{ $goal->title }}</div><div class="text-xs text-zinc-500">{{ $goal->employee->employee_code }} — {{ $goal->employee->full_name_km ?: $goal->employee->full_name_en }}</div></td>
            <td class="px-5 py-4">{{ $goal->start_date->format('d/m/Y') }} – {{ $goal->due_date->format('d/m/Y') }}</td>
            <td class="px-5 py-4">{{ number_format($goal->current_value,2) }} / {{ number_format($goal->target_value,2) }} @if($goal->employee_reported_value !== null)<div class="text-xs text-amber-600">បានរាយការណ៍: {{ number_format($goal->employee_reported_value,2) }}</div>@endif</td>
            <td class="px-5 py-4"><flux:badge>{{ $goal->status }}</flux:badge></td>
            <td class="px-5 py-4">@if($goal->status==='pending_review')<div class="flex justify-end gap-2"><flux:input wire:model="reviewNotes.{{ $goal->id }}" size="sm" placeholder="កំណត់សម្គាល់" /><flux:button size="sm" variant="primary" icon="check" wire:click="review({{ $goal->id }}, true)">ទទួល</flux:button><flux:button size="sm" variant="danger" icon="x-mark" wire:click="review({{ $goal->id }}, false)">ត្រឡប់</flux:button></div>@endif</td>
        </tr>@empty<tr><td colspan="5" class="px-5 py-12 text-center text-zinc-500">មិនទាន់មានគោលដៅទេ។</td></tr>@endforelse</tbody>
    </table></div><div class="p-4">{{ $this->goals->links() }}</div></div>
</div>
