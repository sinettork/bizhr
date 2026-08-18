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
            @can('attendance.report')
                <a class="btn btn-action-link btn-sm" href="{{ route('attendance.reports.index') }}">
                    <i class="fa-solid fa-chart-column"></i><span>Reports</span>
                </a>
            @endcan
        </x-slot:actions>
    </x-workspace-command-bar>

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
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
