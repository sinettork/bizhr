@php
    $metricWidgets = [
        'active_employees' => ['Active employees', $metrics['employees'], 'fa-users', 'primary', 'Current workforce'],
        'scheduled_today' => ['Scheduled today', $metrics['scheduled'] ?? '—', 'fa-calendar-day', 'info', 'Excludes rest days'],
        'checked_in' => ['Checked in', $metrics['present'], 'fa-user-check', 'success', 'Present, late, remote and trip'],
        'late_arrivals' => ['Late arrivals', $metrics['late'], 'fa-clock', 'warning', 'After the configured grace period'],
        'approved_leave' => ['On approved leave', $metrics['leave'], 'fa-calendar-xmark', 'secondary', 'Approved leave covering today'],
        'no_checkout' => ['No check-out', $metrics['openCheckouts'] ?? '—', 'fa-right-from-bracket', 'warning', 'Needs attendance follow-up'],
        'uncovered_schedule' => ['Uncovered schedule', $metrics['absent'] ?? '—', 'fa-user-xmark', 'danger', 'Scheduled but no check-in or leave'],
        'open_tasks' => ['My open tasks', $metrics['openTasks'], 'fa-list-check', 'primary', 'Assigned work not closed'],
    ];
    $widgetLabels = collect($metricWidgets)->mapWithKeys(fn ($widget, $key) => [$key => $widget[0]])->merge([
        'action_queue' => 'Action queue', 'my_work' => 'My work summary', 'recent_attendance' => 'Recent attendance',
    ]);
    $dashboardPreferences = auth()->user()->dashboard_preferences ?? ['order' => [], 'hidden' => []];
@endphp

<x-layouts::app title="Dashboard">
    <x-workspace-command-bar title="Dashboard" icon="fa-chart-line">
        <x-slot:filters><span class="small text-body-secondary">Operational overview · {{ now()->format('d M Y') }}</span></x-slot:filters>
        <x-slot:actions>
            <div class="dashboard-view-actions"><button class="btn btn-action-link btn-sm" type="button" data-dashboard-edit><i class="fa-solid fa-pen"></i><span>Edit dashboard</span></button></div>
            <div class="dashboard-edit-actions" hidden>
                <div class="dropdown"><button class="btn btn-action-link btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="fa-solid fa-circle-plus"></i><span>Add widget</span></button><div class="dropdown-menu dropdown-menu-end dashboard-widget-menu p-2">@foreach($widgetLabels as $key => $label)@if($key !== 'recent_attendance' || $isManagerOrAdmin)<label class="dropdown-item-text form-check"><input class="form-check-input me-2" type="checkbox" value="{{ $key }}" data-dashboard-widget-toggle><span>{{ $label }}</span></label>@endif @endforeach</div></div>
                <button class="btn btn-action-link btn-sm" type="button" data-dashboard-auto><i class="fa-solid fa-wand-magic-sparkles"></i><span>Auto organize</span></button>
                <button class="btn btn-action-link btn-sm" type="button" data-dashboard-save><i class="fa-solid fa-floppy-disk"></i><span>Save</span></button>
                <button class="btn btn-action-link btn-sm text-danger" type="button" data-dashboard-reset><i class="fa-solid fa-rotate-left"></i><span>Reset</span></button>
                <button class="btn btn-action-link btn-sm text-danger" type="button" data-dashboard-cancel><i class="fa-solid fa-xmark"></i><span>Cancel</span></button>
            </div>
        </x-slot:actions>
    </x-workspace-command-bar>

    <div class="dashboard-grid row g-3" data-dashboard-grid>
        @foreach($metricWidgets as $key => [$label, $value, $icon, $tone, $hint])
            @continue(!$isManagerOrAdmin && $key !== 'open_tasks')
            <section class="col-sm-6 col-xl-3 dashboard-widget" data-widget="{{ $key }}" data-default-order="{{ $loop->index }}" draggable="false">
                <div class="card h-100 shadow-sm border-0"><button class="dashboard-widget-remove" type="button" aria-label="Hide {{ $label }}" data-widget-remove hidden><i class="fa-solid fa-xmark"></i></button><span class="dashboard-widget-handle" hidden><i class="fa-solid fa-grip"></i></span><div class="card-body d-flex align-items-center gap-3"><span class="metric-icon bg-{{ $tone }}-subtle text-{{ $tone }}"><i class="fa-solid {{ $icon }}"></i></span><div><div class="text-body-secondary small">{{ $label }}</div><div class="fs-3 fw-semibold">{{ $value }}</div><small class="text-body-secondary">{{ $hint }}</small></div></div></div>
            </section>
        @endforeach

        <section class="col-lg-7 dashboard-widget" data-widget="action_queue" data-default-order="8" draggable="false"><div class="card h-100 shadow-sm border-0"><button class="dashboard-widget-remove" type="button" aria-label="Hide action queue" data-widget-remove hidden><i class="fa-solid fa-xmark"></i></button><span class="dashboard-widget-handle" hidden><i class="fa-solid fa-grip"></i></span><div class="card-header bg-white"><h2 class="h5 mb-0">Action queue</h2></div><div class="list-group list-group-flush">@forelse($actionItems as $item)<a class="list-group-item list-group-item-action d-flex align-items-center gap-3" href="{{ route($item['route']) }}"><span class="metric-icon bg-primary-subtle text-primary"><i class="fa-solid {{ $item['icon'] }}"></i></span><span class="flex-grow-1">{{ $item['label'] }}</span><span class="badge text-bg-{{ $item['count'] ? 'warning' : 'success' }}">{{ $item['count'] }}</span><i class="fa-solid fa-chevron-right text-body-secondary small"></i></a>@empty<div class="list-group-item text-body-secondary py-4">No action queues are assigned to your role.</div>@endforelse</div></div></section>

        <section class="col-lg-5 dashboard-widget" data-widget="my_work" data-default-order="9" draggable="false"><div class="card h-100 shadow-sm border-0"><button class="dashboard-widget-remove" type="button" aria-label="Hide my work summary" data-widget-remove hidden><i class="fa-solid fa-xmark"></i></button><span class="dashboard-widget-handle" hidden><i class="fa-solid fa-grip"></i></span><div class="card-header bg-white"><h2 class="h5 mb-0">My work summary</h2></div><div class="card-body"><div class="d-flex justify-content-between border-bottom pb-2 mb-2"><span class="text-body-secondary">Open tasks</span><strong>{{ $metrics['openTasks'] }}</strong></div><div class="d-flex justify-content-between border-bottom pb-2 mb-2"><span class="text-body-secondary">Leave balance</span><strong>{{ number_format($metrics['leaveBalance'], 1) }} days</strong></div><div class="d-flex justify-content-between"><span class="text-body-secondary">Exports processing</span><strong>{{ $metrics['pendingExports'] }}</strong></div></div></div></section>

        @if($isManagerOrAdmin)<section class="col-12 dashboard-widget" data-widget="recent_attendance" data-default-order="10" draggable="false"><div class="card shadow-sm border-0"><button class="dashboard-widget-remove" type="button" aria-label="Hide recent attendance" data-widget-remove hidden><i class="fa-solid fa-xmark"></i></button><span class="dashboard-widget-handle" hidden><i class="fa-solid fa-grip"></i></span><div class="card-header bg-white d-flex justify-content-between align-items-center py-3"><h2 class="h5 mb-0">Recent attendance</h2>@can('attendance.report')<a href="{{ route('attendance.reports.index') }}" class="btn btn-sm btn-outline-primary">View report</a>@endcan</div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th class="ps-3">Employee</th><th>Check in</th><th class="pe-3">Status</th></tr></thead><tbody>@forelse($recentAttendances as $attendance)<tr><td class="ps-3 fw-medium">{{ $attendance->employee?->getFullName() }}</td><td>{{ $attendance->check_in_at?->format('H:i') ?? '—' }}</td><td class="pe-3"><span class="badge text-bg-success">{{ ucfirst(str_replace('_', ' ', $attendance->status)) }}</span></td></tr>@empty<tr><td colspan="3" class="py-5 text-center text-body-secondary">No attendance records today.</td></tr>@endforelse</tbody></table></div></div></section>@endif
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}" type="application/json" id="dashboard-preferences">@json($dashboardPreferences)</script>
    <script nonce="{{ request()->attributes->get('csp_nonce') }}">window.dashboardPreferenceUrl = @json(route('preferences.dashboard.update')); window.dashboardResetUrl = @json(route('preferences.dashboard.destroy'));</script>
</x-layouts::app>
