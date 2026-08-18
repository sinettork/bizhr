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

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Employee</th>
                        <th>Branch / Department</th>
                        <th>Shift Assignment</th>
                        <th>Notes</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $schedule)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold text-dark">{{ $schedule->employee?->getFullName() }}</div>
                                <small class="text-body-secondary">{{ $schedule->employee?->employee_code }}</small>
                            </td>
                            <td>
                                <div>{{ $schedule->employee?->branch?->name }}</div>
                                <small class="text-body-secondary">{{ $schedule->employee?->department?->name ?: '—' }}</small>
                            </td>
                            <td>
                                @if($schedule->is_rest_day)
                                    <span class="badge text-bg-secondary">Rest day</span>
                                @else
                                    <div class="fw-medium text-dark">{{ $schedule->workShift?->name }}</div>
                                    <small class="text-body-secondary">{{ $schedule->workShift?->getTimeRangeFormatted() }}</small>
                                @endif
                            </td>
                            <td>{{ $schedule->notes ?: '—' }}</td>
                            <td class="text-end pe-3 text-nowrap">
                                @can('schedule.edit')
                                    <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#editSchedule{{ $schedule->id }}">
                                        <i class="fa-solid fa-pen"></i><span>Edit</span>
                                    </button>
                                @endcan
                                @can('schedule.delete')
                                    <form class="d-inline" method="POST" action="{{ route('schedules.destroy', $schedule) }}" onsubmit="return confirm('Delete this schedule?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-action-link btn-sm text-danger" type="submit">
                                            <i class="fa-solid fa-trash"></i><span>Delete</span>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-5 text-center text-body-secondary">
                                <i class="fa-solid fa-calendar-days fa-xl d-block mb-3 text-primary"></i>No schedules published for {{ $workDate->format('d M Y') }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$schedules" />
    </div>

    @can('schedule.create')
        <div class="modal fade" id="createSchedule" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="{{ route('schedules.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title fs-5"><i class="fa-solid fa-calendar-plus me-2 text-primary"></i>Add employee schedule</h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('attendance.schedules._form', ['schedule' => null])
                    </div>
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
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h2 class="modal-title fs-5"><i class="fa-solid fa-pen me-2 text-primary"></i>Edit employee schedule</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @include('attendance.schedules._form', ['schedule' => $schedule])
                        </div>
                        <x-form-save-actions save-label="Save changes" />
                    </form>
                </div>
            </div>
        @endcan
    @endforeach
</x-layouts::app>
