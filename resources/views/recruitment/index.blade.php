<x-layouts::app title="Recruitment">
    <x-workspace-command-bar title="Recruitment &amp; Talent Acquisition" icon="fa-user-plus">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search vacancies or candidates" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms">
                </div>
                <select class="form-select reference-status" name="status" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                    <option value="">All statuses</option>
                    @foreach(['draft','open','closed','cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->title() }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary reference-search-button">Filter</button>
            </form>
        </x-slot:filters>
        <x-slot:actions>
            @can('recruitment.manage')
                <x-list-actions :add-modal="'addVacancy'" add-label="Open new vacancy" />
            @endcan
        </x-slot:actions>
    </x-workspace-command-bar>

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
    @endif

    <!-- Job Vacancies Table -->
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h2 class="h6 fw-bold text-dark mb-0"><i class="fa-solid fa-folder-open text-primary me-2"></i>Job Vacancies</h2>
        <span class="small text-body-secondary">{{ $vacancies->total() }} vacancies</span>
    </div>

    <div class="reference-list mb-4" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Vacancy Title</th>
                        <th>Position &amp; Branch</th>
                        <th>Openings</th>
                        <th>Dates</th>
                        <th>Candidates</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vacancies as $vacancy)
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold text-dark">{{ $vacancy->title }}</div>
                            </td>
                            <td>
                                <div>{{ $vacancy->position?->title ?: 'General' }}</div>
                                <small class="text-body-secondary">{{ $vacancy->branch?->name ?: 'All branches' }}</small>
                            </td>
                            <td>{{ $vacancy->openings }} opening(s)</td>
                            <td>
                                {{ $vacancy->open_date?->format('d M Y') }} – {{ $vacancy->close_date?->format('d M Y') ?: 'Open-ended' }}
                            </td>
                            <td>
                                <span class="fw-semibold text-primary"><i class="fa-solid fa-users me-1"></i>{{ $vacancy->applicants_count }} candidate(s)</span>
                            </td>
                            <td>
                                <span class="badge text-bg-{{ $vacancy->status === 'open' ? 'success' : 'secondary' }}">{{ str($vacancy->status)->title() }}</span>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                @can('recruitment.manage')
                                    <button class="btn btn-action-link btn-sm" data-bs-toggle="modal" data-bs-target="#addApplicant{{ $vacancy->id }}">
                                        <i class="fa-solid fa-user-plus"></i><span>Add candidate</span>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-body-secondary py-4" colspan="7">No job vacancies found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$vacancies" />
    </div>

    <!-- Candidate Pipeline Section -->
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h2 class="h6 fw-bold text-dark mb-0"><i class="fa-solid fa-diagram-project text-primary me-2"></i>Candidate Pipeline</h2>
        <span class="small text-body-secondary">{{ $applicants->count() }} candidate(s)</span>
    </div>

    <div class="reference-list">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Candidate</th>
                        <th>Vacancy</th>
                        <th>Contact</th>
                        <th>Applied Date</th>
                        <th>Stage</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($applicants as $applicant)
                        @php
                            $stageColors = [
                                'applied' => 'secondary',
                                'screening' => 'info',
                                'shortlisted' => 'primary',
                                'interview' => 'warning',
                                'offer_pending' => 'dark',
                                'offered' => 'primary',
                                'accepted' => 'success',
                                'hired' => 'success',
                                'rejected' => 'danger',
                                'declined' => 'danger',
                            ];
                            $next = [
                                'applied' => ['screening', 'rejected'],
                                'screening' => ['shortlisted', 'rejected'],
                                'shortlisted' => ['interview', 'rejected'],
                                'interview' => ['offer_pending', 'rejected'],
                                'offer_pending' => ['offered', 'rejected'],
                                'offered' => ['accepted', 'declined'],
                                'accepted' => ['hired'],
                            ][$applicant->status] ?? [];
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold text-dark">{{ $applicant->full_name }}</div>
                                @if($applicant->hr_note)<small class="text-body-secondary"><i class="fa-solid fa-note-sticky text-warning me-1"></i>{{ \Illuminate\Support\Str::limit($applicant->hr_note, 50) }}</small>@endif
                            </td>
                            <td>{{ $applicant->vacancy->title }}</td>
                            <td>
                                <div><a href="mailto:{{ $applicant->email }}" class="text-decoration-none text-body">{{ $applicant->email }}</a></div>
                                <small class="text-body-secondary">{{ $applicant->phone }}</small>
                            </td>
                            <td>{{ $applicant->applied_at?->format('d M Y') ?? '—' }}</td>
                            <td>
                                <span class="badge text-bg-{{ $stageColors[$applicant->status] ?? 'secondary' }}">{{ str($applicant->status)->replace('_', ' ')->title() }}</span>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                @if($applicant->cv_path)
                                    <a class="btn btn-action-link btn-sm" href="{{ route('recruitment.applicants.cv', $applicant) }}" target="_blank">
                                        <i class="fa-solid fa-file-arrow-down"></i><span>CV</span>
                                    </a>
                                @endif

                                @can('recruitment.manage')
                                    @if(count($next) > 0)
                                        <form class="d-inline-flex gap-1 align-items-center" method="POST" action="{{ route('recruitment.applicants.transition', $applicant) }}">
                                            @csrf
                                            <select class="form-select form-select-sm" name="status" style="width: auto; font-size: .75rem; padding: .2rem .5rem;" required>
                                                <option value="">Move stage...</option>
                                                @foreach($next as $stage)
                                                    <option value="{{ $stage }}">{{ str($stage)->replace('_', ' ')->title() }}</option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-sm btn-primary" type="submit" style="font-size: .75rem; padding: .2rem .55rem;">Go</button>
                                        </form>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-body-secondary py-4" colspan="6">No candidates found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modals -->
    @can('recruitment.manage')
        <div class="modal fade" id="addVacancy" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <form class="modal-content" method="POST" action="{{ route('recruitment.vacancies.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title fs-5"><i class="fa-solid fa-briefcase me-2 text-primary"></i>Open new vacancy</h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Title <span class="text-danger">*</span></label>
                                <input class="form-control" name="title" required placeholder="e.g. Senior Software Engineer">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Openings <span class="text-danger">*</span></label>
                                <input class="form-control" type="number" name="openings" value="1" min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Position</label>
                                <select class="form-select" name="position_id">
                                    <option value="">Unspecified</option>
                                    @foreach($positions as $position)
                                        <option value="{{ $position->id }}">{{ $position->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Branch</label>
                                <select class="form-select" name="branch_id">
                                    <option value="">All branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Open date</label>
                                <input class="form-control" type="date" name="open_date" value="{{ today()->toDateString() }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Close date</label>
                                <input class="form-control" type="date" name="close_date">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="description" minlength="20" rows="4" required placeholder="Provide role responsibilities, key requirements, qualifications..."></textarea>
                            </div>
                        </div>
                    </div>
                    <x-form-save-actions save-label="Open vacancy" />
                </form>
            </div>
        </div>

        @foreach($vacancies as $vacancy)
            <div class="modal fade" id="addApplicant{{ $vacancy->id }}" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <form class="modal-content" method="POST" enctype="multipart/form-data" action="{{ route('recruitment.applicants.store', $vacancy) }}">
                        @csrf
                        <div class="modal-header">
                            <h2 class="modal-title fs-5"><i class="fa-solid fa-user-plus me-2 text-primary"></i>Add candidate · {{ $vacancy->title }}</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full name <span class="text-danger">*</span></label>
                                    <input class="form-control" name="full_name" required placeholder="Candidate full name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input class="form-control" type="email" name="email" required placeholder="candidate@example.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input class="form-control" name="phone" required placeholder="+855 ...">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">CV / Resume (PDF, DOC)</label>
                                    <input class="form-control" type="file" name="cv" accept=".pdf,.doc,.docx">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">HR Evaluation Note</label>
                                    <textarea class="form-control" name="hr_note" placeholder="Initial impressions, salary expectation, source..."></textarea>
                                </div>
                            </div>
                        </div>
                        <x-form-save-actions save-label="Add candidate" />
                    </form>
                </div>
            </div>
        @endforeach
    @endcan
</x-layouts::app>