<x-layouts::app title="Employee Schedules">
    <x-workspace-command-bar title="Employee Schedules" icon="fa-calendar-days" context="Attendance Planning">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-regular fa-calendar"></i></span>
                    <input class="form-control" name="date" type="date" value="{{ $workDate->toDateString() }}" aria-label="Work date" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                </div>
                <select class="form-select reference-status" name="branch_id" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                    <option value="">All branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(request('branch_id') == $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary reference-search-button">View</button>
            </form>
        </x-slot:filters>
        <x-slot:actions>
            <x-list-actions :add-modal="auth()->user()->can('schedule.create') ? 'createSchedule' : null" add-label="Add schedule" />
        </x-slot:actions>
    </x-workspace-command-bar>

    @if (session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
    @endif

    @php
        $restDays = $schedules->getCollection()->where('is_rest_day', true)->count();
        $assignedDays = $schedules->getCollection()->where('is_rest_day', false)->count();
        $shiftGroups = $schedules->getCollection()->where('is_rest_day', false)->groupBy(fn($schedule) => $schedule->workShift?->name ?: 'Unassigned shift');
    @endphp

    <div data-list-container>
        <div class="row g-3 mb-3">
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:42px;height:42px"><i class="fa-solid fa-calendar-day"></i></div>
                        <div><div class="small text-body-secondary">Roster date</div><div class="fw-bold">{{ $workDate->format('D, d M Y') }}</div></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width:42px;height:42px"><i class="fa-solid fa-user-clock"></i></div>
                        <div><div class="small text-body-secondary">On roster</div><div class="fs-5 fw-bold">{{ $assignedDays }}</div></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-secondary-subtle text-secondary d-flex align-items-center justify-content-center flex-shrink-0" style="width:42px;height:42px"><i class="fa-solid fa-bed"></i></div>
                        <div><div class="small text-body-secondary">Rest day</div><div class="fs-5 fw-bold">{{ $restDays }}</div></div>
                    </div>
                </div>
            </div>
        </div>

        @if($schedules->count())
            <div class="row g-3">
                <div class="col-12 col-xl-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-transparent border-bottom d-flex align-items-center justify-content-between gap-2 py-3">
                            <div>
                                <h2 class="h6 fw-bold mb-1">Daily roster</h2>
                                <div class="small text-body-secondary">Employees grouped by assigned shift for the selected day.</div>
                            </div>
                            <span class="badge text-bg-light">{{ $schedules->total() }} schedules</span>
                        </div>
                        <div class="card-body p-0">
                            @forelse($shiftGroups as $shiftName => $group)
                                <section class="border-bottom last-child-border-0">
                                    <div class="px-3 px-lg-4 py-3 bg-body-tertiary d-flex flex-wrap align-items-center justify-content-between gap-2">
                                        <div>
                                            <div class="fw-semibold">{{ $shiftName }}</div>
                                            <small class="text-body-secondary">{{ $group->first()?->workShift?->getTimeRangeFormatted() ?: 'Shift time not set' }}</small>
                                        </div>
                                        <span class="badge rounded-pill text-bg-primary">{{ $group->count() }} employees</span>
                                    </div>
                                    <div class="list-group list-group-flush">
                                        @foreach($group as $schedule)
                                            <div class="list-group-item px-3 px-lg-4 py-3">
                                                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                                    <div class="d-flex align-items-center gap-3 min-w-0">
                                                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width:38px;height:38px"><i class="fa-solid fa-user"></i></div>
                                                        <div class="min-w-0">
                                                            <div class="fw-semibold text-truncate">{{ $schedule->employee?->getFullName() }}</div>
                                                            <div class="small text-body-secondary text-truncate">{{ $schedule->employee?->employee_code }} · {{ $schedule->employee?->department?->name ?: 'No department' }} · {{ $schedule->employee?->branch?->name ?: 'No branch' }}</div>
                                                            @if($schedule->notes)<div class="small mt-1"><i class="fa-regular fa-note-sticky me-1 text-body-secondary"></i>{{ $schedule->notes }}</div>@endif
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                                        @can('schedule.edit')
                                                            <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#editSchedule{{ $schedule->id }}"><i class="fa-solid fa-pen"></i><span>Edit</span></button>
                                                        @endcan
                                                        @can('schedule.delete')
                                                            <form method="POST" action="{{ route('schedules.destroy', $schedule) }}" onsubmit="return confirm('Delete this schedule?')">@csrf @method('DELETE')<button class="btn btn-action-link btn-sm text-danger" type="submit"><i class="fa-solid fa-trash"></i><span>Delete</span></button></form>
                                                        @endcan
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </section>
                            @empty
                                <div class="p-4 text-body-secondary">No active shift assignments on this page.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-12 col-xl-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-transparent border-bottom py-3">
                            <h2 class="h6 fw-bold mb-1">Rest day</h2>
                            <div class="small text-body-secondary">Employees not scheduled to work on this date.</div>
                        </div>
                        <div class="list-group list-group-flush">
                            @forelse($schedules->getCollection()->where('is_rest_day', true) as $schedule)
                                <div class="list-group-item py-3">
                                    <div class="d-flex align-items-start justify-content-between gap-2">
                                        <div>
                                            <div class="fw-semibold">{{ $schedule->employee?->getFullName() }}</div>
                                            <small class="text-body-secondary">{{ $schedule->employee?->department?->name ?: 'No department' }}</small>
                                        </div>
                                        @can('schedule.edit')
                                            <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#editSchedule{{ $schedule->id }}"><i class="fa-solid fa-pen"></i><span>Edit</span></button>
                                        @endcan
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center text-body-secondary small">No rest-day assignments on this page.</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-3"><x-pagination-footer :paginator="$schedules" /></div>
        @else
            <div class="card border-0 shadow-sm">
                <div class="card-body py-5 text-center">
                    <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width:52px;height:52px"><i class="fa-solid fa-calendar-days fa-lg"></i></div>
                    <h2 class="h6 fw-bold">No roster published</h2>
                    <p class="text-body-secondary small mb-0">There are no employee schedules for {{ $workDate->format('d M Y') }} with the current branch filter.</p>
                </div>
            </div>
        @endif
    </div>

    @can('schedule.create')
        <div class="modal fade" id="createSchedule" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="{{ route('schedules.store') }}">
                    @csrf
                    <div class="modal-header"><h2 class="modal-title fs-5"><i class="fa-solid fa-calendar-plus me-2 text-primary"></i>Add employee schedule</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">@include('attendance.schedules._form', ['schedule' => null])</div>
                    <x-form-save-actions :allow-save-new="true" save-label="Save & close" new-label="Save & new" />
                </form>
            </div>
        </div>
    @endcan

    @foreach($schedules as $schedule)
        @can('schedule.edit')
            <div class="modal fade" id="editSchedule{{ $schedule->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" method="POST" action="{{ route('schedules.update', $schedule) }}">
                        @csrf @method('PUT')
                        <div class="modal-header"><h2 class="modal-title fs-5"><i class="fa-solid fa-pen me-2 text-primary"></i>Edit employee schedule</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
                        <div class="modal-body">@include('attendance.schedules._form', ['schedule' => $schedule])</div>
                        <x-form-save-actions save-label="Save changes" />
                    </form>
                </div>
            </div>
        @endcan
    @endforeach
</x-layouts::app>
