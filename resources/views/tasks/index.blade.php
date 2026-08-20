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
        <x-slot:actions>
            <x-list-actions :add-modal="auth()->user()->can('task.assign') ? 'createTask' : null" add-label="Assign new task" />
        </x-slot:actions>
    </x-workspace-command-bar>

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
    @endif

    @php
        $pageTasks = collect($tasks->items());
        $taskSummary = [
            ['Open', $pageTasks->whereNotIn('status', ['verified', 'cancelled'])->count()],
            ['Needs review', $pageTasks->where('status', 'waiting_verification')->count()],
            ['Overdue', $pageTasks->where('effective_status', 'overdue')->count()],
            ['Closed', $pageTasks->whereIn('status', ['verified', 'cancelled'])->count()],
        ];
    @endphp

    <div class="workspace-summary" aria-label="Task summary">
        @foreach($taskSummary as [$label, $value])
            <div class="workspace-summary-item">
                <div class="label">{{ $label }}</div>
                <div class="value">{{ number_format($value) }}</div>
            </div>
        @endforeach
    </div>

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Task</th>
                        <th>Assignee</th>
                        <th>Priority</th>
                        <th>Due</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        @php
                            $statusTone = match($task->effective_status) {
                                'verified' => 'success',
                                'overdue' => 'danger',
                                'waiting_verification' => 'warning',
                                'in_progress' => 'primary',
                                default => 'secondary',
                            };
                            $priorityTone = match($task->priority) {
                                'urgent' => 'danger',
                                'high' => 'warning',
                                'medium' => 'info',
                                default => 'secondary',
                            };
                        @endphp
                        <tr>
                            <td class="ps-3" style="min-width:240px">
                                <div class="fw-semibold text-dark">{{ $task->title }}</div>
                                @if($task->description)
                                    <small class="text-body-secondary">{{ \Illuminate\Support\Str::limit($task->description, 90) }}</small>
                                @endif
                                @if($task->manager_note)
                                    <small class="d-block text-body-secondary mt-1"><i class="fa-regular fa-comment me-1"></i>{{ \Illuminate\Support\Str::limit($task->manager_note, 80) }}</small>
                                @endif
                            </td>
                            <td>
                                <div>{{ $task->employee?->getFullName() }}</div>
                                <small class="text-body-secondary">{{ $task->employee?->department?->name ?? '—' }}</small>
                            </td>
                            <td><span class="status-text text-bg-{{ $priorityTone }}">{{ ucfirst($task->priority) }}</span></td>
                            <td class="{{ $task->effective_status === 'overdue' ? 'text-danger fw-semibold' : '' }}">{{ $task->due_date->format('d M Y') }}</td>
                            <td style="min-width:140px">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:5px"><div class="progress-bar" style="width:{{ max(0,min(100,$task->progress)) }}%"></div></div>
                                    <span class="small fw-semibold">{{ $task->progress }}%</span>
                                </div>
                            </td>
                            <td><span class="status-text text-bg-{{ $statusTone }}">{{ str($task->effective_status)->replace('_',' ')->title() }}</span></td>
                            <td class="text-end pe-3 text-nowrap">
                                @if($task->status === 'waiting_verification')
                                    <form method="POST" action="{{ route('tasks.verify', [$task, 'approve']) }}" class="d-inline" data-confirm="Verify this task as complete?" data-confirm-title="Verify task" data-confirm-action="Verify" data-confirm-tone="primary">
                                        @csrf
                                        <button class="btn btn-primary btn-sm" type="submit"><i class="fa-solid fa-check me-1"></i>Verify</button>
                                    </form>
                                    <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#returnTask{{ $task->id }}"><i class="fa-solid fa-reply"></i><span>Return</span></button>
                                @endif
                                @can('task.assign')
                                    @if(!in_array($task->status,['verified','cancelled'],true))
                                        <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#editTask{{ $task->id }}"><i class="fa-solid fa-pen"></i><span>Edit</span></button>
                                        <button class="btn btn-action-link btn-sm text-danger" type="button" data-bs-toggle="modal" data-bs-target="#cancelTask{{ $task->id }}"><i class="fa-solid fa-ban"></i><span>Cancel</span></button>
                                    @endif
                                @endcan
                                @if(in_array($task->status,['verified','cancelled'],true))
                                    <span class="small text-body-secondary">Closed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-0"><x-empty-state class="py-5 px-3" icon="fa-clipboard-check" title="No tasks found" message="No tasks match the current filters." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$tasks" />
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
