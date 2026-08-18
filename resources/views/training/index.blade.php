<x-layouts::app title="Training & Learning Catalogue">
    <x-workspace-command-bar title="Training Catalogue & Programs" icon="fa-graduation-cap" context="Learning & Development">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET" data-live-search-form hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search course by title..." data-live-search hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms">
                </div>
            </form>
        </x-slot:filters>
        <x-slot:actions>
            <x-list-actions :add-modal="auth()->user()->can('training.manage') ? 'createCourse' : null" add-label="Add new course" />
        </x-slot:actions>
    </x-workspace-command-bar>

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
                        <th class="ps-3">Course Title</th>
                        <th>Type</th>
                        <th>Duration</th>
                        <th>Enrolled</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-medium text-dark">{{ $course->title }}</div>
                                @if($course->description)<small class="text-body-secondary">{{ \Illuminate\Support\Str::limit($course->description, 60) }}</small>@endif
                            </td>
                            <td>
                                @if($course->is_mandatory)
                                    <span class="badge text-bg-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i>Mandatory</span>
                                @else
                                    <span class="badge text-bg-light border text-dark">Optional</span>
                                @endif
                            </td>
                            <td>{{ number_format($course->duration_minutes/60, 1) }} hrs</td>
                            <td>{{ $course->enrollments_count }} employee(s)</td>
                            <td>
                                <span class="badge text-bg-{{ $course->is_active ? 'success' : 'secondary' }}">{{ $course->is_active ? 'Active' : 'Inactive' }}</span>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                @can('training.manage')
                                    <button class="btn btn-action-link btn-sm" data-bs-toggle="modal" data-bs-target="#enroll{{ $course->id }}">
                                        <i class="fa-solid fa-user-plus"></i><span>Assign</span>
                                    </button>
                                    <button class="btn btn-action-link btn-sm" data-bs-toggle="modal" data-bs-target="#editCourse{{ $course->id }}">
                                        <i class="fa-solid fa-pen"></i><span>Edit</span>
                                    </button>
                                    <form class="d-inline" method="POST" action="{{ route('training.destroy', $course) }}" data-confirm="Archive this course? Active enrollments must be completed first.">
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
                            <td class="text-center text-body-secondary py-5" colspan="6">
                                <i class="fa-solid fa-graduation-cap fa-xl d-block mb-3 text-primary"></i>No training courses found in the catalogue.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$courses" />
    </div>

    @can('training.manage')
        <div class="modal fade" id="createCourse" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="{{ route('training.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title fs-5"><i class="fa-solid fa-graduation-cap me-2 text-primary"></i>Add course to catalogue</h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <x-training-course-fields />
                    </div>
                    <x-form-save-actions :allow-save-new="true" save-label="Create course" />
                </form>
            </div>
        </div>

        @foreach($courses as $course)
            <div class="modal fade" id="editCourse{{ $course->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" method="POST" action="{{ route('training.update', $course) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h2 class="modal-title fs-5"><i class="fa-solid fa-pen me-2 text-primary"></i>Edit course</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <x-training-course-fields :course="$course" />
                        </div>
                        <x-form-save-actions save-label="Save changes" />
                    </form>
                </div>
            </div>

            <div class="modal fade" id="enroll{{ $course->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('training.enroll', $course) }}">
                        @csrf
                        <div class="modal-header">
                            <h2 class="modal-title fs-5"><i class="fa-solid fa-user-plus me-2 text-primary"></i>Assign {{ $course->title }}</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <label class="form-label">Employee <span class="text-danger">*</span></label>
                            <select class="form-select" name="employee_id" required>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->getFullName() }}</option>
                                @endforeach
                            </select>
                            <label class="form-label mt-3">Target completion deadline</label>
                            <input class="form-control" type="date" name="due_date" min="{{ today()->toDateString() }}">
                        </div>
                        <x-form-save-actions save-label="Assign training" />
                    </form>
                </div>
            </div>
        @endforeach
    @endcan
</x-layouts::app>