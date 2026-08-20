<x-workspace-command-bar title="Finance Dashboard" icon="fa-calculator" context="Finance Operations">
    <x-slot:actions>
        <div class="d-flex flex-wrap gap-2">
            @can('expense.view')<a class="btn btn-action-link btn-sm" href="{{ route('expenses.index') }}"><i class="fa-solid fa-receipt"></i><span>Expenses</span></a>@endcan
            @can('payroll.view')<a class="btn btn-primary btn-sm" href="{{ route('payroll.periods.index') }}"><i class="fa-solid fa-money-check-dollar me-1"></i>Payroll</a>@endcan
        </div>
    </x-slot:actions>
</x-workspace-command-bar>

<div class="workspace-summary">
    @foreach([
        ['Payroll to process', $metrics['payrollToProcess']],
        ['Awaiting HR approval', $metrics['payrollAwaitingApproval']],
        ['Ready to pay', $metrics['payrollReadyToPay']],
        ['Expenses to review', $metrics['expensesForAccounting']],
        ['Expenses to pay', $metrics['expensesReadyToPay']],
    ] as [$label, $value])
        <div class="workspace-summary-item">
            <div class="label">{{ $label }}</div>
            <div class="value">{{ number_format($value) }}</div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-12 col-xl-7">
        <section class="reference-list h-100">
            <div class="reference-list-toolbar">
                <div>
                    <div class="small text-uppercase text-body-secondary fw-semibold">Finance queue</div>
                    <div class="fw-semibold text-dark">What needs processing</div>
                </div>
            </div>
            @foreach($actionItems as $item)
                <a class="d-flex align-items-center gap-3 px-3 py-3 border-bottom text-decoration-none text-body" href="{{ route($item['route']) }}">
                    <i class="fa-solid {{ $item['icon'] }} text-body-secondary"></i>
                    <span class="flex-grow-1 fw-semibold">{{ $item['label'] }}</span>
                    <span class="fw-semibold {{ $item['count'] > 0 ? 'text-warning' : 'text-success' }}">{{ number_format($item['count']) }}</span>
                    <i class="fa-solid fa-chevron-right text-body-secondary small"></i>
                </a>
            @endforeach
        </section>
    </div>

    <div class="col-12 col-xl-5">
        <section class="reference-list h-100">
            <div class="reference-list-toolbar">
                <div>
                    <div class="small text-uppercase text-body-secondary fw-semibold">Payroll</div>
                    <div class="fw-semibold text-dark">Recent periods</div>
                </div>
                @can('payroll.report')<a class="btn btn-action-link btn-sm" href="{{ route('payroll.reports') }}">Reports</a>@endcan
            </div>
            @forelse($recentPayroll as $period)
                <a class="d-flex align-items-center gap-3 px-3 py-3 border-bottom text-decoration-none text-body" href="{{ route('payroll.periods.index') }}">
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-dark text-truncate">{{ $period->name }}</div>
                        <small class="text-body-secondary">{{ $period->start_date->format('d M') }} – {{ $period->end_date->format('d M Y') }}</small>
                    </div>
                    <span class="status-text text-bg-{{ in_array($period->status, ['paid','closed'], true) ? 'success' : ($period->status === 'awaiting_approval' ? 'warning' : 'primary') }}">{{ str($period->status)->replace('_', ' ')->title() }}</span>
                </a>
            @empty
                <x-empty-state class="py-5 px-3" icon="fa-money-check-dollar" title="No payroll periods" message="Payroll periods will appear here once created." />
            @endforelse
        </section>
    </div>
</div>
