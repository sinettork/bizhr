<x-layouts::app title="My Tasks">
    <x-workspace-command-bar title="My Work & Tasks" icon="fa-square-check" context="Personal Workspace" />

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
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
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $task)
                        @php
                            $statusBadge = match($task->effective_status) {
                                'verified' => 'success',
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
                            <td class="ps-3">
                                <div class="fw-medium text-dark">{{ $task->title }}</div>
                                @if($task->manager_note)<small class="text-body-secondary"><i class="fa-solid fa-comment me-1 text-warning"></i>{{ \Illuminate\Support\Str::limit($task->manager_note, 50) }}</small>@endif
                            </td>
                            <td><span class="badge text-bg-{{ $priorityBadge }} text-uppercase">{{ $task->priority }}</span></td>
                            <td>
                                <span class="{{ $task->effective_status === 'overdue' ? 'text-danger fw-bold' : '' }}">
                                    {{ $task->due_date->format('d M Y') }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2" style="max-width: 140px;">
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
                                @if(!in_array($task->status, ['verified','cancelled']))
                                    <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#updateTask{{ $task->id }}">
                                        <i class="fa-solid fa-pen"></i><span>Update</span>
                                    </button>
                                @else
                                    <span class="small text-body-secondary"><i class="fa-solid fa-lock me-1"></i>Closed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-body-secondary py-5" colspan="6">
                                <i class="fa-solid fa-clipboard-check fa-xl d-block mb-3 text-primary"></i>You have no tasks assigned at this moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$tasks" />
    </div>

    @foreach($tasks as $task)
        @if(!in_array($task->status, ['verified','cancelled']))
            <div class="modal fade" id="updateTask{{ $task->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('tasks.progress', $task) }}">
                        @csrf
                        <div class="modal-header">
                            <h2 class="modal-title fs-5">Update Task Progress · {{ $task->title }}</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Completion Progress (0–100%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input class="form-control" type="number" min="0" max="100" name="progress" value="{{ $task->progress }}" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div>
                                <label class="form-label">Work Note / Deliverable update</label>
                                <textarea class="form-control" name="employee_note" rows="3" placeholder="Describe work done...">{{ $task->employee_note }}</textarea>
                            </div>
                        </div>
                        <x-form-save-actions save-label="Save update" />
                    </form>
                </div>
            </div>
        @endif
    @endforeach
</x-layouts::app>
