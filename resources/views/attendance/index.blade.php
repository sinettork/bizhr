<x-layouts::app title="Attendance & Timecards">
    <x-workspace-command-bar title="Daily Attendance &amp; Shift Monitor" icon="fa-user-clock" context="Time &amp; Attendance Operations">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
                    <input class="form-control" type="date" name="date" value="{{ $date->toDateString() }}" aria-label="Attendance date" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                </div>
                <button class="btn btn-primary reference-search-button">Filter date</button>
                @if(! $date->isToday())
                    <a href="{{ route('attendance.checkinout') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fa-solid fa-clock-rotate-left me-1"></i>Today
                    </a>
                @endif
            </form>
        </x-slot:filters>
        <x-slot:actions>
            <div class="d-flex flex-wrap gap-2">
                @can('attendance.correction.request')
                    <a class="btn btn-action-link btn-sm" href="{{ route('attendance.corrections.request') }}">
                        <i class="fa-solid fa-clock-rotate-left"></i><span>Request correction</span>
                    </a>
                @endcan
                @can('attendance.report')
                    <a class="btn btn-action-link btn-sm" href="{{ route('attendance.reports.index') }}">
                        <i class="fa-solid fa-chart-column"></i><span>Reports</span>
                    </a>
                @endcan
            </div>
        </x-slot:actions>
    </x-workspace-command-bar>

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif

    {{-- Line Employee View: Personal Attendance Card --}}
    @if(! $canManage)
        @php
            $checkedIn = $today?->check_in_at !== null;
            $checkedOut = $today?->check_out_at !== null;
            $stateLabel = $checkedOut ? 'Workday complete' : ($checkedIn ? 'Checked in' : 'Not checked in');
            $stateTone = $checkedOut ? 'success' : ($checkedIn ? 'primary' : 'warning');
        @endphp

        <section class="reference-list mb-3" aria-labelledby="today-attendance-heading">
            <div class="reference-list-toolbar">
                <div>
                    <div class="small text-body-secondary">My attendance · {{ today()->format('d M Y') }}</div>
                    <div class="fw-semibold text-dark" id="today-attendance-heading">Today's Shift Status</div>
                </div>
                <span class="status-text text-bg-{{ $stateTone }}">{{ $stateLabel }}</span>
            </div>

            <div class="workspace-summary mb-0 border-0 border-bottom rounded-0">
                <div class="workspace-summary-item">
                    <div class="label">Check in</div>
                    <div class="value">{{ $today?->check_in_at?->format('H:i') ?? '—' }}</div>
                </div>
                <div class="workspace-summary-item">
                    <div class="label">Check out</div>
                    <div class="value">{{ $today?->check_out_at?->format('H:i') ?? '—' }}</div>
                </div>
                <div class="workspace-summary-item">
                    <div class="label">Worked</div>
                    <div class="value">{{ $today ? intdiv((int) $today->worked_minutes, 60).'h '.((int) $today->worked_minutes % 60).'m' : '—' }}</div>
                </div>
                <div class="workspace-summary-item">
                    <div class="label">Attendance status</div>
                    <div class="value fs-6">{{ $today ? str($today->status)->replace('_', ' ')->title() : 'Waiting for check-in' }}</div>
                </div>
            </div>

            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 px-3 py-2">
                <div class="small text-body-secondary">
                    <i class="fa-solid {{ $checkedOut ? 'fa-circle-check text-success' : 'fa-qrcode text-primary' }} me-1"></i>
                    @if($checkedOut)
                        Your check-in and check-out are complete for today.
                    @elseif($checkedIn)
                        Scan the current branch QR at the workplace when you leave to record check-out.
                    @else
                        Scan the current branch QR at the workplace and allow location access to record check-in.
                    @endif
                </div>
                @can('attendance.correction.request')
                    <a class="btn btn-action-link btn-sm flex-shrink-0" href="{{ route('attendance.corrections.request') }}">
                        <i class="fa-solid fa-clock-rotate-left"></i><span>Wrong time? Request correction</span>
                    </a>
                @endcan
            </div>
        </section>
    @else
        {{-- Operational Manager Monitor Strip (Archetype D) --}}
        <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3">
                <div class="profile-card h-100 mb-0">
                    <div class="profile-card-body p-3">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="small text-body-secondary fw-semibold text-uppercase">Total Logged</div>
                                <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($summary['records']) }}</div>
                            </div>
                            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-body-tertiary text-primary" style="width:36px;height:36px">
                                <i class="fa-solid fa-users"></i>
                            </span>
                        </div>
                        <div class="small text-body-secondary mt-2">Active shifts on {{ $date->format('d M Y') }}</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="profile-card h-100 mb-0">
                    <div class="profile-card-body p-3">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="small text-body-secondary fw-semibold text-uppercase">Present on Site</div>
                                <div class="fs-4 fw-bold text-success mt-1">{{ number_format($summary['present']) }}</div>
                            </div>
                            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-success-subtle text-success" style="width:36px;height:36px">
                                <i class="fa-solid fa-user-check"></i>
                            </span>
                        </div>
                        <div class="small text-body-secondary mt-2">Verified attendance punches</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="profile-card h-100 mb-0">
                    <div class="profile-card-body p-3">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="small text-body-secondary fw-semibold text-uppercase">Late Punch-ins</div>
                                <div class="fs-4 fw-bold {{ $summary['late'] > 0 ? 'text-warning' : 'text-dark' }} mt-1">{{ number_format($summary['late']) }}</div>
                            </div>
                            <span class="d-inline-flex align-items-center justify-content-center rounded-3 {{ $summary['late'] > 0 ? 'bg-warning-subtle text-warning' : 'bg-body-tertiary text-secondary' }}" style="width:36px;height:36px">
                                <i class="fa-solid fa-hourglass-half"></i>
                            </span>
                        </div>
                        <div class="small text-body-secondary mt-2">Punched after shift tolerance</div>
                    </div>
                </div>
            </div>

            <div class="col-6 col-xl-3">
                <div class="profile-card h-100 mb-0">
                    <div class="profile-card-body p-3">
                        <div class="d-flex align-items-start justify-content-between">
                            <div>
                                <div class="small text-body-secondary fw-semibold text-uppercase">Currently on Duty</div>
                                <div class="fs-4 fw-bold text-info mt-1">{{ number_format($summary['open']) }}</div>
                            </div>
                            <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-info-subtle text-info" style="width:36px;height:36px">
                                <i class="fa-solid fa-business-time"></i>
                            </span>
                        </div>
                        <div class="small text-body-secondary mt-2">Punched in, awaiting check-out</div>
                    </div>
                </div>
            </div>
        </div>

        @if($summary['late'] > 0)
            <div class="alert alert-warning d-flex align-items-center justify-content-between gap-2 py-2 px-3 mb-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-triangle-exclamation text-warning"></i>
                    <span class="small fw-medium"><strong>{{ $summary['late'] }} employee(s)</strong> arrived late on this date. Review attendance variance below before payroll cutoff.</span>
                </div>
                @can('attendance.approve')
                    <a href="{{ route('attendance.corrections.review') }}" class="btn btn-outline-dark btn-sm py-1 px-2 text-nowrap" style="font-size: .78rem;">
                        <i class="fa-solid fa-clipboard-check me-1"></i>Review corrections
                    </a>
                @endcan
            </div>
        @endif
    @endif

    {{-- Attendance Master Ledger Table --}}
    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Employee</th>
                        <th>Branch &amp; Department</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Worked Time</th>
                        <th>Late Variance</th>
                        <th class="pe-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        @php
                            $statusTone = in_array($record->status, ['present','remote_work','business_trip']) ? 'success' : ($record->status === 'late' ? 'warning' : 'secondary');
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold text-dark">{{ $record->employee?->getFullName() }}</div>
                                <small class="text-body-secondary font-monospace">{{ $record->employee?->employee_code }}</small>
                            </td>
                            <td>
                                <div class="text-dark">{{ $record->employee?->branch?->name ?: 'All branches' }}</div>
                                <small class="text-body-secondary">{{ $record->employee?->department?->name ?? '—' }}</small>
                            </td>
                            <td>
                                @if($record->check_in_at)
                                    <span class="small fw-semibold text-dark"><i class="fa-regular fa-clock text-success me-1"></i>{{ $record->check_in_at->format('H:i') }}</span>
                                @else
                                    <span class="text-body-secondary">—</span>
                                @endif
                            </td>
                            <td>
                                @if($record->check_out_at)
                                    <span class="small fw-semibold text-dark"><i class="fa-regular fa-clock text-primary me-1"></i>{{ $record->check_out_at->format('H:i') }}</span>
                                @else
                                    <span class="badge text-bg-info-subtle text-info border border-info-subtle">On duty</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-dark">{{ intdiv((int)$record->worked_minutes, 60) }}h {{ (int)$record->worked_minutes % 60 }}m</strong>
                            </td>
                            <td>
                                @if($record->late_minutes > 0)
                                    <span class="badge text-bg-warning-subtle text-warning border border-warning-subtle font-monospace fw-semibold">+{{ $record->late_minutes }} min</span>
                                @else
                                    <span class="badge text-bg-success-subtle text-success border border-success-subtle">On time</span>
                                @endif
                            </td>
                            <td class="pe-3">
                                <span class="badge text-bg-{{ $statusTone }}">{{ str($record->status)->replace('_',' ')->title() }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-0">
                                <x-empty-state class="py-5 px-3" icon="fa-calendar-xmark" title="No attendance records" :message="'No attendance records logged for '.$date->format('d M Y').'.'" />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$records" />
    </div>
</x-layouts::app>
