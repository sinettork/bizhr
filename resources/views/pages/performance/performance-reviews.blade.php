<?php

use App\Models\Employee;
use App\Models\PerformanceReview;
use App\Services\PerformanceReviewService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('ការវាយតម្លៃការងារ')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public bool $showCreate = false;
    public string $employeeId = '';
    public string $periodStart = '';
    public string $periodEnd = '';
    public ?int $editingId = null;
    public array $scores = [];
    public array $scoreComments = [];
    public string $strengths = '';
    public string $areasForImprovement = '';
    public string $managerComment = '';
    public array $reopenReasons = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('performance.view'), 403);
    }

    #[Computed]
    public function reviews()
    {
        return PerformanceReview::query()->with(['employee', 'reviewer', 'scores'])
            ->when($this->search, function ($q): void {
                $term = '%'.$this->search.'%';
                $q->whereHas('employee', fn ($e) => $e->where('full_name_km', 'like', $term)
                    ->orWhere('full_name_en', 'like', $term)->orWhere('employee_code', 'like', $term));
            })
            ->when($this->status, fn ($q) => $q->where('status', $this->status))
            ->latest('id')->paginate(15);
    }

    #[Computed]
    public function employees()
    {
        return Employee::query()->active()->orderBy('employee_code')->get();
    }

    public function createReview(PerformanceReviewService $service): void
    {
        abort_unless(auth()->user()->can('performance.create'), 403);
        $data = $this->validate([
            'employeeId' => ['required','exists:employees,id'],
            'periodStart' => ['required','date'],
            'periodEnd' => ['required','date','after_or_equal:periodStart'],
        ]);
        try {
            $review = $service->create(Employee::findOrFail($data['employeeId']), auth()->user(), $data['periodStart'], $data['periodEnd']);
        } catch (\DomainException $e) {
            $this->addError('employeeId', $e->getMessage()); return;
        }
        $this->showCreate = false;
        $this->openScoring($review->id);
        unset($this->reviews);
    }

    public function openScoring(int $id): void
    {
        $review = PerformanceReview::with('scores')->findOrFail($id);
        abort_unless($review->reviewer_id === auth()->id() || auth()->user()->hasRole('Super Admin'), 403);
        abort_unless($review->status === 'draft', 422);
        $this->editingId = $review->id;
        $this->scores = $review->scores->mapWithKeys(fn ($s) => [$s->id => $s->manager_score ?: ''])->all();
        $this->scoreComments = $review->scores->mapWithKeys(fn ($s) => [$s->id => $s->manager_comment ?: ''])->all();
        $this->strengths = $review->strengths ?? '';
        $this->areasForImprovement = $review->areas_for_improvement ?? '';
        $this->managerComment = $review->manager_comment ?? '';
    }

    public function submitReview(PerformanceReviewService $service): void
    {
        $review = PerformanceReview::findOrFail($this->editingId);
        try {
            $service->submit($review, auth()->user(), $this->scores, $this->scoreComments, [
                'strengths' => $this->strengths,
                'areas_for_improvement' => $this->areasForImprovement,
                'manager_comment' => $this->managerComment,
            ]);
        } catch (\DomainException $e) {
            $this->addError('scores', $e->getMessage()); return;
        }
        $this->editingId = null;
        session()->flash('success', 'បានបញ្ជូនការវាយតម្លៃទៅ HR។');
        unset($this->reviews);
    }

    public function approve(int $id, PerformanceReviewService $service): void
    {
        abort_unless(auth()->user()->can('performance.approve'), 403);
        try { $service->approve(PerformanceReview::findOrFail($id), auth()->user()); }
        catch (\DomainException $e) { $this->addError('workflow', $e->getMessage()); return; }
        session()->flash('success', 'HR បានអនុម័តការវាយតម្លៃ។'); unset($this->reviews);
    }

    public function close(int $id, PerformanceReviewService $service): void
    {
        abort_unless(auth()->user()->can('performance.approve'), 403);
        try { $service->close(PerformanceReview::findOrFail($id), auth()->user()); }
        catch (\DomainException $e) { $this->addError('workflow', $e->getMessage()); return; }
        session()->flash('success', 'បានបិទការវាយតម្លៃ។'); unset($this->reviews);
    }

    public function reopen(int $id, PerformanceReviewService $service): void
    {
        abort_unless(auth()->user()->can('performance.reopen'), 403);
        try { $service->reopen(PerformanceReview::findOrFail($id), auth()->user(), $this->reopenReasons[$id] ?? ''); }
        catch (\DomainException $e) { $this->addError("reopenReasons.$id", $e->getMessage()); return; }
        unset($this->reopenReasons[$id], $this->reviews);
        session()->flash('success', 'បានបើកការវាយតម្លៃឡើងវិញ និងកត់ត្រាមូលហេតុ។');
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div><flux:heading size="xl">ការវាយតម្លៃការងារ</flux:heading><flux:subheading>វាយតម្លៃតាម KPI និងគោលដៅដែលបានទទួលស្គាល់</flux:subheading></div>
        @can('performance.create')<flux:button variant="primary" icon="plus" wire:click="$set('showCreate', true)">បង្កើតការវាយតម្លៃ</flux:button>@endcan
    </div>
    @if(session('success'))<div class="rounded-xl border border-green-200 bg-green-50 p-4 text-green-800">{{ session('success') }}</div>@endif
    @error('workflow')<div class="rounded-xl border border-red-200 bg-red-50 p-4 text-red-800">{{ $message }}</div>@enderror

    @if($showCreate)
        <form wire:submit="createReview" class="grid gap-4 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900 sm:grid-cols-3">
            <flux:select wire:model="employeeId" label="បុគ្គលិក *"><option value="">ជ្រើសរើស</option>@foreach($this->employees as $e)<option value="{{ $e->id }}">{{ $e->employee_code }} — {{ $e->full_name_km ?: $e->full_name_en }}</option>@endforeach</flux:select>
            <flux:input wire:model="periodStart" type="date" label="ចាប់ពីថ្ងៃ *" />
            <flux:input wire:model="periodEnd" type="date" label="ដល់ថ្ងៃ *" />
            <div class="flex justify-end gap-2 sm:col-span-3"><flux:button type="button" variant="ghost" wire:click="$set('showCreate', false)">បោះបង់</flux:button><flux:button type="submit" variant="primary">បង្កើត</flux:button></div>
        </form>
    @endif

    @if($editingId)
        @php($activeReview = \App\Models\PerformanceReview::with('scores')->find($editingId))
        <form wire:submit="submitReview" class="space-y-4 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div><flux:heading size="lg">បញ្ចូលពិន្ទុ 1–5</flux:heading><flux:subheading>ពិន្ទុ 1 ឬ 2 តម្រូវឱ្យបញ្ចូលមូលហេតុយ៉ាងតិច 10 តួអក្សរ</flux:subheading></div>
            @error('scores')<div class="text-sm text-red-600">{{ $message }}</div>@enderror
            @foreach($activeReview->scores as $criterion)
                <div class="grid gap-3 rounded-xl bg-zinc-50 p-4 dark:bg-zinc-800/60 sm:grid-cols-[1fr_140px_1fr]">
                    <div><div class="font-medium">{{ $criterion->criterion_name }}</div><div class="text-xs text-zinc-500">ទម្ងន់ {{ number_format($criterion->weight,0) }}% · គោលដៅ {{ $criterion->target_value }} · លទ្ធផល {{ $criterion->actual_value }}</div></div>
                    <flux:select wire:model="scores.{{ $criterion->id }}" label="ពិន្ទុ *"><option value="">—</option>@for($i=1;$i<=5;$i++)<option value="{{ $i }}">{{ $i }}</option>@endfor</flux:select>
                    <flux:input wire:model="scoreComments.{{ $criterion->id }}" label="មតិអ្នកគ្រប់គ្រង" />
                </div>
            @endforeach
            <div class="grid gap-4 sm:grid-cols-3"><flux:textarea wire:model="strengths" label="ចំណុចខ្លាំង" rows="3" /><flux:textarea wire:model="areasForImprovement" label="ចំណុចត្រូវកែលម្អ" rows="3" /><flux:textarea wire:model="managerComment" label="មតិសរុប" rows="3" /></div>
            <div class="flex justify-end gap-2"><flux:button type="button" variant="ghost" wire:click="$set('editingId', null)">រក្សាទុកព្រាង</flux:button><flux:button type="submit" variant="primary" icon="paper-airplane">បញ្ជូនទៅ HR</flux:button></div>
        </form>
    @endif

    <div class="grid gap-3 sm:grid-cols-[1fr_220px]"><flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="ស្វែងរកបុគ្គលិក..." /><flux:select wire:model.live="status"><option value="">ស្ថានភាពទាំងអស់</option><option value="draft">ព្រាង</option><option value="manager_submitted">រង់ចាំ HR</option><option value="hr_approved">រង់ចាំបុគ្គលិក</option><option value="employee_acknowledged">បានទទួលស្គាល់</option><option value="closed">បានបិទ</option></flux:select></div>
    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"><div class="overflow-x-auto"><table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
        <thead class="bg-zinc-50 dark:bg-zinc-800/60"><tr class="text-left text-sm"><th class="px-5 py-3">បុគ្គលិក</th><th class="px-5 py-3">រយៈពេល</th><th class="px-5 py-3">ពិន្ទុ</th><th class="px-5 py-3">ស្ថានភាព</th><th class="px-5 py-3 text-right">សកម្មភាព</th></tr></thead>
        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">@forelse($this->reviews as $review)<tr>
            <td class="px-5 py-4"><div class="font-medium">{{ $review->employee->full_name_km ?: $review->employee->full_name_en }}</div><div class="text-xs text-zinc-500">{{ $review->employee->employee_code }}</div></td>
            <td class="px-5 py-4">{{ $review->period_start->format('d/m/Y') }} – {{ $review->period_end->format('d/m/Y') }}</td>
            <td class="px-5 py-4 font-medium">{{ $review->overall_score ? number_format($review->overall_score,2).' / 5' : '—' }}</td>
            <td class="px-5 py-4"><flux:badge>{{ $review->status }}</flux:badge></td>
            <td class="px-5 py-4"><div class="flex justify-end gap-2">
                @if($review->status==='draft' && ($review->reviewer_id===auth()->id() || auth()->user()->hasRole('Super Admin')))<flux:button size="sm" icon="pencil-square" wire:click="openScoring({{ $review->id }})">ដាក់ពិន្ទុ</flux:button>@endif
                @can('performance.approve')@if($review->status==='manager_submitted')<flux:button size="sm" variant="primary" icon="check" wire:click="approve({{ $review->id }})">HR អនុម័ត</flux:button>@elseif($review->status==='employee_acknowledged')<flux:button size="sm" variant="primary" icon="lock-closed" wire:click="close({{ $review->id }})">បិទ</flux:button>@endif@endcan
            </div>
            @can('performance.reopen')@if(in_array($review->status,['manager_submitted','hr_approved','employee_acknowledged','closed']))<div class="mt-2 flex justify-end gap-2"><flux:input size="sm" wire:model="reopenReasons.{{ $review->id }}" placeholder="មូលហេតុបើកឡើងវិញ..." /><flux:button size="sm" variant="danger" icon="arrow-path" wire:click="reopen({{ $review->id }})">បើកវិញ</flux:button></div>@endif@endcan
            </td>
        </tr>@empty<tr><td colspan="5" class="px-5 py-12 text-center text-zinc-500">មិនទាន់មានការវាយតម្លៃទេ។</td></tr>@endforelse</tbody>
    </table></div><div class="p-4">{{ $this->reviews->links() }}</div></div>
</div>
