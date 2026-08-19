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
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('attendance.corrections.request') }}">
                        <i class="fa-solid fa-clock-rotate-left me-1"></i><span>Request correction</span>
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
            $stateIcon = $checkedOut ? 'fa-circle-check' : ($checkedIn ? 'fa-person-circle-check' : 'fa-location-dot');
        @endphp

        <section class="profile-card mb-4" aria-labelledby="today-attendance-heading">
            <div class="profile-card-header flex-wrap gap-2">
                <div>
                    <div class="small text-body-secondary mb-1">My attendance</div>
                    <h2 class="profile-card-title mb-0" id="today-attendance-heading">
                        <i class="fa-solid fa-calendar-day text-primary"></i><span>Today · {{ today()->format('d M Y') }}</span>
                    </h2>
                </div>
                <span class="d-inline-flex align-items-center gap-2 small fw-semibold text-{{ $stateTone }}">
                    <i class="fa-solid {{ $stateIcon }}"></i>{{ $stateLabel }}
                </span>
            </div>
            <div class="profile-card-body p-3 p-lg-4">
                <div class="row g-3 align-items-stretch">
                    <div class="col-6 col-lg-3">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="small text-body-secondary mb-1">Check in</div>
                            <div class="fs-4 fw-bold">{{ $today?->check_in_at?->format('H:i') ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="small text-body-secondary mb-1">Check out</div>
                            <div class="fs-4 fw-bold">{{ $today?->check_out_at?->format('H:i') ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="small text-body-secondary mb-1">Worked</div>
                            <div class="fs-4 fw-bold">{{ $today ? intdiv((int) $today->worked_minutes, 60).'h '.((int) $today->worked_minutes % 60).'m' : '—' }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="border rounded-3 p-3 h-100">
                            <div class="small text-body-secondary mb-1">Status</div>
                            <div class="fw-semibold">{{ $today ? str($today->status)->replace('_', ' ')->title() : 'Waiting for check-in' }}</div>
                        </div>
                    </div>
                </div>

                <div class="alert {{ $checkedOut ? 'alert-success' : 'alert-primary' }} mt-3 mb-0 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="d-flex gap-2">
                        <i class="fa-solid {{ $checkedOut ? 'fa-circle-check' : 'fa-qrcode' }} mt-1"></i>
                        <div>
                            @if($checkedOut)
                                <div class="fw-semibold">Attendance complete for today</div>
                                <div class="small">Your check-in and check-out have both been recorded.</div>
                            @elseif($checkedIn)
                                <div class="fw-semibold">Scan the current branch QR when you leave</div>
                                <div class="small">Use your phone camera, open the QR link, allow location access, and the next valid scan will record your check-out.</div>
                            @else
                                <div class="fw-semibold">Scan the current branch QR to check in</div>
                                <div class="small">Use your phone camera at your workplace, open the QR link, sign in if asked, and allow location access.</div>
                            @endif
                        </div>
                    </div>
                    @can('attendance.correction.request')
                        <a class="btn btn-sm {{ $checkedOut ? 'btn-outline-success' : 'btn-outline-primary' }} flex-shrink-0" href="{{ route('attendance.corrections.request') }}">
                            <i class="fa-solid fa-clock-rotate-left me-1"></i>Wrong time?
                        </a>
                    @endcan
                </div>
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
                                    <span class="badge text-bg-light border text-dark fw-bold px-2 py-1"><i class="fa-regular fa-clock text-success me-1"></i>{{ $record->check_in_at->format('H:i') }}</span>
                                @else
                                    <span class="text-body-secondary">—</span>
                                @endif
                            </td>
                            <td>
                                @if($record->check_out_at)
                                    <span class="badge text-bg-light border text-dark fw-bold px-2 py-1"><i class="fa-regular fa-clock text-primary me-1"></i>{{ $record->check_out_at->format('H:i') }}</span>
                                @else
                                    <span class="text-body-secondary">—</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-dark">{{ intdiv((int)$record->worked_minutes, 60) }}h {{ (int)$record->worked_minutes % 60 }}m</strong>
                            </td>
                            <td>
                                @if($record->late_minutes > 0)
                                    <span class="badge text-bg-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i>+{{ $record->late_minutes }} min</span>
                                @else
                                    <span class="badge text-bg-success">On time</span>
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
