@php
    $attendanceLabel = match(true) {
        ! $employee => 'No employee profile',
        $mySchedule?->is_rest_day => 'Rest day',
        ! $myAttendance => 'Not checked in',
        $myAttendance->check_out_at !== null => 'Shift completed',
        $myAttendance->check_in_at !== null => 'Checked in',
        default => 'Not checked in',
    };

    $attendanceTone = match($attendanceLabel) {
        'Checked in', 'Shift completed' => 'success',
        'Rest day' => 'secondary',
        'No employee profile' => 'warning',
        default => 'primary',
    };

    $todayDetail = match(true) {
        ! $employee => 'Link your user account to an employee profile to use personal attendance.',
        $mySchedule?->is_rest_day => 'No shift scheduled today.',
        $mySchedule?->workShift => $mySchedule->workShift->name.' · '.$mySchedule->workShift->getTimeRangeFormatted(),
        $myAttendance?->check_in_at => 'Checked in at '.$myAttendance->check_in_at->format('H:i'),
        default => 'No published shift for today.',
    };

    $shiftSummary = match(true) {
        ! $employee => 'Not linked',
        $mySchedule?->is_rest_day => 'Rest day',
        $mySchedule?->workShift => $mySchedule->workShift->getTimeRangeFormatted(),
        default => 'Not scheduled',
    };
@endphp

<x-layouts::app title="Dashboard">
    <section class="mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="small text-uppercase text-body-secondary fw-semibold">My workspace</div>
                <h1 class="h5 mb-1 fw-bold text-dark">Good {{ now()->hour < 12 ? 'morning' : (now()->hour < 18 ? 'afternoon' : 'evening') }}, {{ auth()->user()->name }}</h1>
                <div class="small text-body-secondary">{{ now()->format('l, d M Y') }} · Focus on what needs action today.</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @can('attendance.checkin')
                    <a class="btn btn-action-link btn-sm" href="{{ route('attendance.checkinout') }}"><i class="fa-solid fa-user-clock"></i><span>Attendance</span></a>
                @endcan
                @can('leave.request')
                    <a class="btn btn-action-link btn-sm" href="{{ route('leave.requests.index') }}"><i class="fa-regular fa-calendar-plus"></i><span>Request leave</span></a>
                @endcan
                @can('task.view-own')
                    <a class="btn btn-primary btn-sm" href="{{ route('tasks.mine') }}"><i class="fa-solid fa-list-check me-1"></i>My tasks</a>
                @endcan
            </div>
        </div>
    </section>

    <section class="reference-list mb-3" aria-labelledby="today-heading">
        <div class="reference-list-toolbar">
            <div>
                <div class="small text-uppercase text-body-secondary fw-semibold">Today</div>
                <div class="fw-semibold text-dark" id="today-heading">{{ $todayDetail }}</div>
            </div>
            <span class="status-text text-bg-{{ $attendanceTone }}">{{ $attendanceLabel }}</span>
        </div>
        <div class="workspace-summary m-0 p-2">
            <div class="workspace-summary-item">
                <div class="label">Check in</div>
                <div class="value">{{ $myAttendance?->check_in_at?->format('H:i') ?? '—' }}</div>
            </div>
            <div class="workspace-summary-item">
                <div class="label">Check out</div>
                <div class="value">{{ $myAttendance?->check_out_at?->format('H:i') ?? '—' }}</div>
            </div>
            <div class="workspace-summary-item">
                <div class="label">Today's shift</div>
                <div class="value">{{ $shiftSummary }}</div>
            </div>
            <div class="workspace-summary-item">
                <div class="label">Open tasks</div>
                <div class="value">{{ number_format($metrics['openTasks']) }}</div>
            </div>
            <div class="workspace-summary-item">
                <div class="label">Leave balance</div>
                <div class="value">{{ number_format($metrics['leaveBalance'], 1) }}</div>
            </div>
            <div class="workspace-summary-item">
                <div class="label">Upcoming leave</div>
                <div class="value">{{ number_format($myUpcomingLeave->count()) }}</div>
            </div>
        </div>
    </section>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-7">
            <section class="reference-list h-100">
                <div class="reference-list-toolbar">
                    <div>
                        <div class="small text-uppercase text-body-secondary fw-semibold">Work</div>
                        <div class="fw-semibold text-dark">My next work</div>
                    </div>
                    @can('task.view-own')<a class="btn btn-action-link btn-sm" href="{{ route('tasks.mine') }}">View all <i class="fa-solid fa-arrow-right"></i></a>@endcan
                </div>
                @forelse($myTasks as $task)
                    <a href="{{ route('tasks.mine') }}" class="d-flex align-items-center gap-3 px-3 py-3 border-bottom text-decoration-none text-body">
                        <div class="flex-grow-1 min-w-0">
                            <div class="fw-semibold text-dark text-truncate">{{ $task->title }}</div>
                            <div class="small text-body-secondary">Due {{ $task->due_date->format('d M Y') }} · {{ ucfirst($task->priority) }}</div>
                        </div>
                        <div class="text-end flex-shrink-0">
                            <div class="small fw-semibold {{ $task->effective_status === 'overdue' ? 'text-danger' : 'text-dark' }}">{{ $task->progress }}%</div>
                            <div class="small text-body-secondary">{{ str($task->effective_status)->replace('_', ' ')->title() }}</div>
                        </div>
                    </a>
                @empty
                    <x-empty-state class="py-5 px-3" icon="fa-circle-check" tone="success" title="You're caught up" message="No open tasks are currently assigned to you." />
                @endforelse
            </section>
        </div>

        <div class="col-12 col-xl-5">
            <section class="reference-list h-100">
                <div class="reference-list-toolbar">
                    <div>
                        <div class="small text-uppercase text-body-secondary fw-semibold">Leave</div>
                        <div class="fw-semibold text-dark">Upcoming leave</div>
                    </div>
                    @can('leave.request')<a class="btn btn-action-link btn-sm" href="{{ route('leave.requests.index') }}">Manage</a>@endcan
                </div>
                @forelse($myUpcomingLeave as $leave)
                    <div class="d-flex align-items-center justify-content-between gap-3 px-3 py-3 border-bottom">
                        <span>
                            <span class="d-block fw-semibold text-dark">{{ $leave->leaveType?->name ?? 'Leave' }}</span>
                            <small class="text-body-secondary">{{ $leave->start_date->format('d M') }} – {{ $leave->end_date->format('d M Y') }}</small>
                        </span>
                        <span class="status-text text-bg-{{ $leave->status === 'approved' ? 'success' : 'warning' }}">{{ str($leave->status)->replace('_',' ')->title() }}</span>
                    </div>
                @empty
                    <x-empty-state class="py-4 px-2" icon="fa-calendar-check" title="No upcoming leave" message="Approved or pending leave will appear here." />
                @endforelse
            </section>
        </div>
    </div>

    @if($isManagerOrAdmin || $actionItems->isNotEmpty())
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-2">
            <div>
                <div class="small text-uppercase text-body-secondary fw-semibold">Team operations</div>
                <h2 class="h6 fw-bold mb-0">What needs attention</h2>
            </div>
            <div class="small text-body-secondary">Live operational overview</div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-xl-7">
                <section class="reference-list h-100">
                    <div class="reference-list-toolbar"><div class="fw-semibold text-dark">Action queue</div></div>
                    @forelse($actionItems as $item)
                        <a class="d-flex align-items-center gap-3 px-3 py-3 border-bottom text-decoration-none text-body" href="{{ route($item['route']) }}">
                            <i class="fa-solid {{ $item['icon'] }} text-body-secondary"></i>
                            <span class="flex-grow-1 fw-semibold">{{ $item['label'] }}</span>
                            <span class="fw-semibold {{ $item['count'] ? 'text-warning' : 'text-success' }}">{{ $item['count'] }}</span>
                            <i class="fa-solid fa-chevron-right text-body-secondary small"></i>
                        </a>
                    @empty
                        <x-empty-state class="py-5 px-3" icon="fa-circle-check" tone="success" title="Nothing needs attention" message="There are no team actions waiting for you right now." />
                    @endforelse
                </section>
            </div>

            @if($isManagerOrAdmin)
                <div class="col-12 col-xl-5">
                    <section class="reference-list h-100">
                        <div class="reference-list-toolbar">
                            <div class="fw-semibold text-dark">Recent attendance</div>
                            @can('attendance.report')<a href="{{ route('attendance.reports.index') }}" class="btn btn-action-link btn-sm">Report</a>@endcan
                        </div>
                        @forelse($recentAttendances as $attendance)
                            <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold text-truncate">{{ $attendance->employee?->getFullName() }}</div>
                                    <small class="text-body-secondary">Check-in {{ $attendance->check_in_at?->format('H:i') ?? '—' }}</small>
                                </div>
                                <span class="status-text text-bg-success">{{ ucfirst(str_replace('_', ' ', $attendance->status)) }}</span>
                            </div>
                        @empty
                            <x-empty-state class="py-5 px-3" icon="fa-calendar-xmark" title="No attendance yet" message="Attendance activity for today will appear here." />
                        @endforelse
                    </section>
                </div>
            @endif
        </div>

        @if($isManagerOrAdmin)
            <div class="workspace-summary">
                @foreach([
                    ['Active employees', $metrics['employees']],
                    ['Scheduled today', $metrics['scheduled'] ?? '—'],
                    ['Checked in', $metrics['present']],
                    ['Late arrivals', $metrics['late']],
                    ['On leave', $metrics['leave']],
                    ['No check-out', $metrics['openCheckouts'] ?? '—'],
                    ['Uncovered', $metrics['absent'] ?? '—'],
                ] as [$label, $value])
                    <div class="workspace-summary-item">
                        <div class="label">{{ $label }}</div>
                        <div class="value">{{ $value }}</div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</x-layouts::app>
