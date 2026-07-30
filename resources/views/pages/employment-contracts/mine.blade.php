<x-layouts::app :title="'កិច្ចសន្យារបស់ខ្ញុំ'">
    <div class="space-y-6">
        <div>
            <flux:heading size="xl">កិច្ចសន្យារបស់ខ្ញុំ</flux:heading>
            <flux:subheading>ទំព័រឯកជន—អ្នកអាចមើល និងទាញយកតែកិច្ចសន្យារបស់អ្នកប៉ុណ្ណោះ</flux:subheading>
        </div>
        <div class="grid gap-4">
            @forelse ($contracts as $contract)
                <article class="rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="text-sm text-zinc-500">{{ $contract->contract_number }}</div>
                            <h2 class="mt-1 text-lg font-bold uppercase">{{ $contract->type }} · {{ $contract->status }}</h2>
                            <div class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                                <div>រយៈពេល: {{ $contract->start_date->format('d/m/Y') }} — {{ $contract->end_date?->format('d/m/Y') ?? 'មិនកំណត់' }}</div>
                                <div>មុខតំណែង: {{ $contract->position_title ?: '—' }}</div>
                                <div>ប្រាក់ឈ្នួល: {{ number_format($contract->salary_amount, $contract->salary_currency === 'KHR' ? 0 : 2) }} {{ $contract->salary_currency }}</div>
                                <div>ម៉ោងការងារ: {{ $contract->work_hours_per_day }} ម៉ោង × {{ $contract->work_days_per_week }} ថ្ងៃ</div>
                            </div>
                        </div>
                        @if ($contract->document_path)
                            <flux:button variant="primary" icon="arrow-down-tray" :href="route('contracts.download', $contract)">ទាញយក PDF</flux:button>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-zinc-300 p-12 text-center text-zinc-500 dark:border-zinc-700">មិនទាន់មានកិច្ចសន្យាដែលអាចមើលបាន</div>
            @endforelse
        </div>
        {{ $contracts->links() }}
    </div>
</x-layouts::app>
