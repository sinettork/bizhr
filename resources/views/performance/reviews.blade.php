<x-layouts::app title="Performance Reviews">
    <x-workspace-command-bar title="Employee Performance Reviews" icon="fa-star" context="Performance & Appraisals">
        <x-slot:actions>
            <x-list-actions :add-modal="auth()->user()->can('performance.create') ? 'addReview' : null" add-label="Create review cycle" />
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
                        <th class="ps-3">Employee</th>
                        <th>Reviewer</th>
                        <th>Appraisal Period</th>
                        <th>Score</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                        @php
                            $statusBadge = match($review->status) {
                                'closed' => 'success',
                                'hr_approved' => 'primary',
                                'employee_acknowledged' => 'info',
                                'manager_submitted' => 'warning',
                                default => 'secondary',
                            };
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="fw-medium text-dark">{{ $review->employee?->getFullName() }}</div>
                                <small class="text-body-secondary">{{ $review->employee?->department?->name ?? '—' }}</small>
                            </td>
                            <td>
                                <div>{{ $review->reviewer?->name ?? 'Unassigned' }}</div>
                                <small class="text-body-secondary">v{{ $review->version }}</small>
                            </td>
                            <td>
                                {{ $review->period_start->format('d M Y') }} – {{ $review->period_end->format('d M Y') }}
                            </td>
                            <td>
                                @if($review->overall_score)
                                    <span class="badge text-bg-warning"><i class="fa-solid fa-star me-1"></i>{{ $review->overall_score }} / 5.0</span>
                                @else
                                    <span class="text-body-secondary small">Pending scoring</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge text-bg-{{ $statusBadge }}">{{ str($review->status)->replace('_',' ')->title() }}</span>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                @if($review->status === 'draft' && $review->reviewer_id === auth()->id())
                                    @can('performance.review')
                                        <button class="btn btn-action-link btn-sm" data-bs-toggle="modal" data-bs-target="#submitReview{{ $review->id }}">
                                            <i class="fa-solid fa-pen-to-square"></i><span>Score & submit</span>
                                        </button>
                                    @endcan
                                @elseif($review->status === 'manager_submitted')
                                    @can('performance.approve')
                                        <form class="d-inline" method="POST" action="{{ route('performance.reviews.transition', [$review, 'approve']) }}">
                                            @csrf
                                            <button class="btn btn-action-link btn-sm text-success" type="submit">
                                                <i class="fa-solid fa-check"></i><span>HR approve</span>
                                            </button>
                                        </form>
                                    @endcan
                                @elseif($review->status === 'employee_acknowledged')
                                    @can('performance.approve')
                                        <form class="d-inline" method="POST" action="{{ route('performance.reviews.transition', [$review, 'close']) }}">
                                            @csrf
                                            <button class="btn btn-action-link btn-sm" type="submit">
                                                <i class="fa-solid fa-lock"></i><span>Close review</span>
                                            </button>
                                        </form>
                                    @endcan
                                @endif

                                @can('performance.reopen')
                                    @if(in_array($review->status, ['manager_submitted','hr_approved','employee_acknowledged','closed'], true))
                                        <button class="btn btn-action-link btn-sm text-secondary" data-bs-toggle="modal" data-bs-target="#reopenReview{{ $review->id }}">
                                            <i class="fa-solid fa-lock-open"></i><span>Reopen</span>
                                        </button>
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-body-secondary py-5" colspan="6">
                                <i class="fa-solid fa-star fa-xl d-block mb-3 text-primary"></i>No performance reviews found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$reviews" />
    </div>

    @can('performance.review')
        @foreach($reviews as $review)
            @if($review->status === 'draft' && $review->reviewer_id === auth()->id())
                <div class="modal fade" id="submitReview{{ $review->id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <form class="modal-content" method="POST" action="{{ route('performance.reviews.submit', $review) }}">
                            @csrf
                            <div class="modal-header">
                                <h2 class="modal-title fs-5"><i class="fa-solid fa-star me-2 text-primary"></i>Score performance · {{ $review->employee?->getFullName() }}</h2>
                                <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                @foreach($review->scores as $criterion)
                                    <div class="row g-2 border-bottom pb-3 mb-3">
                                        <div class="col-md-5">
                                            <div class="fw-semibold">{{ $criterion->criterion_name }}</div>
                                            <small class="text-body-secondary">Target: {{ $criterion->target_value }} {{ $criterion->measurement_unit }} · Weight {{ $criterion->weight }}%</small>
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Score (1–5) <span class="text-danger">*</span></label>
                                            <input class="form-control" type="number" min="1" max="5" name="scores[{{ $criterion->id }}]" required>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label">Manager comment</label>
                                            <input class="form-control" name="comments[{{ $criterion->id }}]" placeholder="Required if score is 1–2">
                                        </div>
                                    </div>
                                @endforeach
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Key Strengths & Wins</label>
                                        <textarea class="form-control" name="strengths" rows="2" placeholder="Highlight notable achievements..."></textarea>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Areas for Improvement</label>
                                        <textarea class="form-control" name="areas_for_improvement" rows="2" placeholder="Development goals..."></textarea>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Overall Manager Summary</label>
                                        <textarea class="form-control" name="manager_comment" rows="2" placeholder="Overall appraisal summary..."></textarea>
                                    </div>
                                </div>
                            </div>
                            <x-form-save-actions save-label="Submit for HR approval" />
                        </form>
                    </div>
                </div>
            @endif
        @endforeach
    @endcan

    @can('performance.reopen')
        @foreach($reviews as $review)
            @if(in_array($review->status, ['manager_submitted','hr_approved','employee_acknowledged','closed'], true))
                <div class="modal fade" id="reopenReview{{ $review->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form class="modal-content" method="POST" action="{{ route('performance.reviews.transition', [$review, 'reopen']) }}">
                            @csrf
                            <div class="modal-header">
                                <h2 class="modal-title fs-5"><i class="fa-solid fa-lock-open me-2 text-warning"></i>Reopen performance review</h2>
                                <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <label class="form-label">Reopen Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="reason" minlength="15" required placeholder="Specify reason for reopening this closed review..."></textarea>
                                <small class="text-body-secondary d-block mt-1">A detailed reason is retained with the review version history.</small>
                            </div>
                            <x-form-save-actions save-label="Reopen review" />
                        </form>
                    </div>
                </div>
            @endif
        @endforeach
    @endcan

    @can('performance.create')
        <div class="modal fade" id="addReview" tabindex="-1">
            <div class="modal-dialog">
                <form class="modal-content" method="POST" action="{{ route('performance.reviews.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title fs-5"><i class="fa-solid fa-star me-2 text-primary"></i>Initiate review cycle</h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <label class="form-label">Employee <span class="text-danger">*</span></label>
                        <select class="form-select mb-3" name="employee_id" required>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->getFullName() }}</option>
                            @endforeach
                        </select>
                        <label class="form-label">Period start date <span class="text-danger">*</span></label>
                        <input class="form-control mb-3" type="date" name="period_start" required value="{{ today()->startOfQuarter()->toDateString() }}">
                        <label class="form-label">Period end date <span class="text-danger">*</span></label>
                        <input class="form-control" type="date" name="period_end" required value="{{ today()->endOfQuarter()->toDateString() }}">
                    </div>
                    <x-form-save-actions save-label="Create review" />
                </form>
            </div>
        </div>
    @endcan
</x-layouts::app>
