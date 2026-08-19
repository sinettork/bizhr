<x-layouts::app title="My Tasks">
    <x-workspace-command-bar title="My Work & Tasks" icon="fa-square-check" context="Personal Workspace">
        <x-slot:filters>
            <div class="d-flex flex-wrap gap-2">
                @foreach([
                    ['All', null],
                    ['Open', 'open'],
                    ['Overdue', 'overdue'],
                    ['Waiting verification', 'waiting_verification'],
                    ['Completed', 'completed'],
                ] as [$label, $value])
                    <a class="btn btn-sm {{ $status === ($value ?? '') ? 'btn-primary' : 'btn-outline-secondary' }}" href="{{ route('tasks.mine', array_filter(['status' => $value])) }}">{{ $label }}</a>
                @endforeach
            </div>
        </x-slot:filters>
    </x-workspace-command-bar>

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
    @endif

    <div class="row g-2 mb-3">
        @foreach([
            ['Open', $statistics['open'], 'fa-list-check', 'primary'],
            ['Overdue', $statistics['overdue'], 'fa-clock', 'danger'],
            ['Waiting verification', $statistics['waiting'], 'fa-hourglass-half', 'warning'],
            ['Completed', $statistics['completed'], 'fa-circle-check', 'success'],
        ] as [$label, $value, $icon, $tone])
            <div class="col-6 col-xl-3">
                <section class="profile-card h-100 mb-0">
                    <div class="profile-card-body py-3">
                        <div class="d-flex align-items-center gap-3">
                            <span class="metric-icon bg-{{ $tone }}-subtle text-{{ $tone }}"><i class="fa-solid {{ $icon }}"></i></span>
                            <div>
                                <div class="small text-body-secondary">{{ $label }}</div>
                                <div class="fs-5 fw-bold text-dark">{{ number_format($value) }}</div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        @endforeach
    </div>

    @if($statistics['overdue'] > 0)
        <div class="alert alert-warning d-flex align-items-start gap-2">
            <i class="fa-solid fa-triangle-exclamation mt-1"></i>
            <div><strong>{{ $statistics['overdue'] }} overdue task{{ $statistics['overdue'] === 1 ? '' : 's' }}.</strong> Update progress or add a work note so your manager can see the latest status.</div>
        </div>
    @endif

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Task</th>
                        <th>Priority</th>
                        <th>Due Date</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Next Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        @php
                            $statusBadge = match($task->effective_status) {
                                'verified' => 'success',
                                'completed' => 'success',
                                'overdue' => 'danger',
                                'waiting_verification' => 'warning',
                                'in_progress' => 'primary',
                                default => 'secondary',
                            };
                            $priorityBadge = match($task->priority) {
                                'urgent' => 'danger',
                                'high' => 'warning',
                                'medium' => 'info',
                                default => 'light border text-dark',
                            };
                        @endphp
                        <tr>
                            <td class="ps-3" style="min-width: 230px;">
                                <div class="fw-semibold text-dark">{{ $task->title }}</div>
                                <div class="small text-body-secondary mt-1">
                                    Assigned by {{ $task->assigner?->name ?? 'Manager' }}
                                    @if($task->manager_note)
                                        · <span class="text-warning-emphasis"><i class="fa-solid fa-comment me-1"></i>{{ \Illuminate\Support\Str::limit($task->manager_note, 70) }}</span>
                                    @endif
                                </div>
                            </td>
                            <td><span class="badge text-bg-{{ $priorityBadge }} text-uppercase">{{ $task->priority }}</span></td>
                            <td>
                                <span class="{{ $task->effective_status === 'overdue' ? 'text-danger fw-bold' : '' }}">
                                    {{ $task->due_date->format('d M Y') }}
                                </span>
                                @if($task->effective_status === 'overdue')
                                    <small class="d-block text-danger">Past due</small>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2" style="min-width: 130px; max-width: 170px;">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ max(0, min(100, $task->progress)) }}%;"></div>
                                    </div>
                                    <span class="small fw-semibold">{{ $task->progress }}%</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge text-bg-{{ $statusBadge }}">{{ str($task->effective_status)->replace('_', ' ')->title() }}</span>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                @if(!in_array($task->status, ['verified','cancelled'], true))
                                    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#updateTask{{ $task->id }}">
                                        <i class="fa-solid fa-pen me-1"></i>{{ $task->progress >= 100 ? 'Submit update' : 'Update progress' }}
                                    </button>
                                @else
                                    <span class="small text-body-secondary"><i class="fa-solid fa-lock me-1"></i>Closed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-0">
                                <x-empty-state class="py-5 px-3" icon="fa-clipboard-check" tone="success" title="No tasks here" message="There are no tasks matching this view." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$tasks" />
    </div>

    @foreach($tasks as $task)
        @if(!in_array($task->status, ['verified','cancelled'], true))
            <div class="modal fade" id="updateTask{{ $task->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('tasks.progress', $task) }}">
                        @csrf
                        <div class="modal-header">
                            <div>
                                <div class="small text-body-secondary">Update task</div>
                                <h2 class="modal-title fs-5">{{ $task->title }}</h2>
                            </div>
                            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-light border small">
                                <i class="fa-solid fa-circle-info text-primary me-1"></i>
                                Set progress to 100% when your work is ready for manager verification. Add a short note describing what was completed.
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Completion progress <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input class="form-control" type="number" min="0" max="100" name="progress" value="{{ $task->progress }}" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Work note</label>
                                <textarea class="form-control" name="employee_note" rows="3" placeholder="Example: Completed the report and shared the final file with the team.">{{ $task->employee_note }}</textarea>
                                <div class="form-text">Use this to explain progress, blockers, or what is ready for review.</div>
                            </div>
                        </div>
                        <x-form-save-actions save-label="Save update" />
                    </form>
                </div>
            </div>
        @endif
    @endforeach
</x-layouts::app>
