<x-layouts::app title="My Training & Courses">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div><div class="small text-uppercase text-body-secondary fw-semibold">My workspace / Learning</div><h1 class="h5 mb-0 fw-bold text-dark">My training plan</h1></div>
        <a class="btn btn-light btn-sm" href="{{ route('dashboard') }}"><i class="fa-solid fa-arrow-left me-1"></i>Workspace</a>
    </div>

    @if(session('status'))<div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>@endif
    @if($errors->any())<div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>@endif

    @php
        $items = collect($enrollments->items());
        $completed = $items->where('status','completed')->count();
        $active = $items->whereNotIn('status',['completed','cancelled'])->count();
        $dueSoon = $items->filter(fn($item) => $item->due_date && $item->status !== 'completed' && $item->due_date->isBetween(today(), today()->addDays(7)))->count();
    @endphp

    <div class="row g-3 mb-3">
        <div class="col-sm-4"><div class="profile-card mb-0"><div class="profile-card-body d-flex align-items-center gap-3"><span class="metric-icon bg-primary-subtle text-primary"><i class="fa-solid fa-book-open"></i></span><div><div class="small text-body-secondary">In progress</div><div class="fs-4 fw-bold">{{ $active }}</div></div></div></div></div>
        <div class="col-sm-4"><div class="profile-card mb-0"><div class="profile-card-body d-flex align-items-center gap-3"><span class="metric-icon bg-warning-subtle text-warning"><i class="fa-regular fa-clock"></i></span><div><div class="small text-body-secondary">Due in 7 days</div><div class="fs-4 fw-bold">{{ $dueSoon }}</div></div></div></div></div>
        <div class="col-sm-4"><div class="profile-card mb-0"><div class="profile-card-body d-flex align-items-center gap-3"><span class="metric-icon bg-success-subtle text-success"><i class="fa-solid fa-check"></i></span><div><div class="small text-body-secondary">Completed</div><div class="fs-4 fw-bold">{{ $completed }}</div></div></div></div></div>
    </div>

    <div class="row g-3" data-list-container>
        @forelse($enrollments as $enrollment)
            @php($isCompleted = $enrollment->status === 'completed')
            <div class="col-md-6 col-xl-4">
                <article class="profile-card h-100 mb-0 d-flex flex-column">
                    <div class="profile-card-body flex-grow-1">
                        <div class="d-flex align-items-start justify-content-between gap-3 mb-3"><span class="metric-icon {{ $isCompleted ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }}"><i class="fa-solid {{ $isCompleted ? 'fa-award' : 'fa-book-open-reader' }}"></i></span><span class="status-text text-bg-{{ $isCompleted ? 'success' : 'primary' }}">{{ str($enrollment->status)->replace('_',' ')->title() }}</span></div>
                        <h2 class="h6 fw-bold mb-2">{{ $enrollment->course?->title }}</h2>
                        @if($enrollment->course?->description)<p class="small text-body-secondary">{{ \Illuminate\Support\Str::limit($enrollment->course->description,110) }}</p>@endif
                        <div class="d-flex justify-content-between small mb-2"><span class="text-body-secondary">Progress</span><strong>{{ $enrollment->progress }}%</strong></div><div class="progress mb-3" style="height:6px"><div class="progress-bar {{ $isCompleted ? 'bg-success' : '' }}" style="width:{{ max(0,min(100,$enrollment->progress)) }}%"></div></div>
                        <div class="profile-kv-row"><span class="profile-kv-label">Deadline</span><span class="profile-kv-value {{ $enrollment->due_date && !$isCompleted && $enrollment->due_date->isPast() ? 'text-danger' : '' }}">{{ $enrollment->due_date?->format('d M Y') ?? 'Flexible' }}</span></div>
                        <div class="profile-kv-row"><span class="profile-kv-label">Score</span><span class="profile-kv-value">{{ $enrollment->score !== null ? number_format($enrollment->score,1).'%' : '—' }}</span></div>
                    </div>
                    <div class="card-footer bg-white d-flex justify-content-end">@if(!$isCompleted)<button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#updateCourse{{ $enrollment->id }}"><i class="fa-solid fa-arrow-trend-up me-1"></i>Update progress</button>@else<span class="small text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>Learning completed</span>@endif</div>
                </article>
            </div>
        @empty
            <div class="col-12"><div class="profile-card"><div class="profile-card-body text-center text-body-secondary py-5"><i class="fa-solid fa-graduation-cap fa-xl d-block mb-3 text-primary"></i><h2 class="h6 fw-bold text-dark">No training assigned</h2><p class="small mb-0">Assigned learning programs will appear here.</p></div></div></div>
        @endforelse
        <div class="col-12"><x-pagination-footer :paginator="$enrollments" /></div>
    </div>

    @foreach($enrollments as $enrollment)
        @if($enrollment->status !== 'completed')
            <div class="modal fade" id="updateCourse{{ $enrollment->id }}" tabindex="-1"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('training.progress',$enrollment) }}">@csrf<div class="modal-header"><h2 class="modal-title fs-5">Update progress · {{ $enrollment->course?->title }}</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="mb-3"><label class="form-label">Progress <span class="text-danger">*</span></label><div class="input-group"><input class="form-control" type="number" min="0" max="100" name="progress" value="{{ $enrollment->progress }}" required><span class="input-group-text">%</span></div></div><div><label class="form-label">Assessment score</label><input class="form-control" type="number" step=".01" min="0" max="100" name="score" value="{{ $enrollment->score }}" placeholder="Optional"></div></div><x-form-save-actions save-label="Save progress" /></form></div></div>
        @endif
    @endforeach
</x-layouts::app>
