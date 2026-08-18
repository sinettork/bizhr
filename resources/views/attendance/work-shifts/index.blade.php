<x-layouts::app title="Work Shifts">
    <x-workspace-command-bar title="Work Shifts" icon="fa-clock" context="Attendance Setup">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search shift" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms">
                </div>
                <select class="form-select reference-status" name="status" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                    <option value="">All statuses</option>
                    <option value="1" @selected(request('status') === '1')>Active</option>
                    <option value="0" @selected(request('status') === '0')>Inactive</option>
                </select>
                <button class="btn btn-primary reference-search-button">Search</button>
            </form>
        </x-slot:filters>
        <x-slot:actions>
            <x-list-actions :add-modal="auth()->user()->can('shift.create') ? 'createWorkShift' : null" add-label="Add work shift" />
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
                        <th class="ps-3">Shift</th>
                        <th>Working Hours</th>
                        <th>Grace Period</th>
                        <th>Schedules</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $shift)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold text-dark">{{ $shift->name }}</div>
                                <small class="text-body-secondary">{{ $shift->code }}{{ $shift->is_night_shift ? ' · Overnight' : '' }}</small>
                            </td>
                            <td>
                                <div>{{ $shift->getTimeRangeFormatted() }}</div>
                                <small class="text-body-secondary">Break {{ $shift->break_minutes }} min · Net {{ number_format($shift->getDurationMinutes() / 60, 2) }} h</small>
                            </td>
                            <td>
                                <div>Late: {{ $shift->late_grace_minutes }} min</div>
                                <small class="text-body-secondary">Early leave: {{ $shift->early_leave_grace_minutes }} min</small>
                            </td>
                            <td>{{ $shift->schedules_count }}</td>
                            <td><span class="badge text-bg-{{ $shift->is_active ? 'success' : 'secondary' }}">{{ $shift->is_active ? 'Active' : 'Inactive' }}</span></td>
                            <td class="text-end pe-3 text-nowrap">
                                @can('shift.edit')
                                    <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#editWorkShift{{ $shift->id }}">
                                        <i class="fa-solid fa-pen"></i><span>Edit</span>
                                    </button>
                                @endcan
                                @can('shift.delete')
                                    @if($shift->schedules_count === 0)
                                        <form class="d-inline" method="POST" action="{{ route('work-shifts.destroy', $shift) }}" data-confirm="Delete this work shift?">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-action-link btn-sm text-danger" type="submit">
                                                <i class="fa-solid fa-trash"></i><span>Delete</span>
                                            </button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-5 text-center text-body-secondary">
                                <i class="fa-solid fa-clock fa-xl d-block mb-3 text-primary"></i>No work shifts found. Create a shift before publishing employee schedules.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$shifts" />
    </div>

    @can('shift.create')
        <div class="modal fade" id="createWorkShift" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="{{ route('work-shifts.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title fs-5"><i class="fa-solid fa-clock me-2 text-primary"></i>Add work shift</h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @include('attendance.work-shifts._form', ['shift' => null])
                    </div>
                    <x-form-save-actions :allow-save-new="true" save-label="Save & close" new-label="Save & new" />
                </form>
            </div>
        </div>
    @endcan

    @foreach($shifts as $shift)
        @can('shift.edit')
            <div class="modal fade" id="editWorkShift{{ $shift->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" method="POST" action="{{ route('work-shifts.update', $shift) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h2 class="modal-title fs-5"><i class="fa-solid fa-pen me-2 text-primary"></i>Edit work shift · {{ $shift->name }}</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            @include('attendance.work-shifts._form', ['shift' => $shift])
                        </div>
                        <x-form-save-actions save-label="Save changes" />
                    </form>
                </div>
            </div>
        @endcan
    @endforeach
</x-layouts::app>