<x-layouts::app title="My Performance Goals">
    <x-workspace-command-bar title="My Goals & Targets" icon="fa-flag" context="Personal Workspace" />

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
                        <th class="ps-3">Goal</th>
                        <th>Target</th>
                        <th>My Reported Progress</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($goals as $goal)
                        @php
                            $statusBadge = match($goal->status) {
                                'achieved' => 'success',
                                'returned' => 'danger',
                                default => 'primary',
                            };
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="fw-medium text-dark">{{ $goal->title }}</div>
                                @if($goal->description)<small class="text-body-secondary">{{ \Illuminate\Support\Str::limit($goal->description, 60) }}</small>@endif
                            </td>
                            <td>{{ $goal->target_value }} {{ $goal->measurement_unit }}</td>
                            <td class="fw-semibold text-primary">
                                {{ $goal->employee_reported_value !== null ? $goal->employee_reported_value.' '.$goal->measurement_unit : '—' }}
                                @if($goal->employee_note)<div class="small text-body-secondary fst-italic">{{ \Illuminate\Support\Str::limit($goal->employee_note, 50) }}</div>@endif
                            </td>
                            <td>{{ $goal->due_date?->format('d M Y') ?? 'Ongoing' }}</td>
                            <td>
                                <span class="badge text-bg-{{ $statusBadge }}">{{ str($goal->status)->title() }}</span>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                @if(in_array($goal->status, ['active','returned']))
                                    <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#updateGoal{{ $goal->id }}">
                                        <i class="fa-solid fa-pen"></i><span>Update progress</span>
                                    </button>
                                @else
                                    <span class="small text-body-secondary"><i class="fa-solid fa-lock me-1"></i>Closed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-body-secondary py-5" colspan="6">
                                <i class="fa-solid fa-flag fa-xl d-block mb-3 text-primary"></i>You have no goals assigned currently.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$goals" />
    </div>

    @foreach($goals as $goal)
        @if(in_array($goal->status, ['active','returned']))
            <div class="modal fade" id="updateGoal{{ $goal->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('performance.my-goals.update', $goal) }}">
                        @csrf
                        <div class="modal-header">
                            <h2 class="modal-title fs-5">Update Goal Progress · {{ $goal->title }}</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3 small">
                                <span class="text-body-secondary">Target:</span>
                                <strong>{{ $goal->target_value }} {{ $goal->measurement_unit }}</strong>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Achieved Value ({{ $goal->measurement_unit }}) <span class="text-danger">*</span></label>
                                <input class="form-control" type="number" step=".01" name="employee_reported_value" value="{{ old('employee_reported_value', $goal->employee_reported_value) }}" required>
                            </div>
                            <div>
                                <label class="form-label">Evidence / Notes <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="employee_note" rows="3" required placeholder="Describe progress and evidence...">{{ old('employee_note', $goal->employee_note) }}</textarea>
                            </div>
                        </div>
                        <x-form-save-actions save-label="Submit progress" />
                    </form>
                </div>
            </div>
        @endif
    @endforeach
</x-layouts::app>
