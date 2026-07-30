<?php

use App\Models\AuditLog;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('កំណត់ត្រាសកម្មភាព')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $module = '';
    public string $action = '';
    public string $dateFrom = '';
    public string $dateTo = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('audit.view'), 403);
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'module', 'action', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function logs()
    {
        return AuditLog::query()
            ->with('user:id,name,email')
            ->when($this->search, function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where(function ($query) use ($term): void {
                    $query->where('record_type', 'like', $term)
                        ->orWhere('record_id', 'like', $term)
                        ->orWhere('route', 'like', $term)
                        ->orWhereHas('user', fn ($user) => $user
                            ->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term));
                });
            })
            ->when($this->module, fn ($query) => $query->where('module', $this->module))
            ->when($this->action, fn ($query) => $query->where('action', $this->action))
            ->when($this->dateFrom, fn ($query) => $query->whereDate('created_at', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('created_at', '<=', $this->dateTo))
            ->latest('id')
            ->paginate(20);
    }

    #[Computed]
    public function modules()
    {
        return AuditLog::query()->distinct()->orderBy('module')->pluck('module');
    }
};
?>

<div class="space-y-6">
    <div>
        <flux:heading size="xl">កំណត់ត្រាសកម្មភាព</flux:heading>
        <flux:subheading>តាមដានការផ្លាស់ប្តូរទិន្នន័យសំខាន់ៗក្នុងប្រព័ន្ធ</flux:subheading>
    </div>

    <div class="grid gap-3 rounded-2xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 sm:grid-cols-2 xl:grid-cols-5">
        <flux:input
            wire:model.live.debounce.300ms="search"
            icon="magnifying-glass"
            placeholder="ស្វែងរកអ្នកប្រើ ឬកំណត់ត្រា..."
        />

        <flux:select wire:model.live="module">
            <option value="">គ្រប់ផ្នែក</option>
            @foreach ($this->modules as $moduleName)
                <option value="{{ $moduleName }}">{{ $moduleName }}</option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="action">
            <option value="">គ្រប់សកម្មភាព</option>
            <option value="created">បង្កើត</option>
            <option value="updated">កែប្រែ</option>
            <option value="deleted">លុប</option>
        </flux:select>

        <flux:input wire:model.live="dateFrom" type="date" />
        <flux:input wire:model.live="dateTo" type="date" />
    </div>

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60">
                    <tr class="text-left text-xs text-zinc-500">
                        <th class="px-5 py-3">កាលបរិច្ឆេទ</th>
                        <th class="px-5 py-3">អ្នកប្រើប្រាស់</th>
                        <th class="px-5 py-3">សកម្មភាព</th>
                        <th class="px-5 py-3">ផ្នែក / កំណត់ត្រា</th>
                        <th class="px-5 py-3">ព័ត៌មានផ្លាស់ប្តូរ</th>
                        <th class="px-5 py-3">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($this->logs as $log)
                        <tr wire:key="audit-{{ $log->id }}" class="align-top text-sm">
                            <td class="whitespace-nowrap px-5 py-4">
                                {{ $log->created_at?->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-medium">{{ $log->user?->name ?? 'ប្រព័ន្ធ' }}</div>
                                <div class="text-xs text-zinc-500">{{ $log->user?->email }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <flux:badge
                                    :color="match ($log->action) {
                                        'created' => 'green',
                                        'deleted' => 'red',
                                        default => 'amber',
                                    }"
                                >
                                    {{ match ($log->action) {
                                        'created' => 'បង្កើត',
                                        'deleted' => 'លុប',
                                        default => 'កែប្រែ',
                                    } }}
                                </flux:badge>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-medium">{{ $log->module }}</div>
                                <div class="text-xs text-zinc-500">#{{ $log->record_id }}</div>
                            </td>
                            <td class="min-w-72 px-5 py-4">
                                <details>
                                    <summary class="cursor-pointer text-indigo-600 dark:text-indigo-400">
                                        មើលព័ត៌មាន
                                    </summary>
                                    <div class="mt-2 grid gap-2 text-xs">
                                        @if ($log->old_values)
                                            <pre class="max-w-xl overflow-auto rounded-lg bg-zinc-100 p-3 dark:bg-zinc-800">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        @endif
                                        @if ($log->new_values)
                                            <pre class="max-w-xl overflow-auto rounded-lg bg-zinc-100 p-3 dark:bg-zinc-800">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        @endif
                                    </div>
                                </details>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-xs text-zinc-500">
                                {{ $log->ip_address ?: '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-zinc-500">
                                មិនទាន់មានកំណត់ត្រាសកម្មភាពទេ។
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($this->logs->hasPages())
            <div class="border-t border-zinc-200 px-5 py-4 dark:border-zinc-700">
                {{ $this->logs->links() }}
            </div>
        @endif
    </div>
</div>
