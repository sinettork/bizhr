<x-workspace-command-bar title="People Operations" icon="fa-address-card" context="HR workspace">
    <x-slot:actions>
        <a class="btn btn-primary btn-sm" href="{{ route('employees.create') }}"><i class="fa-solid fa-user-plus me-1"></i>Start onboarding</a>
    </x-slot:actions>
</x-workspace-command-bar>

<div class="workspace-summary">
    @foreach([
        ['Employees', $metrics['employees']],
        ['Pending leave', $metrics['pendingLeaveApprovals']],
        ['Pending contracts', $metrics['pendingContracts']],
        ['Expiring contracts', $metrics['contractsExpiringSoon']],
    ] as [$label, $value])
        <div class="workspace-summary-item">
            <div class="label">{{ $label }}</div>
            <div class="value {{ str_contains($label, 'Expiring') && $value > 0 ? 'text-danger' : '' }}">{{ number_format($value) }}</div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-12 col-xl-7">
        <x-action-center :items="$actionItems" title="People decisions" description="Approvals and deadlines that keep the employee lifecycle moving." />
    </div>
    <div class="col-12 col-xl-5">
        <section class="reference-list h-100">
            <div class="reference-list-toolbar">
                <div>
                    <div class="small text-uppercase text-body-secondary fw-semibold">Today</div>
                    <div class="fw-semibold text-dark">Attendance exceptions</div>
                </div>
                <a class="btn btn-action-link btn-sm" href="{{ route('attendance.reports.index') }}">Open report</a>
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
                <x-empty-state class="py-5 px-3" icon="fa-circle-check" tone="success" title="No exceptions" message="Attendance records requiring attention will appear here." />
            @endforelse
        </section>
    </div>
</div>
