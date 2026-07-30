<x-layouts::app :title="'គ្រប់គ្រងកិច្ចសន្យាការងារ'">
    <div class="space-y-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <flux:heading size="xl">កិច្ចសន្យាការងារ</flux:heading>
                <flux:subheading>គ្រប់គ្រង FDC, UDC, សាកល្បងការងារ ការអនុម័ត និងថ្ងៃផុតកំណត់</flux:subheading>
            </div>
            @can('contract.create')
                <flux:button variant="primary" icon="plus" :href="route('contracts.create')">បង្កើតកិច្ចសន្យា</flux:button>
            @endcan
        </div>

        @if (session('success'))
            <div class="rounded-lg border border-emerald-300 bg-emerald-50 p-4 text-emerald-800">{{ session('success') }}</div>
        @endif

        <form method="GET" class="grid gap-3 sm:grid-cols-[1fr_220px_auto]">
            <flux:input name="search" :value="request('search')" icon="magnifying-glass"
                placeholder="លេខកិច្ចសន្យា ឈ្មោះ ឬលេខបុគ្គលិក" />
            <flux:select name="status">
                <option value="">ស្ថានភាពទាំងអស់</option>
                @foreach (['draft'=>'ព្រាង','pending_approval'=>'រង់ចាំអនុម័ត','active'=>'សកម្ម','expiring'=>'ជិតផុតកំណត់','expired'=>'ផុតកំណត់','terminated'=>'បានបញ្ចប់','superseded'=>'បានបន្តថ្មី'] as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </flux:select>
            <flux:button type="submit" icon="magnifying-glass">ស្វែងរក</flux:button>
        </form>

        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 text-left text-zinc-600 dark:bg-zinc-800 dark:text-zinc-300">
                        <tr>
                            <th class="px-5 py-3">កិច្ចសន្យា / បុគ្គលិក</th>
                            <th class="px-5 py-3">ប្រភេទ</th>
                            <th class="px-5 py-3">រយៈពេល</th>
                            <th class="px-5 py-3">ប្រាក់ឈ្នួល</th>
                            <th class="px-5 py-3">ស្ថានភាព</th>
                            <th class="px-5 py-3 text-right">សកម្មភាព</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @forelse ($contracts as $contract)
                            <tr>
                                <td class="px-5 py-4">
                                    <div class="font-semibold">{{ $contract->contract_number }}</div>
                                    <div class="text-zinc-500">{{ $contract->employee->full_name_km ?: $contract->employee->full_name_en }} · {{ $contract->employee->employee_code }}</div>
                                </td>
                                <td class="px-5 py-4"><flux:badge>{{ strtoupper($contract->type) }}</flux:badge></td>
                                <td class="whitespace-nowrap px-5 py-4">{{ $contract->start_date->format('d/m/Y') }} — {{ $contract->end_date?->format('d/m/Y') ?? 'មិនកំណត់' }}</td>
                                <td class="whitespace-nowrap px-5 py-4 font-medium">{{ number_format($contract->salary_amount, $contract->salary_currency === 'KHR' ? 0 : 2) }} {{ $contract->salary_currency }}</td>
                                <td class="px-5 py-4"><flux:badge>{{ $contract->status }}</flux:badge></td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        @if ($contract->document_path)
                                            <flux:button size="sm" icon="arrow-down-tray" :href="route('contracts.download', $contract)">PDF</flux:button>
                                        @endif
                                        @can('contract.approve')
                                            @if ($contract->status === 'pending_approval')
                                                <form method="POST" action="{{ route('contracts.approve', $contract) }}">
                                                    @csrf
                                                    <flux:button type="submit" size="sm" variant="primary" icon="check">អនុម័ត</flux:button>
                                                </form>
                                            @endif
                                        @endcan
                                        @can('contract.create')
                                            @if ($contract->type === 'fdc' && in_array($contract->status, ['active', 'expiring']))
                                                <flux:button size="sm" icon="arrow-path" :href="route('contracts.renew', $contract)">បន្ត</flux:button>
                                            @endif
                                        @endcan
                                    </div>
                                    @can('contract.terminate')
                                        @if (in_array($contract->status, ['active', 'expiring']))
                                            <details class="mt-2 text-left">
                                                <summary class="cursor-pointer text-xs text-red-600">បញ្ចប់កិច្ចសន្យា</summary>
                                                <form method="POST" action="{{ route('contracts.terminate', $contract) }}" class="mt-2 grid gap-2">
                                                    @csrf
                                                    <input type="date" name="termination_date" required class="rounded-md border bg-transparent px-2 py-1 dark:border-zinc-700">
                                                    <textarea name="termination_reason" required minlength="10" placeholder="មូលហេតុលម្អិត" class="rounded-md border bg-transparent px-2 py-1 dark:border-zinc-700"></textarea>
                                                    <button class="rounded-md bg-red-600 px-3 py-1.5 text-white" onclick="return confirm('តើអ្នកប្រាកដថាចង់បញ្ចប់កិច្ចសន្យានេះទេ?')">បញ្ជាក់បញ្ចប់</button>
                                                </form>
                                            </details>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-12 text-center text-zinc-500">មិនទាន់មានកិច្ចសន្យា</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $contracts->links() }}</div>
        </div>
    </div>
</x-layouts::app>
