@php
    $metricWidgets = [
        'active_employees' => ['Active employees', $metrics['employees'], 'fa-users', 'primary', 'Current workforce'],
        'scheduled_today' => ['Scheduled today', $metrics['scheduled'] ?? '—', 'fa-calendar-day', 'info', 'Excludes rest days'],
        'checked_in' => ['Checked in', $metrics['present'], 'fa-user-check', 'success', 'Present and working today'],
        'late_arrivals' => ['Late arrivals', $metrics['late'], 'fa-clock', 'warning', 'After the grace period'],
        'approved_leave' => ['On approved leave', $metrics['leave'], 'fa-calendar-xmark', 'secondary', 'Approved leave today'],
        'no_checkout' => ['No check-out', $metrics['openCheckouts'] ?? '—', 'fa-right-from-bracket', 'warning', 'Needs follow-up'],
        'uncovered_schedule' => ['Uncovered schedule', $metrics['absent'] ?? '—', 'fa-user-xmark', 'danger', 'Scheduled without attendance'],
    ];
    $widgetLabels = collect($metricWidgets)->mapWithKeys(fn ($widget, $key) => [$key => $widget[0]])->merge([
        'action_queue' => 'Needs attention',
        'recent_attendance' => 'Recent attendance',
    ]);
    $dashboardPreferences = auth()->user()->dashboard_preferences ?? ['order' => [], 'hidden' => []];
    $attendanceLabel = match(true) {
        !$employee => 'No employee profile',
        $mySchedule?->is_rest_day => 'Rest day',
        !$myAttendance => 'Not checked in',
        $myAttendance->check_out_at !== null => 'Shift completed',
        $myAttendance->check_in_at !== null => 'Checked in',
        default => 'Not checked in',
    };
@endphp

<x-layouts::app title="Dashboard">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <div class="small text-uppercase text-body-secondary fw-semibold">My workspace</div>
            <h1 class="h5 mb-0 fw-bold text-dark">{{ auth()->user()->name }} <span class="text-body-secondary fw-normal">· {{ now()->format('D, d M Y') }}</span></h1>
        </div>
        <div class="d-flex flex-wrap gap-2">
            @can('attendance.checkin')<a class="btn btn-outline-primary btn-sm" href="{{ route('attendance.checkinout') }}"><i class="fa-solid fa-user-clock me-1"></i>Attendance</a>@endcan
            @can('leave.request')<a class="btn btn-outline-primary btn-sm" href="{{ route('leave.requests.index') }}"><i class="fa-regular fa-calendar-plus me-1"></i>Leave</a>@endcan
            @can('task.view-own')<a class="btn btn-primary btn-sm" href="{{ route('tasks.mine') }}"><i class="fa-solid fa-list-check me-1"></i>My tasks</a>@endcan
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6 col-xl-3">
            <div class="profile-card h-100 mb-0"><div class="profile-card-body">
                <div class="d-flex align-items-center gap-3"><span class="metric-icon bg-primary-subtle text-primary"><i class="fa-solid fa-user-clock"></i></span><div><div class="small text-body-secondary">Today</div><div class="fw-bold">{{ $attendanceLabel }}</div></div></div>
                @if($employee)<div class="small text-body-secondary mt-3">@if($mySchedule?->is_rest_day)No shift scheduled.@elseif($mySchedule?->workShift){{ $mySchedule->workShift->name }} · {{ $mySchedule->workShift->getTimeRangeFormatted() }}@elseif($myAttendance?->check_in_at)Check-in {{ $myAttendance->check_in_at->format('H:i') }}@elseNo published shift for today.@endif</div>@endif
            </div></div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="profile-card h-100 mb-0"><div class="profile-card-body">
                <div class="d-flex align-items-center gap-3"><span class="metric-icon bg-info-subtle text-info"><i class="fa-solid fa-list-check"></i></span><div><div class="small text-body-secondary">Open work</div><div class="fs-4 fw-bold">{{ $metrics['openTasks'] }}</div></div></div>
                <div class="small text-body-secondary mt-3">Tasks assigned to you that are not closed.</div>
            </div></div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="profile-card h-100 mb-0"><div class="profile-card-body">
                <div class="d-flex align-items-center gap-3"><span class="metric-icon bg-success-subtle text-success"><i class="fa-solid fa-umbrella-beach"></i></span><div><div class="small text-body-secondary">Leave available</div><div class="fs-4 fw-bold">{{ number_format($metrics['leaveBalance'], 1) }}</div></div></div>
                <div class="small text-body-secondary mt-3">Remaining days across your leave balances.</div>
            </div></div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="profile-card h-100 mb-0"><div class="profile-card-body">
                <div class="d-flex align-items-center gap-3"><span class="metric-icon bg-secondary-subtle text-secondary"><i class="fa-solid fa-cloud-arrow-down"></i></span><div><div class="small text-body-secondary">Exports running</div><div class="fs-4 fw-bold">{{ $metrics['pendingExports'] }}</div></div></div>
                <div class="small text-body-secondary mt-3">Queued or processing exports requested by you.</div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-header"><h2 class="profile-card-title"><i class="fa-solid fa-bolt text-primary"></i><span>My next work</span></h2>@can('task.view-own')<a class="btn btn-action-link btn-sm" href="{{ route('tasks.mine') }}">View all <i class="fa-solid fa-arrow-right"></i></a>@endcan</div>
                <div class="profile-card-body p-0">
                    @forelse($myTasks as $task)
                        <div class="d-flex align-items-start gap-3 px-3 py-3 border-bottom">
                            <span class="mt-1 {{ $task->effective_status === 'overdue' ? 'text-danger' : 'text-primary' }}"><i class="fa-regular fa-circle-check"></i></span>
                            <div class="flex-grow-1 min-w-0"><div class="fw-semibold text-dark">{{ $task->title }}</div><div class="small text-body-secondary">Due {{ $task->due_date->format('d M Y') }} · {{ ucfirst($task->priority) }} priority</div></div>
                            <span class="small fw-semibold {{ $task->effective_status === 'overdue' ? 'text-danger' : 'text-body-secondary' }}">{{ $task->progress }}%</span>
                        </div>
                    @empty
                        <div class="text-center text-body-secondary py-5"><i class="fa-solid fa-circle-check text-success d-block mb-2"></i>No open tasks assigned to you.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-header"><h2 class="profile-card-title"><i class="fa-regular fa-calendar text-primary"></i><span>Upcoming leave</span></h2>@can('leave.request')<a class="btn btn-action-link btn-sm" href="{{ route('leave.requests.index') }}">Manage</a>@endcan</div>
                <div class="profile-card-body">
                    @forelse($myUpcomingLeave as $leave)
                        <div class="profile-kv-row"><span><span class="d-block fw-semibold text-dark">{{ $leave->leaveType?->name ?? 'Leave' }}</span><small class="text-body-secondary">{{ $leave->start_date->format('d M') }} – {{ $leave->end_date->format('d M Y') }}</small></span><span class="status-text text-bg-{{ $leave->status === 'approved' ? 'success' : 'warning' }}">{{ str($leave->status)->replace('_',' ')->title() }}</span></div>
                    @empty
                        <div class="text-center text-body-secondary py-4">No upcoming leave requests.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @if($isManagerOrAdmin || $actionItems->isNotEmpty())
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <div><div class="small text-uppercase text-body-secondary fw-semibold">Team operations</div><h2 class="h6 fw-bold mb-0">What needs attention</h2></div>
            <div class="dashboard-view-actions"><button class="btn btn-action-link btn-sm" type="button" data-dashboard-edit><i class="fa-solid fa-sliders"></i><span>Customize</span></button></div>
            <div class="dashboard-edit-actions" hidden>
                <div class="dropdown"><button class="btn btn-action-link btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="fa-solid fa-circle-plus"></i><span>Widgets</span></button><div class="dropdown-menu dropdown-menu-end dashboard-widget-menu p-2">@foreach($widgetLabels as $key => $label)@if($key !== 'recent_attendance' || $isManagerOrAdmin)<label class="dropdown-item-text form-check"><input class="form-check-input me-2" type="checkbox" value="{{ $key }}" data-dashboard-widget-toggle><span>{{ $label }}</span></label>@endif @endforeach</div></div>
                <button class="btn btn-action-link btn-sm" type="button" data-dashboard-auto>Auto organize</button><button class="btn btn-action-link btn-sm" type="button" data-dashboard-save>Save</button><button class="btn btn-action-link btn-sm text-danger" type="button" data-dashboard-reset>Reset</button><button class="btn btn-action-link btn-sm text-danger" type="button" data-dashboard-cancel>Cancel</button>
            </div>
        </div>

        <div class="dashboard-grid row g-3" data-dashboard-grid>
            @if($isManagerOrAdmin)
                @foreach($metricWidgets as $key => [$label, $value, $icon, $tone, $hint])
                    <section class="col-sm-6 col-xl-3 dashboard-widget" data-widget="{{ $key }}" data-default-order="{{ $loop->index }}" draggable="false"><div class="profile-card h-100 mb-0"><button class="dashboard-widget-remove" type="button" aria-label="Hide {{ $label }}" data-widget-remove hidden><i class="fa-solid fa-xmark"></i></button><span class="dashboard-widget-handle" hidden><i class="fa-solid fa-grip"></i></span><div class="profile-card-body d-flex align-items-center gap-3"><span class="metric-icon bg-{{ $tone }}-subtle text-{{ $tone }}"><i class="fa-solid {{ $icon }}"></i></span><div><div class="small text-body-secondary">{{ $label }}</div><div class="fs-4 fw-bold">{{ $value }}</div><small class="text-body-secondary">{{ $hint }}</small></div></div></div></section>
                @endforeach
            @endif

            <section class="col-lg-7 dashboard-widget" data-widget="action_queue" data-default-order="8" draggable="false"><div class="profile-card h-100 mb-0"><button class="dashboard-widget-remove" type="button" aria-label="Hide needs attention" data-widget-remove hidden><i class="fa-solid fa-xmark"></i></button><span class="dashboard-widget-handle" hidden><i class="fa-solid fa-grip"></i></span><div class="profile-card-header"><h2 class="profile-card-title"><i class="fa-solid fa-inbox text-primary"></i><span>Needs attention</span></h2></div><div>@forelse($actionItems as $item)<a class="d-flex align-items-center gap-3 px-3 py-3 border-bottom text-decoration-none text-body" href="{{ route($item['route']) }}"><span class="metric-icon bg-primary-subtle text-primary"><i class="fa-solid {{ $item['icon'] }}"></i></span><span class="flex-grow-1 fw-semibold">{{ $item['label'] }}</span><span class="{{ $item['count'] ? 'text-warning' : 'text-success' }} fw-bold">{{ $item['count'] }}</span><i class="fa-solid fa-chevron-right text-body-secondary small"></i></a>@empty<div class="text-center text-body-secondary py-5">Nothing needs your attention right now.</div>@endforelse</div></div></section>

            @if($isManagerOrAdmin)<section class="col-lg-5 dashboard-widget" data-widget="recent_attendance" data-default-order="9" draggable="false"><div class="profile-card h-100 mb-0"><button class="dashboard-widget-remove" type="button" aria-label="Hide recent attendance" data-widget-remove hidden><i class="fa-solid fa-xmark"></i></button><span class="dashboard-widget-handle" hidden><i class="fa-solid fa-grip"></i></span><div class="profile-card-header"><h2 class="profile-card-title"><i class="fa-solid fa-users-viewfinder text-primary"></i><span>Recent attendance</span></h2>@can('attendance.report')<a href="{{ route('attendance.reports.index') }}" class="btn btn-action-link btn-sm">Report</a>@endcan</div><div class="profile-card-body p-0">@forelse($recentAttendances as $attendance)<div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom"><div class="flex-grow-1"><div class="fw-semibold">{{ $attendance->employee?->getFullName() }}</div><small class="text-body-secondary">Check-in {{ $attendance->check_in_at?->format('H:i') ?? '—' }}</small></div><span class="status-text text-bg-success">{{ ucfirst(str_replace('_', ' ', $attendance->status)) }}</span></div>@empty<div class="text-center text-body-secondary py-5">No attendance records today.</div>@endforelse</div></div></section>@endif
        </div>
    @endif

    <script nonce="{{ request()->attributes->get('csp_nonce') }}" type="application/json" id="dashboard-preferences">@json($dashboardPreferences)</script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">window.dashboardPreferenceUrl = @json(route('preferences.dashboard.update')); window.dashboardResetUrl = @json(route('preferences.dashboard.destroy'));</script>
</x-layouts::app>
