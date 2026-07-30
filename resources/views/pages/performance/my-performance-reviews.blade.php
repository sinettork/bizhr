<?php

use App\Models\PerformanceReview;
use App\Services\PerformanceReviewService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('ការវាយតម្លៃរបស់ខ្ញុំ')] class extends Component
{
    public array $comments = [];
    public function mount(): void { abort_unless(auth()->user()?->employee && auth()->user()?->can('performance.view-own'), 403); }
    #[Computed] public function reviews() { return PerformanceReview::query()->with('scores')->where('employee_id', auth()->user()->employee->id)->whereIn('status',['hr_approved','employee_acknowledged','closed'])->latest('period_end')->get(); }
    public function acknowledge(int $id, PerformanceReviewService $service): void
    {
        try { $service->acknowledge(PerformanceReview::findOrFail($id), auth()->user(), $this->comments[$id] ?? null); }
        catch(\DomainException $e) { $this->addError("comments.$id",$e->getMessage()); return; }
        unset($this->comments[$id],$this->reviews); session()->flash('success','បានទទួលស្គាល់ការវាយតម្លៃ។ ការទទួលស្គាល់មិនមានន័យថាយល់ព្រមទាំងស្រុងទេ។');
    }
};
?>
<div class="space-y-6">
    <div><flux:heading size="xl">ការវាយតម្លៃរបស់ខ្ញុំ</flux:heading><flux:subheading>មើលពិន្ទុ និងទទួលស្គាល់លទ្ធផលដែល HR បានអនុម័ត</flux:subheading></div>
    @if(session('success'))<div class="rounded-xl border border-green-200 bg-green-50 p-4 text-green-800">{{ session('success') }}</div>@endif
    <div class="space-y-4">@forelse($this->reviews as $review)<article class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="flex items-start justify-between"><div><flux:heading size="lg">{{ $review->period_start->format('d/m/Y') }} – {{ $review->period_end->format('d/m/Y') }}</flux:heading><flux:subheading>ពិន្ទុសរុប {{ number_format($review->overall_score,2) }} / 5</flux:subheading></div><flux:badge>{{ $review->status }}</flux:badge></div>
        <div class="mt-4 overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700"><table class="min-w-full"><thead class="bg-zinc-50 dark:bg-zinc-800/60"><tr class="text-left text-sm"><th class="px-4 py-3">លក្ខខណ្ឌ</th><th class="px-4 py-3">ទម្ងន់</th><th class="px-4 py-3">ពិន្ទុ</th><th class="px-4 py-3">មតិ</th></tr></thead><tbody>@foreach($review->scores as $s)<tr class="border-t border-zinc-100 dark:border-zinc-800"><td class="px-4 py-3">{{ $s->criterion_name }}</td><td class="px-4 py-3">{{ number_format($s->weight,0) }}%</td><td class="px-4 py-3">{{ $s->manager_score }}/5</td><td class="px-4 py-3 text-sm text-zinc-500">{{ $s->manager_comment ?: '—' }}</td></tr>@endforeach</tbody></table></div>
        @if($review->status==='hr_approved')<div class="mt-4 grid gap-3 sm:grid-cols-[1fr_auto]"><flux:textarea wire:model="comments.{{ $review->id }}" label="មតិបុគ្គលិក (មិនចាំបាច់)" rows="2" /><div class="flex items-end"><flux:button variant="primary" icon="check" wire:click="acknowledge({{ $review->id }})">ទទួលស្គាល់</flux:button></div></div>@elseif($review->employee_comment)<div class="mt-4 text-sm text-zinc-500">មតិរបស់ខ្ញុំ: {{ $review->employee_comment }}</div>@endif
    </article>@empty<div class="rounded-2xl border border-dashed border-zinc-300 p-12 text-center text-zinc-500 dark:border-zinc-700">មិនទាន់មានការវាយតម្លៃដែល HR បានអនុម័តទេ។</div>@endforelse</div>
</div>
