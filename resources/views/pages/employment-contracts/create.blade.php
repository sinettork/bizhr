<x-layouts::app :title="'បង្កើតកិច្ចសន្យាការងារ'">
    <div class="space-y-6">
        <div class="mb-6">
            <flux:button variant="ghost" size="sm" icon="arrow-left" :href="route('contracts.index')">ត្រឡប់ទៅបញ្ជី</flux:button>
            <flux:heading size="xl" class="mt-3">បង្កើតកិច្ចសន្យាការងារ</flux:heading>
            <flux:subheading>រក្សាទុកជាឯកសារឯកជន ហើយបញ្ជូនទៅអ្នកមានសិទ្ធិអនុម័ត</flux:subheading>
        </div>

        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-red-300 bg-red-50 p-4 text-red-800">
                <ul class="list-disc space-y-1 pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="POST" action="{{ route('contracts.store') }}" enctype="multipart/form-data"
            class="space-y-6 rounded-2xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            @csrf
            @if ($renewal)
                <input type="hidden" name="previous_contract_id" value="{{ $renewal->id }}">
                <div class="rounded-lg border border-amber-300 bg-amber-50 p-4 text-amber-900">
                    កំពុងបន្តពីកិច្ចសន្យា {{ $renewal->contract_number }}។ ថ្ងៃចាប់ផ្តើមថ្មីត្រូវតែជាថ្ងៃបន្ទាប់ពី {{ $renewal->end_date->format('d/m/Y') }}។
                </div>
            @endif
            <section>
                <h2 class="mb-4 font-bold">១. បុគ្គលិក និងប្រភេទកិច្ចសន្យា</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <flux:select name="employee_id" label="បុគ្គលិក *" required>
                            <option value="">ជ្រើសរើសបុគ្គលិក</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}" @selected(old('employee_id', $renewal?->employee_id) == $employee->id)>{{ $employee->employee_code }} — {{ $employee->full_name_km ?: $employee->full_name_en }}</option>
                            @endforeach
                    </flux:select>
                    <flux:input name="contract_number" :value="old('contract_number')" label="លេខកិច្ចសន្យា *" required />
                    <flux:select name="type" label="ប្រភេទកិច្ចសន្យា *" required>
                        <option value="fdc" @selected(old('type', $renewal ? 'fdc' : '') === 'fdc')>FDC — មានរយៈពេលកំណត់</option>
                        <option value="udc" @selected(old('type') === 'udc')>UDC — មិនកំណត់រយៈពេល</option>
                        <option value="probation" @selected(old('type') === 'probation')>សាកល្បងការងារ</option>
                        <option value="apprenticeship" @selected(old('type') === 'apprenticeship')>ហ្វឹកហាត់វិជ្ជាជីវៈ</option>
                        <option value="internship" @selected(old('type') === 'internship')>កម្មសិក្សា</option>
                    </flux:select>
                    <flux:input type="file" name="document" accept=".pdf" label="ឯកសារចុះហត្ថលេខា PDF (អតិបរមា 10MB)" />
                </div>
            </section>

            <section class="border-t border-zinc-200 pt-5 dark:border-zinc-700">
                <h2 class="mb-4 font-bold">២. កាលបរិច្ឆេទ និងលក្ខខណ្ឌ</h2>
                <div class="grid gap-4 md:grid-cols-3">
                    <flux:input type="date" name="start_date" :value="old('start_date', $renewal?->end_date?->copy()->addDay()->toDateString())" label="ថ្ងៃចាប់ផ្តើម *" required />
                    <flux:input type="date" name="end_date" :value="old('end_date')" label="ថ្ងៃបញ្ចប់ (ចាំបាច់សម្រាប់ FDC)" />
                    <flux:input type="date" name="signed_at" :value="old('signed_at')" label="ថ្ងៃចុះហត្ថលេខា" />
                    <flux:select name="probation_category" label="ក្រុមសាកល្បងការងារ">
                        <option value="">មិនអនុវត្ត</option>
                        <option value="regular" @selected(old('probation_category') === 'regular')>បុគ្គលិកធម្មតា — 3 ខែ</option>
                        <option value="specialized" @selected(old('probation_category') === 'specialized')>កម្មករជំនាញ — 2 ខែ</option>
                        <option value="non_specialized" @selected(old('probation_category') === 'non_specialized')>កម្មករគ្មានជំនាញ — 1 ខែ</option>
                    </flux:select>
                    <flux:input type="date" name="probation_end_date" :value="old('probation_end_date')" label="ថ្ងៃបញ្ចប់សាកល្បង" />
                </div>
            </section>

            <section class="border-t border-zinc-200 pt-5 dark:border-zinc-700">
                <h2 class="mb-4 font-bold">៣. ប្រាក់ឈ្នួល និងម៉ោងការងារ</h2>
                <div class="grid gap-4 md:grid-cols-3">
                    <flux:input type="number" step="0.01" min="0" name="salary_amount" :value="old('salary_amount', $renewal?->salary_amount)" label="ប្រាក់ឈ្នួល *" required />
                    <flux:select name="salary_currency" label="រូបិយប័ណ្ណ *">
                        <option value="USD" @selected(old('salary_currency', $renewal?->salary_currency ?? 'USD') === 'USD')>USD — ដុល្លារ</option>
                        <option value="KHR" @selected(old('salary_currency', $renewal?->salary_currency) === 'KHR')>KHR — រៀល</option>
                    </flux:select>
                    <flux:select name="pay_type" label="វិធីគិតប្រាក់ឈ្នួល *">
                        <option value="monthly" @selected(old('pay_type', $renewal?->pay_type ?? 'monthly') === 'monthly')>ប្រចាំខែ</option>
                        <option value="daily" @selected(old('pay_type', $renewal?->pay_type) === 'daily')>ប្រចាំថ្ងៃ</option>
                        <option value="hourly" @selected(old('pay_type', $renewal?->pay_type) === 'hourly')>ប្រចាំម៉ោង</option>
                    </flux:select>
                    <flux:input type="number" step="0.25" min="1" max="24" name="work_hours_per_day" :value="old('work_hours_per_day', $renewal?->work_hours_per_day ?? 8)" label="ម៉ោងក្នុងមួយថ្ងៃ *" required />
                    <flux:input type="number" step="0.5" min="1" max="7" name="work_days_per_week" :value="old('work_days_per_week', $renewal?->work_days_per_week ?? 6)" label="ថ្ងៃក្នុងមួយសប្ដាហ៍ *" required />
                </div>
            </section>

            <div class="flex justify-end gap-3 border-t border-zinc-200 pt-5 dark:border-zinc-700">
                <flux:button :href="route('contracts.index')" variant="ghost">បោះបង់</flux:button>
                <flux:button type="submit" variant="primary" icon="paper-airplane">រក្សាទុក និងបញ្ជូនអនុម័ត</flux:button>
            </div>
        </form>
    </div>
</x-layouts::app>
