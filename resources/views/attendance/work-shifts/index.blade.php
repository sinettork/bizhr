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
        @if($shifts->count())
            <div class="px-3 py-2 border-bottom bg-body-tertiary d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <div class="fw-semibold text-dark">Shift library</div>
                    <div class="small text-body-secondary">Working windows, grace rules and schedule usage at a glance.</div>
                </div>
                <span class="badge status-counter">{{ number_format($shifts->total()) }} shift(s)</span>
            </div>

            <div class="row g-2 p-3">
                @foreach($shifts as $shift)
                    <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                        <article class="card h-100 shadow-none">
                            <div class="card-body p-3 d-flex flex-column">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div class="d-flex align-items-start gap-2 min-w-0">
                                        <span class="page-icon flex-shrink-0" style="width:38px;height:38px;font-size:.82rem;"><i class="fa-solid {{ $shift->is_night_shift ? 'fa-moon' : 'fa-clock' }}"></i></span>
                                        <div class="min-w-0">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <h2 class="h6 mb-0 text-dark text-truncate">{{ $shift->name }}</h2>
                                                <span class="badge text-bg-{{ $shift->is_active ? 'success' : 'secondary' }}">{{ $shift->is_active ? 'Active' : 'Inactive' }}</span>
                                            </div>
                                            <div class="small text-body-secondary mt-1 text-truncate">{{ $shift->code }}{{ $shift->is_night_shift ? ' · Overnight' : '' }}</div>
                                            <div class="small text-body-secondary text-truncate"><i class="fa-regular fa-clock me-1"></i>{{ $shift->getTimeRangeFormatted() }}</div>
                                        </div>
                                    </div>

                                    <div class="dropdown flex-shrink-0">
                                        <button class="btn btn-action-link btn-sm px-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Shift actions"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            @can('shift.edit')
                                                <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#editWorkShift{{ $shift->id }}"><i class="fa-solid fa-pen me-2"></i>Edit shift</button></li>
                                            @endcan
                                            @can('shift.delete')
                                                @if($shift->schedules_count === 0)
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><form method="POST" action="{{ route('work-shifts.destroy',$shift) }}" data-confirm="Delete this work shift?">@csrf @method('DELETE')<button class="dropdown-item text-danger" type="submit"><i class="fa-solid fa-trash me-2"></i>Delete shift</button></form></li>
                                                @endif
                                            @endcan
                                        </ul>
                                    </div>
                                </div>

                                <hr class="my-3">

                                <div class="row g-0 text-center mt-auto">
                                    <div class="col-4 px-1">
                                        <div class="fw-semibold text-dark">{{ number_format($shift->getDurationMinutes() / 60, 1) }}h</div>
                                        <div class="small text-body-secondary">Net hours</div>
                                    </div>
                                    <div class="col-4 px-1 border-start border-end">
                                        <div class="fw-semibold text-dark">{{ $shift->late_grace_minutes }}m</div>
                                        <div class="small text-body-secondary">Late grace</div>
                                    </div>
                                    <div class="col-4 px-1">
                                        <div class="fw-semibold text-dark">{{ number_format($shift->schedules_count) }}</div>
                                        <div class="small text-body-secondary">Schedules</div>
                                    </div>
                                </div>

                                <div class="small text-body-secondary mt-3 pt-2 border-top d-flex justify-content-between gap-2">
                                    <span>Break {{ $shift->break_minutes }} min</span>
                                    <span>Early leave {{ $shift->early_leave_grace_minutes }} min</span>
                                </div>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
            <x-pagination-footer :paginator="$shifts" />
        @else
            <div class="empty-state py-5 px-3">
                <i class="fa-solid fa-clock fa-2xl d-block mb-3 text-primary"></i>
                <h2 class="h6 mb-1">No work shifts found</h2>
                <p class="text-body-secondary mb-0">Create a shift before publishing employee schedules.</p>
            </div>
        @endif
    </div>

    @can('shift.create')
        <div class="modal fade" id="createWorkShift" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="{{ route('work-shifts.store') }}">
                    @csrf
                    <div class="modal-header"><h2 class="modal-title fs-5"><i class="fa-solid fa-clock me-2 text-primary"></i>Add work shift</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
                    <div class="modal-body">@include('attendance.work-shifts._form', ['shift' => null])</div>
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
                        @csrf @method('PUT')
                        <div class="modal-header"><h2 class="modal-title fs-5"><i class="fa-solid fa-pen me-2 text-primary"></i>Edit work shift · {{ $shift->name }}</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div>
                        <div class="modal-body">@include('attendance.work-shifts._form', ['shift' => $shift])</div>
                        <x-form-save-actions save-label="Save changes" />
                    </form>
                </div>
            </div>
        @endcan
    @endforeach
</x-layouts::app>
