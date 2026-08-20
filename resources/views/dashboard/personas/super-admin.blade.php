<x-workspace-command-bar title="System Dashboard" icon="fa-shield-halved" context="System Oversight">
    <x-slot:actions>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-action-link btn-sm" href="{{ route('users.index') }}"><i class="fa-solid fa-users-gear"></i><span>Users</span></a>
            <a class="btn btn-action-link btn-sm" href="{{ route('roles.index') }}"><i class="fa-solid fa-shield-halved"></i><span>Roles</span></a>
            <a class="btn btn-primary btn-sm" href="{{ route('audit-logs.index') }}"><i class="fa-solid fa-file-shield me-1"></i>Audit logs</a>
        </div>
    </x-slot:actions>
</x-workspace-command-bar>

<div class="workspace-summary">
    @foreach([
        ['Active employees', $metrics['activeEmployees']],
        ['Branches', $metrics['branches']],
        ['Linked users', $metrics['linkedUsers']],
        ['Inactive users', $metrics['inactiveUsers']],
        ['Audit events today', $metrics['auditToday']],
        ['Exports in progress', $metrics['pendingExports']],
        ['Failed exports', $metrics['failedExports']],
    ] as [$label, $value])
        <div class="workspace-summary-item">
            <div class="label">{{ $label }}</div>
            <div class="value">{{ number_format($value) }}</div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-12 col-xl-5">
        <section class="reference-list h-100">
            <div class="reference-list-toolbar">
                <div>
                    <div class="small text-uppercase text-body-secondary fw-semibold">System attention</div>
                    <div class="fw-semibold text-dark">Administrative checks</div>
                </div>
            </div>
            @foreach($systemItems as $item)
                <a class="d-flex align-items-center gap-3 px-3 py-3 border-bottom text-decoration-none text-body" href="{{ route($item['route']) }}">
                    <i class="fa-solid {{ $item['icon'] }} text-body-secondary"></i>
                    <span class="flex-grow-1 fw-semibold">{{ $item['label'] }}</span>
                    <span class="fw-semibold {{ $item['count'] > 0 ? 'text-warning' : 'text-success' }}">{{ number_format($item['count']) }}</span>
                    <i class="fa-solid fa-chevron-right text-body-secondary small"></i>
                </a>
            @endforeach
        </section>
    </div>

    <div class="col-12 col-xl-7">
        <section class="reference-list h-100">
            <div class="reference-list-toolbar">
                <div>
                    <div class="small text-uppercase text-body-secondary fw-semibold">Audit trail</div>
                    <div class="fw-semibold text-dark">Recent system activity</div>
                </div>
                <a class="btn btn-action-link btn-sm" href="{{ route('audit-logs.index') }}">View all</a>
            </div>
            @forelse($recentAuditLogs as $log)
                <div class="d-flex align-items-start gap-3 px-3 py-3 border-bottom">
                    <i class="fa-solid fa-shield text-body-secondary mt-1"></i>
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-dark">{{ str($log->action)->replace('_', ' ')->title() }}</div>
                        <div class="small text-body-secondary text-truncate">
                            {{ $log->user?->name ?? 'System' }} · {{ str($log->module)->replace('_', ' ')->title() }}
                        </div>
                    </div>
                    <small class="text-body-secondary text-nowrap">{{ $log->created_at?->format('H:i') ?? '—' }}</small>
                </div>
            @empty
                <x-empty-state class="py-5 px-3" icon="fa-file-shield" title="No audit activity yet" message="System audit events will appear here." />
            @endforelse
        </section>
    </div>
</div>
