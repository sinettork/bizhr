<?php

use App\Models\Company;
use App\Models\KpiTemplate;
use App\Models\Position;
use App\Services\KpiTemplateService;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('គំរូ KPI')] class extends Component
{
    use WithPagination;

    public string $search = '';
    public string $status = '';
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $description = '';
    public string $positionId = '';
    public string $reviewFrequency = 'monthly';
    public bool $isActive = true;
    public array $items = [];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('performance.manage-goals'), 403);
        $this->resetKpiItems();
    }

    #[Computed]
    public function templates()
    {
        return KpiTemplate::query()
            ->with(['position', 'items'])
            ->when($this->search, function ($query): void {
                $term = '%'.$this->search.'%';
                $query->where(fn ($query) => $query->where('name', 'like', $term)
                    ->orWhereHas('position', fn ($position) => $position->where('title', 'like', $term)));
            })
            ->when($this->status !== '', fn ($query) => $query->where('is_active', $this->status === 'active'))
            ->latest('id')
            ->paginate(15);
    }

    #[Computed]
    public function positions()
    {
        return Position::query()->where('is_active', true)->orderBy('title')->get();
    }

    public function updated($property): void
    {
        if (in_array($property, ['search', 'status'], true)) {
            $this->resetPage();
            unset($this->templates);
        }
    }

    public function addItem(): void
    {
        $this->items[] = $this->blankItem();
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) <= 1) {
            return;
        }
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function save(KpiTemplateService $service): void
    {
        $companyId = Company::query()->value('id');
        abort_unless($companyId, 422, 'សូមបង្កើតព័ត៌មានក្រុមហ៊ុនជាមុនសិន។');

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:150',
                Rule::unique('kpi_templates')->where(fn ($q) => $q
                    ->where('company_id', $companyId)
                    ->where('position_id', $this->positionId ?: null))
                    ->ignore($this->editingId)],
            'description' => ['nullable', 'string', 'max:2000'],
            'positionId' => ['nullable', 'exists:positions,id'],
            'reviewFrequency' => ['required', Rule::in(['monthly', 'quarterly', 'semi_annual', 'annual'])],
            'isActive' => ['boolean'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:150'],
            'items.*.description' => ['nullable', 'string', 'max:500'],
            'items.*.measurement_unit' => ['required', Rule::in(['number', 'percent', 'currency_usd', 'currency_khr', 'days', 'hours', 'score'])],
            'items.*.target_value' => ['required', 'numeric', 'min:0'],
            'items.*.weight' => ['required', 'numeric', 'gt:0', 'max:100'],
            'items.*.scoring_direction' => ['required', Rule::in(['higher_is_better', 'lower_is_better', 'target_is_best'])],
        ]);

        try {
            $service->save([
                'company_id' => $companyId,
                'position_id' => $this->positionId ?: null,
                'name' => $validated['name'],
                'description' => $validated['description'] ?: null,
                'review_frequency' => $validated['reviewFrequency'],
                'is_active' => $validated['isActive'],
            ], $validated['items'], auth()->user(),
                $this->editingId ? KpiTemplate::findOrFail($this->editingId) : null);
        } catch (\DomainException $exception) {
            $this->addError('items', $exception->getMessage());
            return;
        }

        session()->flash('success', $this->editingId ? 'បានកែប្រែគំរូ KPI។' : 'បានបង្កើតគំរូ KPI។');
        $this->cancel();
        unset($this->templates);
    }

    public function edit(int $id): void
    {
        $template = KpiTemplate::with('items')->findOrFail($id);
        $this->editingId = $template->id;
        $this->name = $template->name;
        $this->description = $template->description ?? '';
        $this->positionId = (string) ($template->position_id ?? '');
        $this->reviewFrequency = $template->review_frequency;
        $this->isActive = $template->is_active;
        $this->items = $template->items->map(fn ($item) => [
            'name' => $item->name,
            'description' => $item->description ?? '',
            'measurement_unit' => $item->measurement_unit,
            'target_value' => (string) $item->target_value,
            'weight' => (string) $item->weight,
            'scoring_direction' => $item->scoring_direction,
        ])->all();
        $this->showForm = true;
    }

    public function toggleStatus(int $id): void
    {
        $template = KpiTemplate::findOrFail($id);
        $template->update(['is_active' => ! $template->is_active]);
        unset($this->templates);
    }

    public function cancel(): void
    {
        $this->reset(['editingId', 'name', 'description', 'positionId', 'showForm']);
        $this->reviewFrequency = 'monthly';
        $this->isActive = true;
        $this->resetKpiItems();
        $this->resetValidation();
    }

    private function resetKpiItems(): void
    {
        $this->items = [$this->blankItem()];
    }

    private function blankItem(): array
    {
        return [
            'name' => '', 'description' => '', 'measurement_unit' => 'number',
            'target_value' => '', 'weight' => '', 'scoring_direction' => 'higher_is_better',
        ];
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <flux:heading size="xl">គំរូសូចនាករ KPI</flux:heading>
            <flux:subheading>កំណត់គោលដៅ ទម្ងន់ និងវិធីវាស់វែងតាមមុខតំណែង</flux:subheading>
        </div>
        <flux:button variant="primary" icon="plus" wire:click="$set('showForm', true)">បង្កើតគំរូ KPI</flux:button>
    </div>

    @if (session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 p-4 text-green-800">{{ session('success') }}</div>
    @endif

    @if ($showForm)
        <form wire:submit="save" class="space-y-5 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input wire:model="name" label="ឈ្មោះគំរូ KPI *" />
                <flux:select wire:model="positionId" label="អនុវត្តសម្រាប់មុខតំណែង">
                    <option value="">មុខតំណែងទាំងអស់</option>
                    @foreach ($this->positions as $position)
                        <option value="{{ $position->id }}">{{ $position->title }}</option>
                    @endforeach
                </flux:select>
                <flux:select wire:model="reviewFrequency" label="វដ្តវាយតម្លៃ *">
                    <option value="monthly">ប្រចាំខែ</option>
                    <option value="quarterly">ប្រចាំត្រីមាស</option>
                    <option value="semi_annual">រៀងរាល់ ៦ ខែ</option>
                    <option value="annual">ប្រចាំឆ្នាំ</option>
                </flux:select>
                <flux:field>
                    <flux:label>ស្ថានភាព</flux:label>
                    <flux:switch wire:model="isActive" label="កំពុងប្រើប្រាស់" />
                </flux:field>
                <div class="sm:col-span-2"><flux:textarea wire:model="description" label="ពិពណ៌នា" rows="2" /></div>
            </div>

            <div class="space-y-3 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">លក្ខខណ្ឌ KPI</flux:heading>
                        <flux:subheading>ទម្ងន់សរុបត្រូវតែស្មើ 100%</flux:subheading>
                    </div>
                    <flux:button type="button" size="sm" icon="plus" wire:click="addItem">បន្ថែមលក្ខខណ្ឌ</flux:button>
                </div>
                @error('items') <div class="text-sm text-red-600">{{ $message }}</div> @enderror

                @foreach ($items as $index => $item)
                    <div wire:key="kpi-item-{{ $index }}" class="grid gap-3 rounded-xl bg-zinc-50 p-4 dark:bg-zinc-800/60 sm:grid-cols-2 xl:grid-cols-6">
                        <div class="xl:col-span-2"><flux:input wire:model="items.{{ $index }}.name" label="ឈ្មោះសូចនាករ *" /></div>
                        <flux:select wire:model="items.{{ $index }}.measurement_unit" label="ឯកតាវាស់ *">
                            <option value="number">ចំនួន</option><option value="percent">ភាគរយ</option>
                            <option value="currency_usd">USD</option><option value="currency_khr">KHR</option>
                            <option value="days">ថ្ងៃ</option><option value="hours">ម៉ោង</option><option value="score">ពិន្ទុ</option>
                        </flux:select>
                        <flux:input wire:model="items.{{ $index }}.target_value" type="number" step="0.01" min="0" label="គោលដៅ *" />
                        <flux:input wire:model="items.{{ $index }}.weight" type="number" step="0.01" min="0.01" max="100" label="ទម្ងន់ % *" />
                        <div class="flex items-end gap-2">
                            <div class="min-w-0 flex-1">
                                <flux:select wire:model="items.{{ $index }}.scoring_direction" label="វិធីគណនា *">
                                    <option value="higher_is_better">ខ្ពស់ជាង កាន់តែល្អ</option>
                                    <option value="lower_is_better">ទាបជាង កាន់តែល្អ</option>
                                    <option value="target_is_best">ជិតគោលដៅ កាន់តែល្អ</option>
                                </flux:select>
                            </div>
                            <flux:button type="button" size="sm" variant="danger" icon="trash" wire:click="removeItem({{ $index }})" />
                        </div>
                        <div class="sm:col-span-2 xl:col-span-6"><flux:input wire:model="items.{{ $index }}.description" label="សេចក្ដីពិពណ៌នាលក្ខខណ្ឌ" /></div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-2 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                <flux:button type="button" variant="ghost" wire:click="cancel">បោះបង់</flux:button>
                <flux:button type="submit" variant="primary" icon="check">{{ $editingId ? 'រក្សាទុកការកែប្រែ' : 'បង្កើតគំរូ KPI' }}</flux:button>
            </div>
        </form>
    @endif

    <div class="grid gap-3 sm:grid-cols-[1fr_220px]">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" placeholder="ស្វែងរកគំរូ ឬមុខតំណែង..." />
        <flux:select wire:model.live="status">
            <option value="">ស្ថានភាពទាំងអស់</option>
            <option value="active">កំពុងប្រើប្រាស់</option>
            <option value="inactive">បានផ្អាក</option>
        </flux:select>
    </div>

    <div class="overflow-hidden rounded-2xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60"><tr class="text-left text-sm">
                    <th class="px-5 py-3">គំរូ KPI</th><th class="px-5 py-3">មុខតំណែង</th>
                    <th class="px-5 py-3">វដ្ត</th><th class="px-5 py-3">លក្ខខណ្ឌ</th>
                    <th class="px-5 py-3">ស្ថានភាព</th><th class="px-5 py-3 text-right">សកម្មភាព</th>
                </tr></thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                    @forelse ($this->templates as $template)
                        <tr>
                            <td class="px-5 py-4"><div class="font-medium">{{ $template->name }}</div><div class="mt-1 text-xs text-zinc-500">{{ \Illuminate\Support\Str::limit($template->description, 70) }}</div></td>
                            <td class="px-5 py-4">{{ $template->position?->title ?? 'មុខតំណែងទាំងអស់' }}</td>
                            <td class="px-5 py-4">{{ $template->review_frequency }}</td>
                            <td class="px-5 py-4">{{ $template->items->count() }} · {{ number_format($template->items->sum('weight'), 0) }}%</td>
                            <td class="px-5 py-4"><flux:badge :color="$template->is_active ? 'green' : 'zinc'">{{ $template->is_active ? 'កំពុងប្រើ' : 'បានផ្អាក' }}</flux:badge></td>
                            <td class="px-5 py-4"><div class="flex justify-end gap-2">
                                <flux:button size="sm" icon="pencil-square" wire:click="edit({{ $template->id }})">កែប្រែ</flux:button>
                                <flux:button size="sm" icon="power" wire:click="toggleStatus({{ $template->id }})" wire:confirm="តើអ្នកចង់ប្ដូរស្ថានភាពគំរូនេះមែនទេ?" />
                            </div></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-zinc-500">មិនទាន់មានគំរូ KPI ទេ។</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $this->templates->links() }}</div>
    </div>
</div>
