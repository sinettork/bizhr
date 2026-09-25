<x-layouts::app title="Recruitment">
    <x-workspace-command-bar title="Recruitment &amp; Talent Acquisition" icon="fa-user-plus" context="Hiring Pipeline &amp; Talent Journey">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search vacancies or roles">
                </div>
                <select class="form-select reference-status" name="status">
                    <option value="">All vacancy statuses</option>
                    @foreach(['draft','open','closed','cancelled'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->title() }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary reference-search-button">Filter</button>
                @if(request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('recruitment.pipeline') }}" class="btn btn-action-link btn-sm"><i class="fa-solid fa-rotate-left"></i><span>Clear</span></a>
                @endif
            </form>
        </x-slot:filters>
        <x-slot:actions>
            @can('recruitment.manage')
                <button class="btn btn-primary btn-sm px-3" type="button" data-bs-toggle="modal" data-bs-target="#addVacancy">
                    <i class="fa-solid fa-plus me-1"></i><span>Open new vacancy</span>
                </button>
            @endcan
        </x-slot:actions>
    </x-workspace-command-bar>

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3">
            <i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3">
            <i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span>
        </div>
    @endif

    {{-- Recruitment Funnel Metrics Strip (Archetype C) --}}
    @php
        $activeVacanciesCount = $vacancies->where('status', 'open')->count();
        $totalOpenings = $vacancies->where('status', 'open')->sum('openings');
        $activeApplicants = $applicants->whereNotIn('status', ['rejected', 'declined']);
        $inInterview = $applicants->where('status', 'interview')->count();
        $inOffer = $applicants->whereIn('status', ['offer_pending', 'offered', 'accepted'])->count();
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-6 col-xl-3">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-body p-3">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="small text-body-secondary fw-semibold text-uppercase">Active Vacancies</div>
                            <div class="fs-4 fw-bold text-dark mt-1">
                                {{ $activeVacanciesCount }} <span class="fs-6 fw-normal text-body-secondary">({{ $totalOpenings }} openings)</span>
                            </div>
                        </div>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary" style="width:36px;height:36px">
                            <i class="fa-solid fa-briefcase"></i>
                        </span>
                    </div>
                    <div class="small text-body-secondary mt-2">Open recruitment campaigns</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-body p-3">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="small text-body-secondary fw-semibold text-uppercase">Total in Pipeline</div>
                            <div class="fs-4 fw-bold text-dark mt-1">{{ $activeApplicants->count() }} <span class="fs-6 fw-normal text-body-secondary">candidates</span></div>
                        </div>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-body-tertiary text-dark" style="width:36px;height:36px">
                            <i class="fa-solid fa-users"></i>
                        </span>
                    </div>
                    <div class="small text-body-secondary mt-2">Active applicants across all stages</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-body p-3">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="small text-body-secondary fw-semibold text-uppercase">Interviewing</div>
                            <div class="fs-4 fw-bold text-info mt-1">{{ $inInterview }} <span class="fs-6 fw-normal text-body-secondary">evaluations</span></div>
                        </div>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-info-subtle text-info" style="width:36px;height:36px">
                            <i class="fa-solid fa-comments"></i>
                        </span>
                    </div>
                    <div class="small text-body-secondary mt-2">Active interview stage</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-body p-3">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="small text-body-secondary fw-semibold text-uppercase">Offer &amp; Hired</div>
                            <div class="fs-4 fw-bold text-success mt-1">{{ $inOffer }} <span class="fs-6 fw-normal text-body-secondary">candidates</span></div>
                        </div>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-success-subtle text-success" style="width:36px;height:36px">
                            <i class="fa-solid fa-file-signature"></i>
                        </span>
                    </div>
                    <div class="small text-body-secondary mt-2">Offers pending or finalized</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Vacancies Overview Strip --}}
    <section class="mb-4">
        <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
            <div>
                <div class="small text-uppercase text-body-secondary fw-semibold">Open Roles &amp; Openings</div>
                <h2 class="h6 fw-bold mb-0 text-dark">Active Recruitment Campaigns</h2>
            </div>
            <span class="small text-body-secondary">{{ $vacancies->total() }} vacancy role(s)</span>
        </div>

        <div class="row g-3">
            @forelse($vacancies as $vacancy)
                <div class="col-md-6 col-xl-4">
                    <article class="profile-card h-100 mb-0">
                        <div class="profile-card-body p-3">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <h3 class="h6 fw-bold mb-1 text-dark">{{ $vacancy->title }}</h3>
                                    <div class="small text-body-secondary">
                                        {{ $vacancy->position?->title ?: 'General position' }}
                                        @if($vacancy->branch)
                                            <span class="mx-1">·</span>{{ $vacancy->branch->name }}
                                        @endif
                                    </div>
                                </div>
                                <span class="badge text-bg-{{ $vacancy->status === 'open' ? 'success' : 'secondary' }}">
                                    {{ str($vacancy->status)->title() }}
                                </span>
                            </div>

                            <div class="d-flex gap-3 small p-2 bg-body-tertiary rounded border mt-3">
                                <div><span class="text-body-secondary d-block">Openings</span><strong class="text-dark">{{ $vacancy->openings }}</strong></div>
                                <div class="border-start ps-3"><span class="text-body-secondary d-block">Candidates</span><strong class="text-primary">{{ $vacancy->applicants_count }}</strong></div>
                                <div class="border-start ps-3"><span class="text-body-secondary d-block">Deadline</span><strong class="text-dark">{{ $vacancy->close_date?->format('d M') ?: 'Open' }}</strong></div>
                            </div>
                        </div>

                        @can('recruitment.manage')
                            <div class="card-footer bg-body-tertiary border-top py-2 px-3 d-flex justify-content-end">
                                <button class="btn btn-action-link btn-sm text-primary" type="button" data-bs-toggle="modal" data-bs-target="#addApplicant{{ $vacancy->id }}">
                                    <i class="fa-solid fa-user-plus me-1"></i><span>Add candidate</span>
                                </button>
                            </div>
                        @endcan
                    </article>
                </div>
            @empty
                <div class="col-12">
                    <div class="profile-card mb-0">
                        <div class="profile-card-body text-center text-body-secondary py-4">
                            <i class="fa-solid fa-briefcase fa-xl d-block mb-2 text-primary opacity-50"></i>No active vacancies match this filter.
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @if($vacancies->total() > $vacancies->perPage())
            <div class="mt-3"><x-pagination-footer :paginator="$vacancies" /></div>
        @endif
    </section>

    {{-- Interactive Hiring Pipeline Board (Archetype C: Kanban Flow) --}}
    @php
        $pipeline = [
            'applied'     => ['Applied', 'fa-inbox', 'border-secondary', 'badge text-bg-secondary'],
            'screening'   => ['Screening', 'fa-magnifying-glass', 'border-info', 'badge text-bg-info text-white'],
            'shortlisted' => ['Shortlisted', 'fa-star', 'border-warning', 'badge text-bg-warning text-dark'],
            'interview'   => ['Interview', 'fa-comments', 'border-primary', 'badge text-bg-primary'],
            'offer'       => ['Offer Extended', 'fa-file-signature', 'border-indigo', 'badge text-bg-dark'],
            'hired'       => ['Hired', 'fa-user-check', 'border-success', 'badge text-bg-success'],
        ];

        $candidateGroup = function ($status) {
            return match ($status) {
                'offer_pending', 'offered', 'accepted' => 'offer',
                'rejected', 'declined' => null,
                default => $status,
            };
        };

        $groupedApplicants = $applicants->groupBy(fn ($applicant) => $candidateGroup($applicant->status));

        $primaryAdvance = [
            'applied' => ['stage' => 'screening', 'label' => 'Screen candidate'],
            'screening' => ['stage' => 'shortlisted', 'label' => 'Shortlist'],
            'shortlisted' => ['stage' => 'interview', 'label' => 'Schedule interview'],
            'interview' => ['stage' => 'offer_pending', 'label' => 'Prepare offer'],
            'offer_pending' => ['stage' => 'offered', 'label' => 'Send offer'],
            'offered' => ['stage' => 'accepted', 'label' => 'Accept offer'],
            'accepted' => ['stage' => 'hired', 'label' => 'Mark as hired'],
        ];

        $nextStages = [
            'applied' => ['screening', 'rejected'],
            'screening' => ['shortlisted', 'rejected'],
            'shortlisted' => ['interview', 'rejected'],
            'interview' => ['offer_pending', 'rejected'],
            'offer_pending' => ['offered', 'rejected'],
            'offered' => ['accepted', 'declined'],
            'accepted' => ['hired'],
        ];
    @endphp

    <section class="mb-4">
        <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
            <div>
                <div class="small text-uppercase text-body-secondary fw-semibold">Candidate Pipeline Flow</div>
                <h2 class="h6 fw-bold mb-0 text-dark">Stage-by-Stage Candidate Progression</h2>
            </div>
            <span class="small text-body-secondary">Showing {{ $activeApplicants->count() }} active candidate(s)</span>
        </div>

        <div class="d-flex gap-3 overflow-auto pb-3 reference-list border-0 bg-transparent p-0 shadow-none" data-list-container style="align-items: stretch;">
            @foreach($pipeline as $key => [$label, $icon, $borderClass, $badgeClass])
                @php($rows = $groupedApplicants->get($key, collect()))

                <div style="min-width: 295px; width: 295px; flex-shrink: 0;">
                    <div class="profile-card h-100 mb-0 border-top border-3 {{ $borderClass }}">
                        <div class="profile-card-header py-2 px-3 bg-white border-bottom d-flex align-items-center justify-content-between">
                            <h3 class="profile-card-title fs-6 fw-bold mb-0 text-dark d-flex align-items-center">
                                <i class="fa-solid {{ $icon }} text-primary me-2"></i><span>{{ $label }}</span>
                            </h3>
                            <span class="{{ $badgeClass }} rounded-pill font-monospace">{{ $rows->count() }}</span>
                        </div>

                        <div class="profile-card-body bg-body-tertiary p-2 d-grid gap-2 align-content-start" style="min-height: 480px;">
                            @forelse($rows as $applicant)
                                <article class="card border shadow-sm">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                                            <div class="fw-bold text-dark">{{ $applicant->full_name }}</div>
                                            @if($applicant->cv_path)
                                                <a class="btn btn-outline-secondary btn-sm py-0 px-2" href="{{ route('recruitment.applicants.cv', $applicant) }}" target="_blank" title="Inspect applicant CV">
                                                    <i class="fa-solid fa-file-pdf text-danger me-1"></i><span class="small">CV</span>
                                                </a>
                                            @endif
                                        </div>

                                        <div class="small text-primary fw-medium mb-1">
                                            <i class="fa-solid fa-briefcase me-1 text-body-tertiary"></i>{{ $applicant->vacancy->title }}
                                        </div>

                                        <div class="small text-body-secondary mb-2">
                                            <i class="fa-regular fa-envelope me-1 text-body-tertiary"></i>{{ $applicant->email }}
                                        </div>

                                        @if($applicant->hr_note)
                                            <div class="small text-body-secondary p-2 bg-body-tertiary rounded border mb-2">
                                                <i class="fa-regular fa-note-sticky me-1 text-primary"></i>{{ \Illuminate\Support\Str::limit($applicant->hr_note, 80) }}
                                            </div>
                                        @endif

                                        <div class="d-flex align-items-center justify-content-between gap-1 pt-2 border-top mt-2">
                                            <span class="small text-body-secondary" title="Application date">
                                                <i class="fa-regular fa-calendar me-1"></i>{{ $applicant->applied_at?->format('d M') ?? '—' }}
                                            </span>

                                            @can('recruitment.manage')
                                                <div class="d-flex align-items-center gap-1">
                                                    {{-- Primary Quick Advance Button --}}
                                                    @if(isset($primaryAdvance[$applicant->status]))
                                                        @php($adv = $primaryAdvance[$applicant->status])
                                                        <form method="POST" action="{{ route('recruitment.applicants.transition', $applicant) }}" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="status" value="{{ $adv['stage'] }}">
                                                            <button class="btn btn-primary btn-sm py-0 px-2" type="submit" title="Advance to {{ str($adv['stage'])->replace('_',' ')->title() }}" style="font-size: .78rem;">
                                                                <span>Advance</span> <i class="fa-solid fa-arrow-right ms-1"></i>
                                                            </button>
                                                        </form>
                                                    @endif

                                                    {{-- Dropdown for other stages / rejection --}}
                                                    <div class="dropdown">
                                                        <button class="btn btn-action-link btn-sm py-0 px-1" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Other actions">
                                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                            @foreach($nextStages[$applicant->status] ?? [] as $stage)
                                                                <li>
                                                                    <form method="POST" action="{{ route('recruitment.applicants.transition', $applicant) }}">
                                                                        @csrf
                                                                        <input type="hidden" name="status" value="{{ $stage }}">
                                                                        <button class="dropdown-item small {{ in_array($stage, ['rejected', 'declined'], true) ? 'text-danger' : '' }}" type="submit">
                                                                            Move to {{ str($stage)->replace('_', ' ')->title() }}
                                                                        </button>
                                                                    </form>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            @endcan
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <div class="text-center text-body-secondary small py-5">
                                    <i class="fa-regular fa-folder-open d-block mb-1 text-secondary opacity-50"></i>
                                    No candidates in this stage
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if(($groupedApplicants->get(null, collect()))->isNotEmpty())
            <div class="small text-body-secondary mt-2">
                <i class="fa-solid fa-info-circle me-1 text-primary"></i>Rejected and declined candidates are safely archived in records but filtered from active pipeline columns.
            </div>
        @endif
    </section>

    {{-- Modals for Adding Vacancy & Adding Candidate --}}
    @can('recruitment.manage')
        <div class="modal fade" id="addVacancy" tabindex="-1" aria-labelledby="addVacancyTitle">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route('recruitment.vacancies.store') }}">
                    @csrf
                    <div class="modal-header bg-body-tertiary py-3 px-4 border-bottom">
                        <h5 class="modal-title fs-6 fw-bold text-dark d-flex align-items-center" id="addVacancyTitle">
                            <i class="fa-solid fa-briefcase me-2 text-primary"></i>Open New Vacancy Campaign
                        </h5>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold text-dark">Campaign Title <span class="text-danger">*</span></label>
                                <input class="form-control" name="title" placeholder="e.g. Senior Backend Engineer (Laravel)" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-dark">Openings <span class="text-danger">*</span></label>
                                <input class="form-control" type="number" name="openings" value="1" min="1" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Position Template</label>
                                <select class="form-select" name="position_id">
                                    <option value="">Unspecified</option>
                                    @foreach($positions as $position)
                                        <option value="{{ $position->id }}">{{ $position->title }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Branch / Workplace</label>
                                <select class="form-select" name="branch_id">
                                    <option value="">All branches</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Campaign Start Date <span class="text-danger">*</span></label>
                                <input class="form-control" type="date" name="open_date" value="{{ today()->toDateString() }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-dark">Application Deadline</label>
                                <input class="form-control" type="date" name="close_date">
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-semibold text-dark">Role Description &amp; Requirements <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="description" minlength="20" required rows="4" placeholder="Key responsibilities, required skills, and qualification expectations..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-body-tertiary py-2 px-4 border-top">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm px-3">
                            <i class="fa-solid fa-plus me-1"></i>Publish Vacancy
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @foreach($vacancies as $vacancy)
            <div class="modal fade" id="addApplicant{{ $vacancy->id }}" tabindex="-1" aria-labelledby="addApplicantTitle{{ $vacancy->id }}">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <form class="modal-content" method="POST" enctype="multipart/form-data" action="{{ route('recruitment.applicants.store', $vacancy) }}">
                        @csrf
                        <div class="modal-header bg-body-tertiary py-3 px-4 border-bottom">
                            <h5 class="modal-title fs-6 fw-bold text-dark d-flex align-items-center" id="addApplicantTitle{{ $vacancy->id }}">
                                <i class="fa-solid fa-user-plus me-2 text-primary"></i>Add Candidate · {{ $vacancy->title }}
                            </h5>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-dark">Full Name <span class="text-danger">*</span></label>
                                    <input class="form-control" name="full_name" placeholder="Candidate's full name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-dark">Email Address <span class="text-danger">*</span></label>
                                    <input class="form-control" type="email" name="email" placeholder="candidate@example.com" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-dark">Contact Phone <span class="text-danger">*</span></label>
                                    <input class="form-control" name="phone" placeholder="+855 ..." required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold text-dark">Resume / CV Document</label>
                                    <input class="form-control" type="file" name="cv" accept=".pdf,.doc,.docx">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold text-dark">Initial Screening &amp; Sourcing Notes</label>
                                    <textarea class="form-control" name="hr_note" rows="3" placeholder="Sourced from LinkedIn, salary expectations, notice period, or early impressions..."></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer bg-body-tertiary py-2 px-4 border-top">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3">
                                <i class="fa-solid fa-check me-1"></i>Enlist Candidate
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endforeach
    @endcan
</x-layouts::app>
