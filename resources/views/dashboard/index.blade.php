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
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('attendance.checkinout') }}"><i class="fa-solid fa-user-clock me-1"></i>Attendance</a>
                @endcan
                @can('leave.request')
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('leave.requests.index') }}"><i class="fa-regular fa-calendar-plus me-1"></i>Request leave</a>
                @endcan
                @can('task.view-own')
                    <a class="btn btn-primary btn-sm" href="{{ route('tasks.mine') }}"><i class="fa-solid fa-list-check me-1"></i>My tasks</a>
                @endcan
            </div>
        </div>
    </section>

    <div class="row g-3 mb-3">
        <div class="col-12 col-xl-5">
            <section class="profile-card h-100 mb-0">
                <div class="profile-card-header">
                    <h2 class="profile-card-title"><i class="fa-solid fa-sun text-primary"></i><span>Today</span></h2>
                    <span class="status-text text-bg-{{ $attendanceTone }}">{{ $attendanceLabel }}</span>
                </div>
                <div class="profile-card-body">
                    <div class="d-flex align-items-start gap-3">
                        <span class="metric-icon bg-{{ $attendanceTone }}-subtle text-{{ $attendanceTone }} flex-shrink-0"><i class="fa-solid fa-user-clock"></i></span>
                        <div class="min-w-0">
                            <div class="fw-bold text-dark">{{ $attendanceLabel }}</div>
                            <div class="small text-body-secondary mt-1">{{ $todayDetail }}</div>
                            @if($myAttendance?->check_in_at)
                                <div class="d-flex flex-wrap gap-3 mt-3 small">
                                    <div><span class="text-body-secondary d-block">Check in</span><strong>{{ $myAttendance->check_in_at->format('H:i') }}</strong></div>
                                    <div><span class="text-body-secondary d-block">Check out</span><strong>{{ $myAttendance->check_out_at?->format('H:i') ?? '—' }}</strong></div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-7">
            <div class="row g-3 h-100">
                <div class="col-6 col-lg-3">
                    <section class="profile-card h-100 mb-0"><div class="profile-card-body">
                        <div class="small text-body-secondary">Open tasks</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($metrics['openTasks']) }}</div>
                        <div class="small text-body-secondary mt-2">Assigned to you</div>
                    </div></section>
                </div>
                <div class="col-6 col-lg-3">
                    <section class="profile-card h-100 mb-0"><div class="profile-card-body">
                        <div class="small text-body-secondary">Leave balance</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($metrics['leaveBalance'], 1) }}</div>
                        <div class="small text-body-secondary mt-2">Days available</div>
                    </div></section>
                </div>
                <div class="col-6 col-lg-3">
                    <section class="profile-card h-100 mb-0"><div class="profile-card-body">
                        <div class="small text-body-secondary">Today's shift</div>
                        <div class="fs-6 fw-bold text-dark mt-1">{{ $shiftSummary }}</div>
                        <div class="small text-body-secondary mt-2">Published schedule</div>
                    </div></section>
                </div>
                <div class="col-6 col-lg-3">
                    <section class="profile-card h-100 mb-0"><div class="profile-card-body">
                        <div class="small text-body-secondary">Upcoming leave</div>
                        <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($myUpcomingLeave->count()) }}</div>
                        <div class="small text-body-secondary mt-2">Requests ahead</div>
                    </div></section>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-7">
            <section class="profile-card h-100 mb-0">
                <div class="profile-card-header">
                    <h2 class="profile-card-title"><i class="fa-solid fa-bolt text-primary"></i><span>My next work</span></h2>
                    @can('task.view-own')<a class="btn btn-action-link btn-sm" href="{{ route('tasks.mine') }}">View all <i class="fa-solid fa-arrow-right"></i></a>@endcan
                </div>
                <div class="profile-card-body p-0">
                    @forelse($myTasks as $task)
                        <a href="{{ route('tasks.mine') }}" class="d-flex align-items-center gap-3 px-3 py-3 border-bottom text-decoration-none text-body">
                            <span class="metric-icon {{ $task->effective_status === 'overdue' ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary' }} flex-shrink-0"><i class="fa-regular fa-circle-check"></i></span>
                            <div class="flex-grow-1 min-w-0">
                                <div class="fw-semibold text-dark text-truncate">{{ $task->title }}</div>
                                <div class="small text-body-secondary">Due {{ $task->due_date->format('d M Y') }} · {{ ucfirst($task->priority) }}</div>
                            </div>
                            <div class="text-end flex-shrink-0">
                                <div class="small fw-semibold {{ $task->effective_status === 'overdue' ? 'text-danger' : 'text-dark' }}">{{ $task->progress }}%</div>
                                <i class="fa-solid fa-chevron-right text-body-secondary small"></i>
                            </div>
                        </a>
                    @empty
                        <x-empty-state class="py-5 px-3" icon="fa-circle-check" tone="success" title="You're caught up" message="No open tasks are currently assigned to you." />
                    @endforelse
                </div>
            </section>
        </div>

        <div class="col-12 col-xl-5">
            <section class="profile-card h-100 mb-0">
                <div class="profile-card-header">
                    <h2 class="profile-card-title"><i class="fa-regular fa-calendar text-primary"></i><span>Upcoming leave</span></h2>
                    @can('leave.request')<a class="btn btn-action-link btn-sm" href="{{ route('leave.requests.index') }}">Manage</a>@endcan
                </div>
                <div class="profile-card-body">
                    @forelse($myUpcomingLeave as $leave)
                        <div class="profile-kv-row">
                            <span>
                                <span class="d-block fw-semibold text-dark">{{ $leave->leaveType?->name ?? 'Leave' }}</span>
                                <small class="text-body-secondary">{{ $leave->start_date->format('d M') }} – {{ $leave->end_date->format('d M Y') }}</small>
                            </span>
                            <span class="status-text text-bg-{{ $leave->status === 'approved' ? 'success' : 'warning' }}">{{ str($leave->status)->replace('_',' ')->title() }}</span>
                        </div>
                    @empty
                        <x-empty-state class="py-4 px-2" icon="fa-calendar-check" title="No upcoming leave" message="Approved or pending leave will appear here." />
                    @endforelse
                </div>
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
                <section class="profile-card h-100 mb-0">
                    <div class="profile-card-header"><h2 class="profile-card-title"><i class="fa-solid fa-inbox text-primary"></i><span>Action queue</span></h2></div>
                    <div>
                        @forelse($actionItems as $item)
                            <a class="d-flex align-items-center gap-3 px-3 py-3 border-bottom text-decoration-none text-body" href="{{ route($item['route']) }}">
                                <span class="metric-icon {{ $item['count'] ? 'bg-warning-subtle text-warning' : 'bg-success-subtle text-success' }} flex-shrink-0"><i class="fa-solid {{ $item['icon'] }}"></i></span>
                                <span class="flex-grow-1 fw-semibold">{{ $item['label'] }}</span>
                                <span class="fw-bold {{ $item['count'] ? 'text-warning' : 'text-success' }}">{{ $item['count'] }}</span>
                                <i class="fa-solid fa-chevron-right text-body-secondary small"></i>
                            </a>
                        @empty
                            <x-empty-state class="py-5 px-3" icon="fa-circle-check" tone="success" title="Nothing needs attention" message="There are no team actions waiting for you right now." />
                        @endforelse
                    </div>
                </section>
            </div>

            @if($isManagerOrAdmin)
                <div class="col-12 col-xl-5">
                    <section class="profile-card h-100 mb-0">
                        <div class="profile-card-header">
                            <h2 class="profile-card-title"><i class="fa-solid fa-users-viewfinder text-primary"></i><span>Recent attendance</span></h2>
                            @can('attendance.report')<a href="{{ route('attendance.reports.index') }}" class="btn btn-action-link btn-sm">Report</a>@endcan
                        </div>
                        <div class="profile-card-body p-0">
                            @forelse($recentAttendances as $attendance)
                                <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
                                    <span class="metric-icon bg-success-subtle text-success flex-shrink-0"><i class="fa-solid fa-user-check"></i></span>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="fw-semibold text-truncate">{{ $attendance->employee?->getFullName() }}</div>
                                        <small class="text-body-secondary">Check-in {{ $attendance->check_in_at?->format('H:i') ?? '—' }}</small>
                                    </div>
                                    <span class="status-text text-bg-success">{{ ucfirst(str_replace('_', ' ', $attendance->status)) }}</span>
                                </div>
                            @empty
                                <x-empty-state class="py-5 px-3" icon="fa-calendar-xmark" title="No attendance yet" message="Attendance activity for today will appear here." />
                            @endforelse
                        </div>
                    </section>
                </div>
            @endif
        </div>

        @if($isManagerOrAdmin)
            <div class="row g-2">
                @foreach([
                    ['Active employees', $metrics['employees'], 'fa-users', 'primary'],
                    ['Scheduled today', $metrics['scheduled'] ?? '—', 'fa-calendar-day', 'info'],
                    ['Checked in', $metrics['present'], 'fa-user-check', 'success'],
                    ['Late arrivals', $metrics['late'], 'fa-clock', 'warning'],
                    ['On leave', $metrics['leave'], 'fa-calendar-xmark', 'secondary'],
                    ['No check-out', $metrics['openCheckouts'] ?? '—', 'fa-right-from-bracket', 'warning'],
                    ['Uncovered', $metrics['absent'] ?? '—', 'fa-user-xmark', 'danger'],
                ] as [$label, $value, $icon, $tone])
                    <div class="col-6 col-md-4 col-xl">
                        <section class="profile-card h-100 mb-0">
                            <div class="profile-card-body py-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="metric-icon bg-{{ $tone }}-subtle text-{{ $tone }}"><i class="fa-solid {{ $icon }}"></i></span>
                                    <div class="min-w-0">
                                        <div class="small text-body-secondary text-truncate">{{ $label }}</div>
                                        <div class="fs-5 fw-bold text-dark">{{ $value }}</div>
                                    </div>
                                </div>
                            </div>
                        </section>
                    </div>
                @endforeach
            </div>
        @endif
    @endif
</x-layouts::app>
