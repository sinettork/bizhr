<x-workspace-command-bar title="Executive Overview" icon="fa-building" context="Company decisions">
    <x-slot:actions>
        <a class="btn btn-primary btn-sm" href="{{ route('payroll.reports') }}"><i class="fa-solid fa-chart-pie me-1"></i>View workforce report</a>
    </x-slot:actions>
</x-workspace-command-bar>

<div class="workspace-summary">
    @foreach([
        ['Employees', $metrics['employees']],
        ['Present today', $metrics['present']],
        ['Leave approvals', $metrics['pendingLeaveApprovals']],
        ['Payroll approvals', $metrics['pendingPayroll']],
    ] as [$label, $value])
        <div class="workspace-summary-item">
            <div class="label">{{ $label }}</div>
            <div class="value {{ in_array($label, ['Leave approvals', 'Payroll approvals'], true) && $value > 0 ? 'text-warning' : '' }}">{{ number_format($value) }}</div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-12 col-xl-7">
        <x-action-center :items="$actionItems" title="Executive decisions" description="Approvals with a direct impact on people, pay, and risk." />
    </div>
    <div class="col-12 col-xl-5">
        <section class="reference-list h-100">
            <div class="reference-list-toolbar">
                <div>
                    <div class="small text-uppercase text-body-secondary fw-semibold">Workforce pulse</div>
                    <div class="fw-semibold text-dark">Recent attendance</div>
                </div>
            </div>
            @forelse($recentAttendances as $attendance)
                <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-dark">{{ $attendance->employee?->full_name_en ?? $attendance->employee?->employee_code }}</div>
                        <small class="text-body-secondary">{{ str($attendance->status)->replace('_', ' ')->title() }}</small>
                    </div>
                    <span class="status-text text-bg-{{ $attendance->status === 'present' ? 'success' : 'warning' }}">{{ str($attendance->status)->replace('_', ' ')->title() }}</span>
                </div>
            @empty
                <x-empty-state class="py-5 px-3" icon="fa-chart-line" title="No attendance activity" message="Workforce activity will appear here." />
            @endforelse
        </section>
    </div>
</div>
