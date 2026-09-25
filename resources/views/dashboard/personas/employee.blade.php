<x-workspace-command-bar title="My Workspace" icon="fa-user" context="Personal work">
    <x-slot:actions>
        @if(! $myAttendance?->check_in_at || ! $myAttendance?->check_out_at)
            <a class="btn btn-success btn-sm" href="{{ route('attendance.checkinout') }}"><i class="fa-solid fa-user-clock me-1"></i>{{ $myAttendance?->check_in_at ? 'Finish attendance' : 'Open attendance' }}</a>
        @endif
        <a class="btn btn-action-link btn-sm" href="{{ route('leave.requests.index') }}"><i class="fa-regular fa-calendar-plus"></i><span>Request leave</span></a>
    </x-slot:actions>
</x-workspace-command-bar>

<div class="workspace-summary">
    <div class="workspace-summary-item"><div class="label">Leave balance</div><div class="value">{{ number_format($metrics['leaveBalance'], 1) }} days</div></div>
    <div class="workspace-summary-item"><div class="label">Open tasks</div><div class="value">{{ number_format($metrics['openTasks']) }}</div></div>
    <div class="workspace-summary-item"><div class="label">Today's attendance</div><div class="value">{{ $myAttendance?->status ? str($myAttendance->status)->replace('_', ' ')->title() : 'Not started' }}</div></div>
    <div class="workspace-summary-item"><div class="label">Next shift</div><div class="value">{{ $mySchedule?->workShift?->name ?? 'Not scheduled' }}</div></div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-7">
        <section class="reference-list h-100">
            <div class="reference-list-toolbar">
                <div>
                    <div class="small text-uppercase text-body-secondary fw-semibold">Next actions</div>
                    <div class="fw-semibold text-dark">My work queue</div>
                    <div class="small text-body-secondary">Tasks that need progress or submission.</div>
                </div>
                <span class="badge rounded-pill text-bg-primary">{{ $myTasks->count() }}</span>
            </div>
            @forelse($myTasks as $task)
                <a href="{{ route('tasks.mine') }}" class="d-flex align-items-center gap-3 px-3 py-3 border-bottom text-decoration-none text-body">
                    <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary" style="width:36px;height:36px"><i class="fa-solid fa-list-check"></i></span>
                    <span class="flex-grow-1 min-w-0">
                        <span class="d-block fw-semibold text-dark">{{ $task->title }}</span>
                        <small class="text-body-secondary">Due {{ $task->due_date?->format('M d, Y') ?? 'No due date' }}</small>
                    </span>
                    <span class="btn btn-sm btn-outline-primary">Open</span>
                </a>
            @empty
                <x-empty-state class="py-5 px-3" icon="fa-inbox" tone="success" title="Inbox clear" message="You have no open tasks right now." />
            @endforelse
        </section>
    </div>
    <div class="col-12 col-xl-5">
        <section class="reference-list h-100">
            <div class="reference-list-toolbar">
                <div>
                    <div class="small text-uppercase text-body-secondary fw-semibold">Upcoming</div>
                    <div class="fw-semibold text-dark">My leave</div>
                </div>
            </div>
            @forelse($myUpcomingLeave as $leave)
                <div class="d-flex align-items-center gap-3 px-3 py-3 border-bottom">
                    <i class="fa-solid fa-calendar-check text-primary"></i>
                    <div class="flex-grow-1">
                        <div class="fw-semibold">{{ $leave->leaveType?->name ?? 'Leave' }}</div>
                        <small class="text-body-secondary">{{ $leave->start_date->format('M d') }} – {{ $leave->end_date->format('M d, Y') }}</small>
                    </div>
                    <span class="status-text text-bg-warning">{{ str($leave->status)->replace('_', ' ')->title() }}</span>
                </div>
            @empty
                <x-empty-state class="py-5 px-3" icon="fa-calendar-check" title="No upcoming leave" message="Approved and pending leave will appear here." />
            @endforelse
        </section>
    </div>
</div>
