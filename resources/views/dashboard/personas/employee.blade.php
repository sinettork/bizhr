@php
    $attendanceLabel = match(true) {
        ! $employee => 'Profile not linked',
        $mySchedule?->is_rest_day => 'Rest day',
        ! $myAttendance => 'Not checked in',
        $myAttendance->check_out_at !== null => 'Shift completed',
        $myAttendance->check_in_at !== null => 'Checked in',
        default => 'Not checked in',
    };

    $attendanceTone = match($attendanceLabel) {
        'Checked in', 'Shift completed' => 'success',
        'Rest day' => 'secondary',
        'Profile not linked' => 'warning',
        default => 'primary',
    };

    $todayDetail = match(true) {
        ! $employee => 'Your account is not linked to an active employee profile.',
        $mySchedule?->is_rest_day => 'No shift is scheduled today.',
        $mySchedule?->workShift => $mySchedule->workShift->name.' · '.$mySchedule->workShift->getTimeRangeFormatted(),
        $myAttendance?->check_in_at => 'Checked in at '.$myAttendance->check_in_at->format('H:i'),
        default => 'No published shift for today.',
    };
@endphp

<x-workspace-command-bar title="My Dashboard" icon="fa-user-check" context="Personal Workspace">
    <x-slot:actions>
        @if($employee)
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
        @endif
    </x-slot:actions>
</x-workspace-command-bar>

@if(! $employee)
    <div class="alert alert-warning d-flex align-items-start gap-2">
        <i class="fa-solid fa-triangle-exclamation mt-1"></i>
        <div>
            <div class="fw-semibold">Employee profile required</div>
            <div class="small">Personal attendance, leave, tasks, payslips and other self-service tools require an active employee record linked to this account.</div>
        </div>
    </div>
@else
    <section class="reference-list mb-3" aria-labelledby="employee-today-heading">
        <div class="reference-list-toolbar">
            <div>
                <div class="small text-uppercase text-body-secondary fw-semibold">Today</div>
                <div class="fw-semibold text-dark" id="employee-today-heading">{{ $todayDetail }}</div>
            </div>
            <span class="status-text text-bg-{{ $attendanceTone }}">{{ $attendanceLabel }}</span>
        </div>
        <div class="workspace-summary m-0 p-2">
            @foreach([
                ['Check in', $myAttendance?->check_in_at?->format('H:i') ?? '—'],
                ['Check out', $myAttendance?->check_out_at?->format('H:i') ?? '—'],
                ['Open tasks', number_format($metrics['openTasks'])],
                ['Leave balance', number_format($metrics['leaveBalance'], 1)],
                ['Upcoming leave', number_format($myUpcomingLeave->count())],
            ] as [$label, $value])
                <div class="workspace-summary-item">
                    <div class="label">{{ $label }}</div>
                    <div class="value">{{ $value }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="row g-3">
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
@endif
