@php
    $isManagerStage = $reviewStage === 'manager';
    $queueTitle = $isManagerStage ? 'Manager Leave Review' : 'Final HR Leave Approval';
    $queueContext = $isManagerStage ? 'Team Approvals' : 'HR & Approvals';
    $queueIntro = $isManagerStage
        ? 'Review requests from employees in your department. Approving sends the request to HR for final approval.'
        : 'These requests already passed manager review. Approving here is the final leave decision and updates leave balances.';
    $stageLabel = $isManagerStage ? 'Manager review' : 'Awaiting final HR approval';
@endphp

<x-layouts::app title="{{ $queueTitle }}">
    <x-workspace-command-bar :title="$queueTitle" icon="fa-list-check" :context="$queueContext">
        <x-slot:actions>
            <span class="status-text text-bg-warning">
                <i class="fa-solid fa-clock me-1"></i>{{ number_format($statistics['pending_review']) }} waiting review
            </span>
        </x-slot:actions>
    </x-workspace-command-bar>

    <div class="alert alert-light border d-flex align-items-start gap-2 mb-3">
        <i class="fa-solid {{ $isManagerStage ? 'fa-people-roof' : 'fa-user-shield' }} text-primary mt-1"></i>
        <div>
            <div class="fw-semibold text-dark">{{ $stageLabel }}</div>
            <div class="small text-body-secondary">{{ $queueIntro }}</div>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="row g-2 mb-3">
        <div class="col-12 col-sm-4">
            <section class="profile-card h-100 mb-0"><div class="profile-card-body py-3">
                <div class="small text-body-secondary">Waiting for you</div>
                <div class="fs-4 fw-bold text-dark mt-1">{{ number_format($statistics['pending_review']) }}</div>
                <div class="small text-body-secondary mt-1">{{ $isManagerStage ? 'Department requests' : 'Manager-approved requests' }}</div>
            </div></section>
        </div>
        <div class="col-6 col-sm-4">
            <section class="profile-card h-100 mb-0"><div class="profile-card-body py-3">
                <div class="small text-body-secondary">Final approvals today</div>
                <div class="fs-4 fw-bold text-success mt-1">{{ number_format($statistics['approved_today']) }}</div>
                <div class="small text-body-secondary mt-1">Company-wide</div>
            </div></section>
        </div>
        <div class="col-6 col-sm-4">
            <section class="profile-card h-100 mb-0"><div class="profile-card-body py-3">
                <div class="small text-body-secondary">Rejected today</div>
                <div class="fs-4 fw-bold text-danger mt-1">{{ number_format($statistics['rejected_today']) }}</div>
                <div class="small text-body-secondary mt-1">Company-wide</div>
            </div></section>
        </div>
    </div>

    <div class="reference-list" data-list-container>
        @if($requests->count())
            <div class="p-3 border-bottom bg-body-tertiary d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <div class="fw-semibold text-dark">Requests needing your decision</div>
                    <div class="small text-body-secondary">Review the employee, dates and reason before deciding.</div>
                </div>
                <span class="small text-body-secondary">Oldest requests first</span>
            </div>

            <div class="p-3 d-grid gap-3">
                @foreach($requests as $leaveRequest)
                    @php
                        $empName = $leaveRequest->employee?->full_name_km ?: $leaveRequest->employee?->full_name_en ?: $leaveRequest->employee?->employee_code;
                    @endphp
                    <article class="card shadow-none">
                        <div class="card-body p-3 p-lg-4">
                            <div class="row g-3 align-items-start">
                                <div class="col-12 col-lg-4">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="page-icon flex-shrink-0"><i class="fa-solid fa-user"></i></div>
                                        <div class="min-w-0">
                                            <div class="d-flex flex-wrap align-items-center gap-2">
                                                <h2 class="h6 mb-0 text-dark">{{ $empName }}</h2>
                                                <span class="status-text text-bg-{{ $isManagerStage ? 'warning' : 'primary' }}">{{ $stageLabel }}</span>
                                            </div>
                                            <div class="small text-body-secondary mt-1">{{ $leaveRequest->employee?->employee_code }}</div>
                                            <div class="small text-body-secondary">{{ $leaveRequest->employee?->department?->name ?? 'No department' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="text-body-secondary small mb-1">Leave</div>
                                    <div class="fw-semibold text-dark">{{ $leaveRequest->leaveType?->name }}</div>
                                    <div class="small text-body-secondary mt-1">{{ number_format($leaveRequest->total_days ?? 0, 1) }} day{{ ($leaveRequest->total_days ?? 0) == 1 ? '' : 's' }}</div>
                                </div>

                                <div class="col-12 col-md-6 col-lg-3">
                                    <div class="text-body-secondary small mb-1">Period</div>
                                    <div class="fw-semibold text-dark">{{ $leaveRequest->start_date->format('d M Y') }}</div>
                                    <div class="small text-body-secondary">to {{ $leaveRequest->end_date->format('d M Y') }}</div>
                                </div>

                                <div class="col-12 col-lg-2">
                                    <x-decision-actions
                                        class="justify-content-lg-end flex-lg-column"
                                        :approve-url="route('leave.requests.approve', $leaveRequest)"
                                        reject-target="#rejectModal{{ $leaveRequest->id }}"
                                        :approve-fields="['note' => $isManagerStage ? 'Manager approved' : 'Final HR approved']"
                                    />
                                </div>
                            </div>

                            <div class="mt-3 pt-3 border-top">
                                <div class="text-body-secondary small mb-1">Employee reason</div>
                                <div class="small text-dark">{{ $leaveRequest->reason ?: 'No reason provided.' }}</div>
                                @if(! $isManagerStage && $leaveRequest->manager)
                                    <div class="small text-body-secondary mt-2"><i class="fa-solid fa-circle-check text-success me-1"></i>Manager review completed by {{ $leaveRequest->manager->name }}</div>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <x-pagination-footer :paginator="$requests" />
        @else
            <x-empty-state
                class="py-5 px-3"
                icon="fa-circle-check"
                tone="success"
                title="Approval queue is clear"
                :message="$isManagerStage ? 'No department leave requests are waiting for manager review.' : 'No manager-approved requests are waiting for final HR review.'"
            />
        @endif
    </div>

    @if($approvedRequests->isNotEmpty())
        <section class="profile-card mt-4">
            <div class="profile-card-header">
                <div>
                    <h2 class="profile-card-title"><i class="fa-solid fa-calendar-check text-success"></i><span>Upcoming approved leave</span></h2>
                    <div class="small text-body-secondary mt-1">HR can reverse future approved leave before it starts. Balance restoration is automatic and audited.</div>
                </div>
            </div>
            <div class="profile-card-body p-0">
                <div class="list-group list-group-flush">
                    @foreach($approvedRequests as $leaveRequest)
                        @php($empName = $leaveRequest->employee?->full_name_km ?: $leaveRequest->employee?->full_name_en ?: $leaveRequest->employee?->employee_code)
                        <div class="list-group-item px-3 py-3">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                                <div>
                                    <div class="fw-semibold text-dark">{{ $empName }} <span class="status-text text-bg-success ms-1">Approved</span></div>
                                    <div class="small text-body-secondary mt-1">
                                        {{ $leaveRequest->leaveType?->name }} · {{ $leaveRequest->start_date->format('d M Y') }} – {{ $leaveRequest->end_date->format('d M Y') }} · {{ number_format($leaveRequest->total_days ?? 0, 1) }} days
                                    </div>
                                </div>
                                <button class="btn btn-outline-danger btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#cancelApprovedLeave{{ $leaveRequest->id }}">
                                    <i class="fa-solid fa-ban me-1"></i>Cancel approved leave
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @foreach($requests as $leaveRequest)
        <div class="modal fade" id="rejectModal{{ $leaveRequest->id }}" tabindex="-1">
            <div class="modal-dialog">
                <form class="modal-content" method="POST" action="{{ route('leave.requests.reject', $leaveRequest) }}">
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title fs-5">Reject Leave Request</h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-body-secondary mb-3">Please provide a clear reason for rejecting this leave request for <strong>{{ $leaveRequest->employee?->getFullName() }}</strong>.</p>
                        <label class="form-label">Rejection Reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="note" required minlength="3" rows="3" placeholder="Reason for rejection..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger" type="submit"><i class="fa-solid fa-xmark me-1"></i>Confirm rejection</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    @foreach($approvedRequests as $leaveRequest)
        <div class="modal fade" id="cancelApprovedLeave{{ $leaveRequest->id }}" tabindex="-1">
            <div class="modal-dialog">
                <form class="modal-content" method="POST" action="{{ route('leave.requests.reject', $leaveRequest) }}">
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title fs-5"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i>Cancel Approved Leave</h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning small">This reverses the approval and restores the employee leave balance. It is blocked once leave starts or when an overlapping payroll period is locked.</div>
                        <label class="form-label">Cancellation reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="note" required minlength="5" maxlength="1000" rows="3" placeholder="Explain why the approved leave is being cancelled..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Keep approved leave</button>
                        <button class="btn btn-danger" type="submit"><i class="fa-solid fa-ban me-1"></i>Cancel leave & restore balance</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</x-layouts::app>
