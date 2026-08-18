<x-layouts::app title="Recruitment">
    <x-workspace-command-bar title="Recruitment & Talent Acquisition" icon="fa-user-plus" context="Hiring Pipeline">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET">
                <div class="input-group reference-search"><span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span><input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search vacancies"></div>
                <select class="form-select reference-status" name="status"><option value="">All vacancy statuses</option>@foreach(['draft','open','closed','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->title() }}</option>@endforeach</select>
                <button class="btn btn-primary reference-search-button">Filter</button>
            </form>
        </x-slot:filters>
        <x-slot:actions>@can('recruitment.manage')<x-list-actions :add-modal="'addVacancy'" add-label="Open vacancy" />@endcan</x-slot:actions>
    </x-workspace-command-bar>

    @if(session('status'))<div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>@endif
    @if($errors->any())<div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>@endif

    <section class="mb-4">
        <div class="d-flex align-items-end justify-content-between gap-3 mb-2"><div><div class="small text-uppercase text-body-secondary fw-semibold">Open roles</div><h2 class="h6 fw-bold mb-0">Vacancy overview</h2></div><span class="small text-body-secondary">{{ $vacancies->total() }} vacancy(s)</span></div>
        <div class="row g-3">
            @forelse($vacancies as $vacancy)
                <div class="col-md-6 col-xl-4">
                    <article class="profile-card h-100 mb-0">
                        <div class="profile-card-body">
                            <div class="d-flex justify-content-between gap-3 mb-2"><div><h3 class="h6 fw-bold mb-1">{{ $vacancy->title }}</h3><div class="small text-body-secondary">{{ $vacancy->position?->title ?: 'General role' }}@if($vacancy->branch)<span class="mx-1">·</span>{{ $vacancy->branch->name }}@endif</div></div><span class="status-text text-bg-{{ $vacancy->status === 'open' ? 'success' : 'secondary' }}">{{ str($vacancy->status)->title() }}</span></div>
                            <div class="d-flex gap-4 small mt-3"><div><span class="d-block text-body-secondary">Openings</span><strong>{{ $vacancy->openings }}</strong></div><div><span class="d-block text-body-secondary">Candidates</span><strong>{{ $vacancy->applicants_count }}</strong></div><div><span class="d-block text-body-secondary">Closes</span><strong>{{ $vacancy->close_date?->format('d M') ?: 'Open' }}</strong></div></div>
                        </div>
                        @can('recruitment.manage')<div class="card-footer bg-white border-top d-flex justify-content-end"><button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#addApplicant{{ $vacancy->id }}"><i class="fa-solid fa-user-plus"></i><span>Add candidate</span></button></div>@endcan
                    </article>
                </div>
            @empty
                <div class="col-12"><div class="profile-card mb-0"><div class="profile-card-body text-center text-body-secondary py-5"><i class="fa-solid fa-briefcase fa-xl d-block mb-3 text-primary"></i>No vacancies match this view.</div></div></div>
            @endforelse
        </div>
        <div class="mt-3"><x-pagination-footer :paginator="$vacancies" /></div>
    </section>

    @php
        $pipeline = [
            'applied' => ['Applied','fa-inbox'],
            'screening' => ['Screening','fa-magnifying-glass'],
            'shortlisted' => ['Shortlisted','fa-star'],
            'interview' => ['Interview','fa-comments'],
            'offer' => ['Offer','fa-file-signature'],
            'hired' => ['Hired','fa-user-check'],
        ];
        $candidateGroup = function($status) {
            return match($status) {
                'offer_pending','offered','accepted' => 'offer',
                'rejected','declined' => null,
                default => $status,
            };
        };
        $groupedApplicants = $applicants->groupBy(fn($a) => $candidateGroup($a->status));
        $nextStages = [
            'applied' => ['screening','rejected'],
            'screening' => ['shortlisted','rejected'],
            'shortlisted' => ['interview','rejected'],
            'interview' => ['offer_pending','rejected'],
            'offer_pending' => ['offered','rejected'],
            'offered' => ['accepted','declined'],
            'accepted' => ['hired'],
        ];
    @endphp

    <section>
        <div class="d-flex align-items-end justify-content-between gap-3 mb-2"><div><div class="small text-uppercase text-body-secondary fw-semibold">Candidate journey</div><h2 class="h6 fw-bold mb-0">Hiring pipeline</h2></div><span class="small text-body-secondary">{{ $applicants->count() }} active/recent candidate(s)</span></div>
        <div class="d-flex gap-3 overflow-auto pb-2" data-list-container style="align-items:stretch;">
            @foreach($pipeline as $key => [$label,$icon])
                @php($rows = $groupedApplicants->get($key, collect()))
                <div style="min-width:280px;width:280px;">
                    <div class="profile-card h-100 mb-0">
                        <div class="profile-card-header"><h3 class="profile-card-title"><i class="fa-solid {{ $icon }} text-primary"></i><span>{{ $label }}</span></h3><span class="small fw-semibold text-body-secondary">{{ $rows->count() }}</span></div>
                        <div class="profile-card-body bg-body-tertiary p-2 d-grid gap-2 align-content-start" style="min-height:420px;">
                            @forelse($rows as $applicant)
                                <article class="card border-0 shadow-sm"><div class="card-body p-3">
                                    <div class="fw-semibold text-dark">{{ $applicant->full_name }}</div><div class="small text-body-secondary mb-2">{{ $applicant->vacancy->title }}</div>
                                    <div class="small text-body-secondary mb-2"><i class="fa-regular fa-envelope me-1"></i>{{ $applicant->email }}</div>
                                    @if($applicant->hr_note)<p class="small text-body-secondary mb-2"><i class="fa-regular fa-note-sticky me-1"></i>{{ \Illuminate\Support\Str::limit($applicant->hr_note,70) }}</p>@endif
                                    <div class="d-flex align-items-center justify-content-between gap-2 mt-3">
                                        <span class="small text-body-secondary">{{ $applicant->applied_at?->format('d M') ?? '—' }}</span>
                                        <div class="dropdown"><button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="dropdown"><i class="fa-solid fa-ellipsis"></i></button><ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            @if($applicant->cv_path)<li><a class="dropdown-item" href="{{ route('recruitment.applicants.cv',$applicant) }}" target="_blank"><i class="fa-solid fa-file-arrow-down me-2"></i>Open CV</a></li>@endif
                                            @can('recruitment.manage')@foreach($nextStages[$applicant->status] ?? [] as $stage)<li><form method="POST" action="{{ route('recruitment.applicants.transition',$applicant) }}">@csrf<input type="hidden" name="status" value="{{ $stage }}"><button class="dropdown-item {{ in_array($stage,['rejected','declined']) ? 'text-danger' : '' }}" type="submit">Move to {{ str($stage)->replace('_',' ')->title() }}</button></form></li>@endforeach@endcan
                                        </ul></div>
                                    </div>
                                </div></article>
                            @empty<div class="text-center text-body-secondary small py-5">No candidates.</div>@endforelse
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @if(($groupedApplicants->get(null, collect()))->isNotEmpty())<div class="small text-body-secondary mt-2">Rejected/declined candidates are retained in history but omitted from the active pipeline.</div>@endif
    </section>

    @can('recruitment.manage')
        <div class="modal fade" id="addVacancy" tabindex="-1"><div class="modal-dialog modal-lg"><form class="modal-content" method="POST" action="{{ route('recruitment.vacancies.store') }}">@csrf
            <div class="modal-header"><h2 class="modal-title fs-5"><i class="fa-solid fa-briefcase me-2 text-primary"></i>Open new vacancy</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><div class="row g-3">
                <div class="col-md-8"><label class="form-label">Title <span class="text-danger">*</span></label><input class="form-control" name="title" required></div>
                <div class="col-md-4"><label class="form-label">Openings <span class="text-danger">*</span></label><input class="form-control" type="number" name="openings" value="1" min="1" required></div>
                <div class="col-md-6"><label class="form-label">Position</label><select class="form-select" name="position_id"><option value="">Unspecified</option>@foreach($positions as $position)<option value="{{ $position->id }}">{{ $position->title }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label">Branch</label><select class="form-select" name="branch_id"><option value="">All branches</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
                <div class="col-md-4"><label class="form-label">Open date</label><input class="form-control" type="date" name="open_date" value="{{ today()->toDateString() }}" required></div>
                <div class="col-md-4"><label class="form-label">Close date</label><input class="form-control" type="date" name="close_date"></div>
                <div class="col-12"><label class="form-label">Description <span class="text-danger">*</span></label><textarea class="form-control" name="description" minlength="20" required rows="4"></textarea></div>
            </div></div><x-form-save-actions save-label="Open vacancy" />
        </form></div></div>

        @foreach($vacancies as $vacancy)
            <div class="modal fade" id="addApplicant{{ $vacancy->id }}" tabindex="-1"><div class="modal-dialog modal-lg"><form class="modal-content" method="POST" enctype="multipart/form-data" action="{{ route('recruitment.applicants.store',$vacancy) }}">@csrf
                <div class="modal-header"><h2 class="modal-title fs-5"><i class="fa-solid fa-user-plus me-2 text-primary"></i>Add candidate · {{ $vacancy->title }}</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                <div class="modal-body"><div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Full name <span class="text-danger">*</span></label><input class="form-control" name="full_name" required></div>
                    <div class="col-md-6"><label class="form-label">Email <span class="text-danger">*</span></label><input class="form-control" type="email" name="email" required></div>
                    <div class="col-md-6"><label class="form-label">Phone <span class="text-danger">*</span></label><input class="form-control" name="phone" required></div>
                    <div class="col-md-6"><label class="form-label">CV</label><input class="form-control" type="file" name="cv" accept=".pdf,.doc,.docx"></div>
                    <div class="col-12"><label class="form-label">HR note</label><textarea class="form-control" name="hr_note" rows="3"></textarea></div>
                </div></div><x-form-save-actions save-label="Add candidate" />
            </form></div></div>
        @endforeach
    @endcan
</x-layouts::app>
