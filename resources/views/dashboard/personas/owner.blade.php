<x-workspace-command-bar title="Executive Dashboard" icon="fa-chart-line" context="Business Overview">
    <x-slot:actions>
        <div class="d-flex flex-wrap gap-2">
            @can('employee.view')<a class="btn btn-action-link btn-sm" href="{{ route('employees.index') }}"><i class="fa-solid fa-users"></i><span>Employees</span></a>@endcan
            @can('payroll.approve')<a class="btn btn-primary btn-sm" href="{{ route('payroll.review') }}"><i class="fa-solid fa-file-circle-check me-1"></i>Approval queue</a>@endcan
        </div>
    </x-slot:actions>
</x-workspace-command-bar>

<div class="workspace-summary">
    @foreach([
        ['Active employees', $metrics['employees']],
        ['Scheduled today', $metrics['scheduled']],
        ['Checked in', $metrics['present']],
        ['On leave', $metrics['leave']],
        ['Uncovered today', $metrics['absent']],
        ['Payroll awaiting approval', $metrics['pendingPayroll']],
        ['Open expense workflow', $metrics['pendingExpenses']],
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
                    <div class="small text-uppercase text-body-secondary fw-semibold">Executive attention</div>
                    <div class="fw-semibold text-dark">Approvals and open workflows</div>
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
                    <div class="small text-uppercase text-body-secondary fw-semibold">Workforce today</div>
                    <div class="fw-semibold text-dark">Recent attendance</div>
                </div>
                @can('attendance.report')<a class="btn btn-action-link btn-sm" href="{{ route('attendance.reports.index') }}">Full report</a>@endcan
            </div>
            @forelse($recentAttendances as $attendance)
                <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-truncate">{{ $attendance->employee?->getFullName() }}</div>
                        <small class="text-body-secondary">Check-in {{ $attendance->check_in_at?->format('H:i') ?? '—' }}</small>
                    </div>
                    <span class="status-text text-bg-{{ $attendance->late_minutes > 0 ? 'warning' : 'success' }}">{{ ucfirst(str_replace('_', ' ', $attendance->status)) }}</span>
                </div>
            @empty
                <x-empty-state class="py-5 px-3" icon="fa-calendar-xmark" title="No attendance yet" message="Today's attendance activity will appear here." />
            @endforelse
        </section>
    </div>
</div>
