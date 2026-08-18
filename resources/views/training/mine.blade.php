<x-layouts::app title="My Training & Courses">
    <x-workspace-command-bar title="My Training & Certifications" icon="fa-book-open" context="Personal Workspace" />

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
                        <th class="ps-3">Course</th>
                        <th>Deadline</th>
                        <th>Progress</th>
                        <th>Score</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrollments as $enrollment)
                        @php
                            $isCompleted = $enrollment->status === 'completed';
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="fw-medium text-dark">{{ $enrollment->course?->title }}</div>
                                @if($enrollment->course?->description)<small class="text-body-secondary">{{ \Illuminate\Support\Str::limit($enrollment->course->description, 60) }}</small>@endif
                            </td>
                            <td>{{ $enrollment->due_date?->format('d M Y') ?? 'Flexible' }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2" style="max-width: 150px;">
                                    <div class="progress flex-grow-1" style="height: 6px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ max(0, min(100, $enrollment->progress)) }}%;"></div>
                                    </div>
                                    <span class="small fw-semibold">{{ $enrollment->progress }}%</span>
                                </div>
                            </td>
                            <td>
                                @if($enrollment->score !== null)
                                    <span class="badge text-bg-dark"><i class="fa-solid fa-star text-warning me-1"></i>{{ $enrollment->score }}</span>
                                @else
                                    <span class="text-body-secondary small">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge text-bg-{{ $isCompleted ? 'success' : 'primary' }}">{{ str($enrollment->status)->replace('_', ' ')->title() }}</span>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                @if(!$isCompleted)
                                    <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#updateCourse{{ $enrollment->id }}">
                                        <i class="fa-solid fa-pen"></i><span>Update progress</span>
                                    </button>
                                @else
                                    <span class="small text-success"><i class="fa-solid fa-circle-check me-1"></i>Completed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-body-secondary py-5" colspan="6">
                                <i class="fa-solid fa-graduation-cap fa-xl d-block mb-3 text-primary"></i>No training courses assigned to you at this time.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$enrollments" />
    </div>

    @foreach($enrollments as $enrollment)
        @if($enrollment->status !== 'completed')
            <div class="modal fade" id="updateCourse{{ $enrollment->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('training.progress', $enrollment) }}">
                        @csrf
                        <div class="modal-header">
                            <h2 class="modal-title fs-5">Update Training Progress · {{ $enrollment->course?->title }}</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Progress Percentage (0–100%) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input class="form-control" type="number" min="0" max="100" name="progress" value="{{ $enrollment->progress }}" required>
                                    <span class="input-group-text">%</span>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Assessment Score (optional)</label>
                                <input class="form-control" type="number" step=".01" min="0" max="100" name="score" value="{{ $enrollment->score }}" placeholder="e.g. 85.0">
                            </div>
                        </div>
                        <x-form-save-actions save-label="Save progress" />
                    </form>
                </div>
            </div>
        @endif
    @endforeach
</x-layouts::app>
