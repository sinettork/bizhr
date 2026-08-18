<x-layouts::app title="My Performance Reviews">
    <x-workspace-command-bar title="My Performance Appraisals" icon="fa-star-half-stroke" context="Personal Workspace" />

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
                        <th class="ps-3">Appraisal Period</th>
                        <th>Overall Score</th>
                        <th>Manager Summary</th>
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
                            <td class="ps-3 fw-medium">
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
                                <span class="small text-body-secondary">{{ \Illuminate\Support\Str::limit($review->manager_comment, 80) ?: 'No summary comment.' }}</span>
                            </td>
                            <td>
                                <span class="badge text-bg-{{ $statusBadge }}">{{ str($review->status)->replace('_', ' ')->title() }}</span>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                @if($review->status === 'hr_approved')
                                    <button class="btn btn-action-link btn-sm text-primary" type="button" data-bs-toggle="modal" data-bs-target="#ackReview{{ $review->id }}">
                                        <i class="fa-solid fa-signature"></i><span>Acknowledge</span>
                                    </button>
                                @elseif($review->status === 'employee_acknowledged' || $review->status === 'closed')
                                    <span class="small text-success"><i class="fa-solid fa-circle-check me-1"></i>Acknowledged</span>
                                @else
                                    <span class="small text-body-secondary">In progress</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-body-secondary py-5" colspan="5">
                                <i class="fa-solid fa-star-half-stroke fa-xl d-block mb-3 text-primary"></i>You have no performance reviews published yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$reviews" />
    </div>

    @foreach($reviews as $review)
        @if($review->status === 'hr_approved')
            <div class="modal fade" id="ackReview{{ $review->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('performance.my-reviews.acknowledge', $review) }}">
                        @csrf
                        <div class="modal-header">
                            <h2 class="modal-title fs-5">Acknowledge Performance Appraisal</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3 small">
                                <div class="text-body-secondary mb-1">Appraisal Period:</div>
                                <div class="fw-semibold">{{ $review->period_start->format('d M Y') }} – {{ $review->period_end->format('d M Y') }}</div>
                            </div>
                            @if($review->overall_score)
                                <div class="mb-3 small">
                                    <div class="text-body-secondary mb-1">Score:</div>
                                    <div class="badge text-bg-warning fs-6"><i class="fa-solid fa-star me-1"></i>{{ $review->overall_score }} / 5.0</div>
                                </div>
                            @endif
                            @if($review->manager_comment)
                                <div class="mb-3 p-2 bg-light rounded border small">
                                    <div class="text-body-secondary fw-semibold mb-1">Manager feedback:</div>
                                    <div>{{ $review->manager_comment }}</div>
                                </div>
                            @endif
                            <div>
                                <label class="form-label">Employee comments (optional)</label>
                                <textarea class="form-control" name="comment" rows="3" placeholder="Add your comments or notes before confirming..."></textarea>
                            </div>
                        </div>
                        <x-form-save-actions save-label="Confirm & Acknowledge" />
                    </form>
                </div>
            </div>
        @endif
    @endforeach
</x-layouts::app>
