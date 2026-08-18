<x-layouts::app title="My employment contracts">
    <x-workspace-command-bar title="My employment contracts" icon="fa-file-contract" />
    <div class="row g-3">
        @forelse ($contracts as $contract)
            <div class="col-lg-6"><article class="card h-100"><div class="card-body"><div class="d-flex justify-content-between gap-3"><div><div class="small text-body-secondary">{{ $contract->contract_number }}</div><h2 class="h5 mt-1 mb-0">{{ strtoupper($contract->type) }}</h2></div><span class="badge text-bg-{{ $contract->status === 'active' ? 'success' : 'secondary' }} align-self-start">{{ ucwords(str_replace('_', ' ', $contract->status)) }}</span></div><dl class="row small mt-4 mb-0"><dt class="col-5 text-body-secondary">Period</dt><dd class="col-7">{{ $contract->start_date->format('d/m/Y') }} – {{ $contract->end_date?->format('d/m/Y') ?? 'Open-ended' }}</dd><dt class="col-5 text-body-secondary">Position</dt><dd class="col-7">{{ $contract->position_title ?: '—' }}</dd><dt class="col-5 text-body-secondary">Salary</dt><dd class="col-7">{{ number_format($contract->salary_amount, $contract->salary_currency === 'KHR' ? 0 : 2) }} {{ $contract->salary_currency }}</dd></dl>@if ($contract->document_path)<a class="btn btn-sm btn-primary mt-2" href="{{ route('contracts.download', $contract) }}"><i class="fa-solid fa-download me-1"></i>Download PDF</a>@endif</div></article></div>
        @empty
            <div class="col-12"><div class="reference-list py-5 text-center text-body-secondary">No employment contracts are available.</div></div>
        @endforelse
    </div>
    <x-pagination-footer :paginator="$contracts" />
</x-layouts::app>
