<x-workspace-command-bar title="Team Dashboard" icon="fa-users" context="Team Operations">
    <x-slot:actions>
        <div class="d-flex flex-wrap gap-2">
            @can('task.assign')<a class="btn btn-action-link btn-sm" href="{{ route('tasks.index') }}"><i class="fa-solid fa-list-check"></i><span>Tasks</span></a>@endcan
            @can('leave.approve')<a class="btn btn-primary btn-sm" href="{{ route('leave.requests.review') }}"><i class="fa-solid fa-calendar-check me-1"></i>Review leave</a>@endcan
        </div>
    </x-slot:actions>
</x-workspace-command-bar>

@if($managerContextMissing)
    <div class="alert alert-warning d-flex align-items-start gap-2">
        <i class="fa-solid fa-triangle-exclamation mt-1"></i>
        <div>
            <div class="fw-semibold">Manager team scope is not configured</div>
            <div class="small">Link this manager account to an active employee record with a department before using team-level dashboard data.</div>
        </div>
    </div>
@endif

<div class="workspace-summary">
    @foreach([
        ['Team members', $metrics['employees']],
        ['Scheduled today', $metrics['scheduled']],
        ['Checked in', $metrics['present']],
        ['Late', $metrics['late']],
        ['On leave', $metrics['leave']],
        ['Open tasks', $metrics['openTasks']],
        ['Needs verification', $metrics['waitingVerification']],
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
                    <div class="small text-uppercase text-body-secondary fw-semibold">Team queue</div>
                    <div class="fw-semibold text-dark">What needs your action</div>
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
                    <div class="small text-uppercase text-body-secondary fw-semibold">Today</div>
                    <div class="fw-semibold text-dark">Team attendance</div>
                </div>
                @can('attendance.report')<a class="btn btn-action-link btn-sm" href="{{ route('attendance.reports.index') }}">Report</a>@endcan
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
                <x-empty-state class="py-5 px-3" icon="fa-calendar-xmark" title="No attendance yet" message="Today's team attendance will appear here." />
            @endforelse
        </section>
    </div>
</div>
