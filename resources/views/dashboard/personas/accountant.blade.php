<x-workspace-command-bar title="Payroll & Expenses" icon="fa-money-check-dollar" context="Finance workspace">
    <x-slot:actions>
        <a class="btn btn-primary btn-sm" href="{{ route('payroll.periods.index') }}"><i class="fa-solid fa-calendar me-1"></i>Open payroll</a>
    </x-slot:actions>
</x-workspace-command-bar>

<div class="workspace-summary">
    @foreach([
        ['Payroll to process', $metrics['payrollToProcess']],
        ['Payroll awaiting approval', $metrics['payrollAwaitingApproval']],
        ['Ready to pay', $metrics['payrollReadyToPay']],
        ['Expenses to review', $metrics['expensesForAccounting']],
    ] as [$label, $value])
        <div class="workspace-summary-item">
            <div class="label">{{ $label }}</div>
            <div class="value {{ $value > 0 ? 'text-primary' : '' }}">{{ number_format($value) }}</div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-12 col-xl-7">
        <x-action-center :items="$actionItems" title="Finance work queue" description="Payroll and expense steps that are ready for your review." />
    </div>
    <div class="col-12 col-xl-5">
        <section class="reference-list h-100">
            <div class="reference-list-toolbar">
                <div>
                    <div class="small text-uppercase text-body-secondary fw-semibold">Recent cycles</div>
                    <div class="fw-semibold text-dark">Payroll activity</div>
                </div>
            </div>
            @forelse($recentPayroll as $period)
                <a class="d-flex align-items-center gap-3 px-3 py-3 border-bottom text-decoration-none text-body" href="{{ route('payroll.periods.index') }}">
                    <i class="fa-solid fa-calendar-days text-primary"></i>
                    <span class="flex-grow-1">
                        <span class="d-block fw-semibold text-dark">{{ $period->name }}</span>
                        <small class="text-body-secondary">{{ str($period->status)->replace('_', ' ')->title() }}</small>
                    </span>
                    <i class="fa-solid fa-chevron-right text-body-secondary"></i>
                </a>
            @empty
                <x-empty-state class="py-5 px-3" icon="fa-calendar-check" title="No payroll cycles" message="New payroll periods will appear here." />
            @endforelse
        </section>
    </div>
</div>
