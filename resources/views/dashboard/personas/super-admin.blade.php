<x-workspace-command-bar title="System Administration" icon="fa-server" context="IT / Admin">
    <x-slot:actions>
        <div class="d-flex flex-wrap gap-2">
                <button class="btn btn-action-link btn-sm" type="button"><i class="fa-solid fa-broom"></i><span>Clear Cache</span></button>
            <a class="btn btn-primary btn-sm" href="{{ route('roles.index') }}"><i class="fa-solid fa-shield-halved me-1"></i>Role Management</a>
        </div>
    </x-slot:actions>
</x-workspace-command-bar>

<div class="workspace-summary">
    @foreach([
        ['Active Employees', $metrics['activeEmployees']],
        ['Branches', $metrics['branches']],
        ['Linked Users', $metrics['linkedUsers']],
        ['Failed Exports', $metrics['failedExports']],
    ] as [$label, $value])
        <div class="workspace-summary-item">
            <div class="label">{{ $label }}</div>
            <div class="value {{ str_contains($label, 'Failed') ? ($value > 0 ? 'text-danger' : '') : '' }}">{{ $value }}</div>
        </div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-12 col-xl-6">
        <section class="reference-list h-100 border border-danger border-2 rounded">
            <div class="reference-list-toolbar bg-danger text-white rounded-top">
                <div>
                    <div class="small text-uppercase text-white-50 fw-semibold">System Health</div>
                    <div class="fw-semibold text-white">System Attention</div>
                </div>
            </div>
            @forelse($systemItems as $item)
                <div class="d-flex align-items-center gap-3 px-3 py-3 border-bottom">
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-dark text-truncate">{{ $item['label'] }}</div>
                        <div class="small text-body-secondary text-truncate">{{ $item['count'] }}</div>
                    </div>
                </div>
            @empty
                <x-empty-state class="py-4 px-2" icon="fa-network-wired" tone="success" title="Systems Nominal" message="No system attention items are pending." />
            @endforelse
        </section>
    </div>

    <div class="col-12 col-xl-6">
        <section class="reference-list h-100">
            <div class="reference-list-toolbar">
                <div>
                    <div class="small text-uppercase text-body-secondary fw-semibold">Security</div>
                    <div class="fw-semibold text-dark">Recent Admin Audit Log</div>
                </div>
                <a class="btn btn-action-link btn-sm" href="{{ route('audit-logs.index') }}">Full Log</a>
            </div>
            @forelse($recentAuditLogs as $log)
                <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-dark">
                            <span class="text-primary">{{ $log->user?->name ?? 'System' }}</span>
                            {{ $log->action }}
                            <span class="text-dark">{{ class_basename($log->record_type) }} #{{ $log->record_id }}</span>
                        </div>
                        <small class="text-body-secondary">IP: {{ $log->ip_address }} · {{ $log->created_at->format('H:i:s') }}</small>
                    </div>
                </div>
            @empty
                <x-empty-state class="py-4 px-2" icon="fa-shield-halved" title="No Recent Activity" message="No audit events have been recorded yet." />
            @endforelse
        </section>
    </div>
</div>