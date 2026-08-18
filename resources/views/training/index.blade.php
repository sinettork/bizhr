<x-layouts::app title="Training & Learning Catalogue">
    <x-workspace-command-bar title="Training Catalogue & Programs" icon="fa-graduation-cap" context="Learning & Development">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search courses">
                </div>
                <button class="btn btn-primary reference-search-button">Search</button>
            </form>
        </x-slot:filters>
        <x-slot:actions>
            <x-list-actions :add-modal="auth()->user()->can('training.manage') ? 'createCourse' : null" add-label="Add course" />
        </x-slot:actions>
    </x-workspace-command-bar>

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
    @endif

    <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-2">
        <div>
            <div class="small text-uppercase text-body-secondary fw-semibold">Learning library</div>
            <h2 class="h6 fw-bold mb-0">Available programs</h2>
        </div>
        <span class="small text-body-secondary">{{ number_format($courses->total()) }} course{{ $courses->total() === 1 ? '' : 's' }}</span>
    </div>

    <div class="row g-3 reference-list" data-list-container>
        @forelse($courses as $course)
            <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
                <article class="profile-card h-100 mb-0 d-flex flex-column">
                    <div class="profile-card-body flex-grow-1 p-3">
                        <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                            <span class="metric-icon {{ $course->is_mandatory ? 'bg-danger-subtle text-danger' : 'bg-primary-subtle text-primary' }} flex-shrink-0">
                                <i class="fa-solid {{ $course->is_mandatory ? 'fa-shield-halved' : 'fa-book-open' }}"></i>
                            </span>
                            <span class="status-text text-bg-{{ $course->is_active ? 'success' : 'secondary' }}">{{ $course->is_active ? 'Active' : 'Inactive' }}</span>
                        </div>

                        <div class="mb-3">
                            <div class="small text-uppercase text-body-secondary fw-semibold mb-1">{{ $course->is_mandatory ? 'Mandatory training' : 'Optional learning' }}</div>
                            <h3 class="h6 fw-bold text-dark mb-1">{{ $course->title }}</h3>
                            <p class="small text-body-secondary mb-0">{{ $course->description ? \Illuminate\Support\Str::limit($course->description, 105) : 'No course description provided.' }}</p>
                        </div>

                        <div class="d-flex flex-wrap gap-3 pt-3 border-top small">
                            <span class="text-body-secondary"><i class="fa-regular fa-clock me-1"></i><strong class="text-dark">{{ number_format($course->duration_minutes / 60, 1) }}</strong> hrs</span>
                            <span class="text-body-secondary"><i class="fa-solid fa-user-group me-1"></i><strong class="text-dark">{{ number_format($course->enrollments_count) }}</strong> assigned</span>
                        </div>
                    </div>

                    @can('training.manage')
                        <div class="card-footer bg-white d-flex align-items-center justify-content-between gap-2 py-2 px-3">
                            <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#enroll{{ $course->id }}">
                                <i class="fa-solid fa-user-plus"></i><span>Assign</span>
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-action-link btn-sm px-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="More actions for {{ $course->title }}">
                                    <i class="fa-solid fa-ellipsis-vertical"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                    <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#editCourse{{ $course->id }}"><i class="fa-solid fa-pen me-2"></i>Edit course</button></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('training.destroy',$course) }}" data-confirm="Archive this course? Active enrollments must be completed first.">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item text-danger" type="submit"><i class="fa-solid fa-box-archive me-2"></i>Archive course</button>
                                        </form>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    @endcan
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="profile-card mb-0">
                    <x-empty-state class="py-5 px-3" icon="fa-graduation-cap" title="No training courses" message="Training programs will appear here when they are created." />
                </div>
            </div>
        @endforelse
        <div class="col-12"><x-pagination-footer :paginator="$courses" /></div>
    </div>

    @can('training.manage')
        <div class="modal fade" id="createCourse" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="{{ route('training.store') }}">@csrf
                    <div class="modal-header"><h2 class="modal-title fs-5"><i class="fa-solid fa-graduation-cap me-2 text-primary"></i>Add course</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body"><x-training-course-fields /></div>
                    <x-form-save-actions :allow-save-new="true" save-label="Create course" />
                </form>
            </div>
        </div>

        @foreach($courses as $course)
            <div class="modal fade" id="editCourse{{ $course->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" method="POST" action="{{ route('training.update',$course) }}">@csrf @method('PUT')
                        <div class="modal-header"><h2 class="modal-title fs-5"><i class="fa-solid fa-pen me-2 text-primary"></i>Edit course</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body"><x-training-course-fields :course="$course" /></div>
                        <x-form-save-actions save-label="Save changes" />
                    </form>
                </div>
            </div>

            <div class="modal fade" id="enroll{{ $course->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('training.enroll',$course) }}">@csrf
                        <div class="modal-header"><h2 class="modal-title fs-5"><i class="fa-solid fa-user-plus me-2 text-primary"></i>Assign {{ $course->title }}</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                        <div class="modal-body">
                            <label class="form-label">Employee <span class="text-danger">*</span></label>
                            <select class="form-select" name="employee_id" required>@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->getFullName() }}</option>@endforeach</select>
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
