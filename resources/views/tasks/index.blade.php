<x-layouts::app title="Tasks">
    <x-workspace-command-bar title="Task Management" icon="fa-list-check" context="Work Planning">
        <x-slot:filters>
            <form method="GET" class="reference-filter-form">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search task or employee">
                </div>
                <select name="status" class="form-select reference-status">
                    <option value="">All statuses</option>
                    @foreach(['not_started','in_progress','waiting_verification','verified','cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->replace('_',' ')->title() }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary reference-search-button">Filter</button>
            </form>
        </x-slot:filters>
        <x-slot:actions><x-list-actions :add-modal="auth()->user()->can('task.assign') ? 'createTask' : null" add-label="Assign new task" /></x-slot:actions>
    </x-workspace-command-bar>

    @if(session('status'))<div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>@endif
    @if($errors->any())<div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>@endif

    @php
        $board = [
            'not_started' => ['To do', 'fa-circle', 'secondary'],
            'in_progress' => ['In progress', 'fa-spinner', 'primary'],
            'waiting_verification' => ['Needs review', 'fa-circle-check', 'warning'],
            'done' => ['Done', 'fa-check-double', 'success'],
        ];
        $grouped = collect($tasks->items())->groupBy(function ($task) {
            if(in_array($task->effective_status, ['verified','cancelled'], true)) return 'done';
            if($task->effective_status === 'overdue') return 'in_progress';
            return $task->status;
        });
    @endphp

    <div class="row g-3" data-list-container>
        @foreach($board as $key => [$label, $icon, $tone])
            @php($rows = $grouped->get($key, collect()))
            <div class="col-12 col-xl-3">
                <section class="profile-card h-100 mb-0">
                    <div class="profile-card-header">
                        <h2 class="profile-card-title"><i class="fa-solid {{ $icon }} text-{{ $tone }}"></i><span>{{ $label }}</span></h2>
                        <span class="small fw-semibold text-body-secondary">{{ $rows->count() }}</span>
                    </div>
                    <div class="profile-card-body bg-body-tertiary p-2 d-grid gap-2 align-content-start" style="min-height: 420px;">
                        @forelse($rows as $task)
                            <article class="card border-0 shadow-sm">
                                <div class="card-body p-3">
                                    <div class="d-flex justify-content-between gap-2 mb-2">
                                        <div class="fw-semibold text-dark">{{ $task->title }}</div>
                                        @if($task->priority !== 'low')<span class="small fw-semibold {{ in_array($task->priority,['urgent','high']) ? 'text-danger' : 'text-body-secondary' }}">{{ ucfirst($task->priority) }}</span>@endif
                                    </div>
                                    <div class="small text-body-secondary mb-3"><i class="fa-regular fa-user me-1"></i>{{ $task->employee?->getFullName() }}@if($task->employee?->department)<span class="mx-1">·</span>{{ $task->employee->department->name }}@endif</div>
                                    @if($task->description)<p class="small text-body-secondary mb-3">{{ \Illuminate\Support\Str::limit($task->description, 90) }}</p>@endif
                                    <div class="d-flex align-items-center gap-2 mb-2"><div class="progress flex-grow-1" style="height:5px"><div class="progress-bar" style="width:{{ max(0,min(100,$task->progress)) }}%"></div></div><span class="small fw-semibold">{{ $task->progress }}%</span></div>
                                    <div class="d-flex align-items-center justify-content-between gap-2 small">
                                        <span class="{{ $task->effective_status === 'overdue' ? 'text-danger fw-semibold' : 'text-body-secondary' }}"><i class="fa-regular fa-calendar me-1"></i>{{ $task->due_date->format('d M Y') }}</span>
                                        <div class="dropdown">
                                            <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="dropdown" aria-label="Task actions"><i class="fa-solid fa-ellipsis"></i></button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                @if($task->status === 'waiting_verification')
                                                    <li><form method="POST" action="{{ route('tasks.verify', [$task, 'approve']) }}">@csrf<button class="dropdown-item text-success" type="submit"><i class="fa-solid fa-check me-2"></i>Verify task</button></form></li>
                                                    <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#returnTask{{ $task->id }}"><i class="fa-solid fa-reply me-2"></i>Return for revision</button></li>
                                                @endif
                                                @can('task.assign')
                                                    @if(!in_array($task->status,['verified','cancelled'],true))
                                                        <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#editTask{{ $task->id }}"><i class="fa-solid fa-pen me-2"></i>Edit</button></li>
                                                        <li><button class="dropdown-item text-danger" type="button" data-bs-toggle="modal" data-bs-target="#cancelTask{{ $task->id }}"><i class="fa-solid fa-ban me-2"></i>Cancel task</button></li>
                                                    @endif
                                                @endcan
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="text-center text-body-secondary py-5 small"><i class="fa-solid {{ $icon }} d-block mb-2"></i>No tasks here.</div>
                        @endforelse
                    </div>
                </section>
            </div>
        @endforeach
        <div class="col-12"><x-pagination-footer :paginator="$tasks" /></div>
    </div>

    @can('task.assign')
        <div class="modal fade" id="createTask" tabindex="-1">
            <div class="modal-dialog modal-lg"><form class="modal-content" method="POST" action="{{ route('tasks.store') }}">@csrf
                <div class="modal-header"><h2 class="modal-title fs-5"><i class="fa-solid fa-list-check me-2 text-primary"></i>Assign task</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                <div class="modal-body row g-3">
                    <div class="col-md-6"><label class="form-label">Title <span class="text-danger">*</span></label><input class="form-control" name="title" required placeholder="Task objective or deliverable"></div>
                    <div class="col-md-3"><label class="form-label">Assignee <span class="text-danger">*</span></label><select class="form-select" name="assigned_to" required>@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->getFullName() }}</option>@endforeach</select></div>
                    <div class="col-md-3"><label class="form-label">Priority</label><select class="form-select" name="priority"><option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="urgent">Urgent</option></select></div>
                    <div class="col-md-3"><label class="form-label">Start date</label><input class="form-control" type="date" name="start_date" value="{{ today()->toDateString() }}" required></div>
                    <div class="col-md-3"><label class="form-label">Due date</label><input class="form-control" type="date" name="due_date" value="{{ today()->addWeek()->toDateString() }}" required></div>
                    <div class="col-md-6"><label class="form-label">Description / Instructions</label><textarea class="form-control" name="description" rows="2" placeholder="Task scope and completion criteria..."></textarea></div>
                </div><x-form-save-actions :allow-save-new="true" save-label="Assign task" />
            </form></div>
        </div>

        @foreach($tasks as $task)
            @if(!in_array($task->status,['verified','cancelled'],true))
                <div class="modal fade" id="editTask{{ $task->id }}" tabindex="-1"><div class="modal-dialog modal-lg"><form class="modal-content" method="POST" action="{{ route('tasks.update',$task) }}">@csrf @method('PUT')
                    <div class="modal-header"><h2 class="modal-title fs-5"><i class="fa-solid fa-pen me-2 text-primary"></i>Edit task</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body row g-3">
                        <div class="col-md-6"><label class="form-label">Title</label><input class="form-control" name="title" value="{{ $task->title }}" required></div>
                        <div class="col-md-3"><label class="form-label">Assignee</label><select class="form-select" name="assigned_to">@foreach($employees as $employee)<option value="{{ $employee->id }}" @selected($task->assigned_to === $employee->id)>{{ $employee->getFullName() }}</option>@endforeach</select></div>
                        <div class="col-md-3"><label class="form-label">Priority</label><select class="form-select" name="priority">@foreach(['low','medium','high','urgent'] as $priority)<option value="{{ $priority }}" @selected($task->priority === $priority)>{{ ucfirst($priority) }}</option>@endforeach</select></div>
                        <div class="col-md-3"><label class="form-label">Start date</label><input class="form-control" type="date" name="start_date" value="{{ $task->start_date->toDateString() }}" required></div>
                        <div class="col-md-3"><label class="form-label">Due date</label><input class="form-control" type="date" name="due_date" value="{{ $task->due_date->toDateString() }}" required></div>
                        <div class="col-md-6"><label class="form-label">Description</label><textarea class="form-control" name="description" rows="2">{{ $task->description }}</textarea></div>
                    </div><x-form-save-actions save-label="Update task" />
                </form></div></div>
                <div class="modal fade" id="cancelTask{{ $task->id }}" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('tasks.cancel',$task) }}">@csrf
                    <div class="modal-header"><h2 class="modal-title fs-5"><i class="fa-solid fa-ban me-2 text-danger"></i>Cancel task</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body"><label class="form-label">Cancellation reason <span class="text-danger">*</span></label><textarea class="form-control" name="reason" minlength="10" required placeholder="State why this task is being cancelled..."></textarea></div><x-form-save-actions save-label="Confirm cancellation" />
                </form></div></div>
            @endif
        @endforeach
    @endcan

    @foreach($tasks as $task)
        @if($task->status === 'waiting_verification')
            <div class="modal fade" id="returnTask{{ $task->id }}" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('tasks.verify',[$task,'return']) }}">@csrf
                <div class="modal-header"><h2 class="modal-title fs-5"><i class="fa-solid fa-reply me-2 text-warning"></i>Return task for revision</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><label class="form-label">Required changes / Feedback <span class="text-danger">*</span></label><textarea class="form-control" name="manager_note" minlength="3" required placeholder="Specify what needs to be improved before verification..."></textarea></div><x-form-save-actions save-label="Return to employee" />
            </form></div></div>
        @endif
    @endforeach
</x-layouts::app>
