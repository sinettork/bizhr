<x-workspace-command-bar title="Team Operations" icon="fa-users" context="Manager workspace">
    <x-slot:actions>
        <a class="btn btn-action-link btn-sm" href="{{ route('schedules.index') }}"><i class="fa-solid fa-calendar-days"></i><span>Team schedule</span></a>
    </x-slot:actions>
</x-workspace-command-bar>

<div class="workspace-summary">
    @foreach([
        ['Team members', $metrics['employees']],
        ['Scheduled today', $metrics['scheduled']],
        ['Present today', $metrics['present']],
        ['Exceptions', $metrics['pendingCorrections'] + $metrics['absent']],
    ] as [$label, $value])
        <div class="workspace-summary-item">
            <div class="label">{{ $label }}</div>
            <div class="value {{ $label === 'Exceptions' && $value > 0 ? 'text-warning' : '' }}">{{ number_format($value) }}</div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-12 col-xl-7">
        <x-action-center :items="$actionItems" title="Team decisions" description="Approvals and checks that are waiting for you." />
    </div>
    <div class="col-12 col-xl-5">
        <section class="reference-list h-100">
            <div class="reference-list-toolbar">
                <div>
                    <div class="small text-uppercase text-body-secondary fw-semibold">Today</div>
                    <div class="fw-semibold text-dark">Attendance pulse</div>
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
                <x-empty-state class="py-5 px-3" icon="fa-user-clock" title="No attendance yet" message="Your team's attendance records will appear here." />
            @endforelse
        </section>
    </div>
</div>
