<x-layouts::app title="Attendance & Timecards">
    <x-workspace-command-bar title="Daily Attendance & Time Tracking" icon="fa-user-clock" context="Attendance">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
                    <input class="form-control" type="date" name="date" value="{{ $date->toDateString() }}" aria-label="Attendance date" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                </div>
                <button class="btn btn-primary reference-search-button">Filter date</button>
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
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif

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
                    <div class="fw-semibold text-dark" id="today-attendance-heading">Today</div>
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
                        <i class="fa-solid fa-clock-rotate-left"></i><span>Wrong time?</span>
                    </a>
                @endcan
            </div>
        </section>
    @endif

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Employee</th>
                        <th>Branch & Department</th>
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
                                <small class="text-body-secondary">{{ $record->employee?->employee_code }}</small>
                            </td>
                            <td>
                                <div>{{ $record->employee?->branch?->name ?: 'All branches' }}</div>
                                <small class="text-body-secondary">{{ $record->employee?->department?->name }}</small>
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
                                    <span class="text-body-secondary">—</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-dark">{{ intdiv((int)$record->worked_minutes, 60) }}h {{ (int)$record->worked_minutes % 60 }}m</strong>
                            </td>
                            <td>
                                @if($record->late_minutes > 0)
                                    <span class="status-text text-bg-warning">+{{ $record->late_minutes }} min</span>
                                @else
                                    <span class="status-text text-bg-success">On time</span>
                                @endif
                            </td>
                            <td class="pe-3">
                                <span class="status-text text-bg-{{ $statusTone }}">{{ str($record->status)->replace('_',' ')->title() }}</span>
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
