@php
    $isManagerStage = $reviewStage === 'manager';
    $queueTitle = $isManagerStage ? 'Team Leave Review & Approvals' : 'HR Final Leave Approval';
    $queueContext = $isManagerStage ? 'Department Team Operations' : 'People & Payroll Operations';
    $queueIntro = $isManagerStage
        ? 'Review department requests. Manager approval validates team coverage and forwards the request to HR for final sign-off.'
        : 'These requests have passed departmental manager review. HR approval commits the deduction to the employee leave quota and payroll schedule.';
    $stageLabel = $isManagerStage ? 'Manager review' : 'Final HR review';
@endphp

<x-layouts::app title="{{ $queueTitle }}">
    <x-workspace-command-bar :title="$queueTitle" icon="fa-calendar-check" :context="$queueContext">
        <x-slot:actions>
            <div class="d-flex align-items-center gap-2">
                @if($statistics['pending_review'] > 0)
                    <span class="badge text-bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 fs-6">
                        <i class="fa-solid fa-hourglass-half me-1"></i>{{ number_format($statistics['pending_review']) }} waiting review
                    </span>
                @else
                    <span class="badge text-bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-6">
                        <i class="fa-solid fa-check-double me-1"></i>Queue clear
                    </span>
                @endif
                <a href="{{ route('leave.balances.index') }}" class="btn btn-action-link btn-sm">
                    <i class="fa-solid fa-chart-pie me-1"></i><span>Balances</span>
                </a>
            </div>
        </x-slot:actions>
    </x-workspace-command-bar>

    @if (session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger mb-3"><ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    {{-- Review Queue Triage Metrics (Archetype A) --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-body p-3">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="small text-body-secondary fw-semibold text-uppercase">Action Required</div>
                            <div class="fs-4 fw-bold {{ $statistics['pending_review'] > 0 ? 'text-warning' : 'text-dark' }} mt-1">
                                {{ number_format($statistics['pending_review']) }} <span class="fs-6 fw-normal text-body-secondary">request(s)</span>
                            </div>
                        </div>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 {{ $statistics['pending_review'] > 0 ? 'bg-warning-subtle text-warning' : 'bg-body-tertiary text-secondary' }}" style="width:36px;height:36px">
                            <i class="fa-solid fa-clipboard-question"></i>
                        </span>
                    </div>
                    <div class="small text-body-secondary mt-2">Awaiting your approval decision</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-body p-3">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="small text-body-secondary fw-semibold text-uppercase">Approved Today</div>
                            <div class="fs-4 fw-bold text-success mt-1">{{ number_format($statistics['approved_today']) }}</div>
                        </div>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-success-subtle text-success" style="width:36px;height:36px">
                            <i class="fa-solid fa-circle-check"></i>
                        </span>
                    </div>
                    <div class="small text-body-secondary mt-2">Signed off and processed</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-body p-3">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="small text-body-secondary fw-semibold text-uppercase">Rejected / Cancelled</div>
                            <div class="fs-4 fw-bold text-danger mt-1">{{ number_format($statistics['rejected_today']) }}</div>
                        </div>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-danger-subtle text-danger" style="width:36px;height:36px">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </span>
                    </div>
                    <div class="small text-body-secondary mt-2">Declined with recorded reason</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-body p-3">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="small text-body-secondary fw-semibold text-uppercase">Upcoming Outages</div>
                            <div class="fs-4 fw-bold text-info mt-1">{{ $approvedRequests->count() }} <span class="fs-6 fw-normal text-body-secondary">scheduled</span></div>
                        </div>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-info-subtle text-info" style="width:36px;height:36px">
                            <i class="fa-solid fa-user-clock"></i>
                        </span>
                    </div>
                    <div class="small text-body-secondary mt-2">Employees on approved future leave</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Main Triage Request List --}}
    <div class="reference-list mb-4" data-list-container>
        <div class="reference-list-toolbar bg-body-tertiary">
            <div>
                <div class="fw-semibold text-dark">{{ $stageLabel }} Queue</div>
                <div class="small text-body-secondary">{{ $queueIntro }}</div>
            </div>
            <span class="small text-body-secondary">Sorted by submission order (oldest first)</span>
        </div>

        @forelse($requests as $leaveRequest)
            @php
                $empName = $leaveRequest->employee?->full_name_km ?: $leaveRequest->employee?->full_name_en ?: $leaveRequest->employee?->employee_code;
                $empDeptId = $leaveRequest->employee?->department_id;
                
                // Department coverage clash detection against approved upcoming requests
                $clashingRequests = $approvedRequests->filter(function($approved) use ($leaveRequest, $empDeptId) {
                    return $approved->id !== $leaveRequest->id
                        && $approved->employee?->department_id === $empDeptId
                        && ($leaveRequest->start_date <= $approved->end_date && $leaveRequest->end_date >= $approved->start_date);
                });
            @endphp
            <div class="p-3 border-bottom {{ $clashingRequests->isNotEmpty() ? 'bg-warning-subtle bg-opacity-25' : '' }}">
                <div class="row g-3 align-items-center">
                    {{-- Employee & Justification Column --}}
                    <div class="col-12 col-lg-4">
                        <div class="d-flex align-items-start gap-2">
                            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary fw-bold" style="width:36px;height:36px;font-size:.85rem">
                                {{ strtoupper(substr($empName, 0, 2)) }}
                            </span>
                            <div class="min-w-0">
                                <div class="fw-bold text-dark">{{ $empName }}</div>
                                <div class="small text-body-secondary">
                                    <span class="font-monospace">{{ $leaveRequest->employee?->employee_code }}</span>
                                    @if($leaveRequest->employee?->department?->name)
                                        · <span class="fw-medium text-dark">{{ $leaveRequest->employee->department->name }}</span>
                                    @endif
                                </div>
                                <div class="small text-body-secondary mt-1 p-2 bg-body-tertiary rounded border">
                                    <i class="fa-solid fa-quote-left text-body-tertiary me-1"></i>{{ $leaveRequest->reason ?: 'No justification note specified.' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Leave Type & Duration --}}
                    <div class="col-6 col-lg-2">
                        <div class="small text-body-secondary fw-semibold text-uppercase">Leave Type</div>
                        <div class="fw-bold text-dark mt-1">{{ $leaveRequest->leaveType?->name }}</div>
                        <div class="badge text-bg-light border text-dark mt-1 font-monospace">
                            {{ number_format($leaveRequest->total_days ?? 0, 1) }} day{{ ($leaveRequest->total_days ?? 0) == 1 ? '' : 's' }}
                        </div>
                    </div>

                    {{-- Dates & Team Coverage Alert --}}
                    <div class="col-6 col-lg-3">
                        <div class="small text-body-secondary fw-semibold text-uppercase">Dates Requested</div>
                        <div class="fw-semibold text-dark mt-1">
                            <i class="fa-regular fa-calendar text-primary me-1"></i>{{ $leaveRequest->start_date->format('d M Y') }} – {{ $leaveRequest->end_date->format('d M Y') }}
                        </div>
                        @if($clashingRequests->isNotEmpty())
                            <div class="badge text-bg-warning text-dark border border-warning-subtle small mt-1 text-wrap text-start">
                                <i class="fa-solid fa-triangle-exclamation me-1"></i>Team clash: {{ $clashingRequests->first()->employee?->getFullName() }} also away
                            </div>
                        @else
                            <div class="small text-success mt-1">
                                <i class="fa-solid fa-circle-check me-1"></i>No team coverage conflicts
                            </div>
                        @endif
                    </div>

                    {{-- Decision Actions --}}
                    <div class="col-12 col-lg-3">
                        <div class="d-flex align-items-center justify-content-lg-end gap-2">
                            <button type="button" class="btn btn-outline-danger btn-sm px-3" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $leaveRequest->id }}">
                                <i class="fa-solid fa-xmark me-1"></i>Reject
                            </button>
                            <form method="POST" action="{{ route('leave.requests.approve', $leaveRequest) }}">
                                @csrf
                                <input type="hidden" name="note" value="{{ $isManagerStage ? 'Manager approved - coverage verified' : 'Final HR approved' }}">
                                <button type="submit" class="btn btn-primary btn-sm px-3" data-confirm="{{ $isManagerStage ? 'Approve this leave request and forward to HR?' : 'Grant final HR approval and deduct leave quota?' }}">
                                    <i class="fa-solid fa-check me-1"></i>{{ $isManagerStage ? 'Approve & forward' : 'Final approve' }}
                                </button>
                            </form>
                        </div>
                        @if(! $isManagerStage && $leaveRequest->manager)
                            <div class="small text-body-secondary text-lg-end mt-1">
                                <i class="fa-solid fa-check-double text-success me-1"></i>Mgr: {{ $leaveRequest->manager->name }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <x-empty-state
                class="py-5 px-3"
                icon="fa-circle-check"
                tone="success"
                title="Approval queue is completely clear"
                :message="$isManagerStage ? 'All department leave requests have been reviewed and forwarded.' : 'No manager-approved requests are waiting for final HR review.'"
            />
        @endforelse

        @if($requests->count())
            <x-pagination-footer :paginator="$requests" />
        @endif
    </div>

    {{-- Upcoming Scheduled Team Absences (Contextual Awareness) --}}
    @if($approvedRequests->isNotEmpty())
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mt-4 mb-2">
            <div>
                <div class="small text-uppercase text-body-secondary fw-semibold">Team Capacity &amp; Outage Context</div>
                <h2 class="h6 fw-bold mb-0">Upcoming Approved Employee Absences</h2>
            </div>
            <div class="small text-body-secondary">Staff coverage overview for future planning</div>
        </div>

        <div class="reference-list mb-4">
            @foreach($approvedRequests as $leaveRequest)
                @php($empName = $leaveRequest->employee?->full_name_km ?: $leaveRequest->employee?->full_name_en ?: $leaveRequest->employee?->employee_code)
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 px-3 py-3 border-bottom">
                    <div>
                        <div class="fw-semibold text-dark">
                            {{ $empName }}
                            <span class="badge text-bg-success-subtle text-success border border-success-subtle ms-1">Approved Outage</span>
                        </div>
                        <div class="small text-body-secondary mt-1">
                            <strong>{{ $leaveRequest->leaveType?->name }}</strong> · {{ $leaveRequest->start_date->format('d M Y') }} – {{ $leaveRequest->end_date->format('d M Y') }} ({{ number_format($leaveRequest->total_days ?? 0, 1) }} days)
                            @if($leaveRequest->employee?->department?->name)
                                · <span class="text-dark">{{ $leaveRequest->employee->department->name }}</span>
                            @endif
                        </div>
                    </div>
                    <button class="btn btn-action-link btn-sm text-danger" type="button" data-bs-toggle="modal" data-bs-target="#cancelApprovedLeave{{ $leaveRequest->id }}">
                        <i class="fa-solid fa-ban"></i><span>Cancel approved leave</span>
                    </button>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Rejection Modals --}}
    @foreach($requests as $leaveRequest)
        <div class="modal fade" id="rejectModal{{ $leaveRequest->id }}" tabindex="-1" aria-labelledby="rejectTitle{{ $leaveRequest->id }}">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route('leave.requests.reject', $leaveRequest) }}">
                    @csrf
                    <div class="modal-header bg-body-tertiary py-3 px-4 border-bottom">
                        <h5 class="modal-title fs-6 fw-bold text-dark d-flex align-items-center" id="rejectTitle{{ $leaveRequest->id }}">
                            <i class="fa-solid fa-circle-xmark text-danger me-2"></i>Reject Leave Request
                        </h5>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="small text-body-secondary mb-3">Provide a clear, respectful reason for rejecting the request for <strong>{{ $leaveRequest->employee?->getFullName() }}</strong>.</p>
                        <label class="form-label small fw-semibold text-dark">Rejection reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="note" required minlength="3" maxlength="1000" rows="3" placeholder="Explain the rationale (e.g. Understaffed during peak shift, insufficient leave balance)..."></textarea>
                    </div>
                    <div class="modal-footer bg-body-tertiary py-2 px-4 border-top">
                        <button class="btn btn-secondary btn-sm" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger btn-sm px-3" type="submit"><i class="fa-solid fa-xmark me-1"></i>Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

    {{-- Cancel Approved Leave Modals --}}
    @foreach($approvedRequests as $leaveRequest)
        <div class="modal fade" id="cancelApprovedLeave{{ $leaveRequest->id }}" tabindex="-1" aria-labelledby="cancelTitle{{ $leaveRequest->id }}">
            <div class="modal-dialog modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route('leave.requests.reject', $leaveRequest) }}">
                    @csrf
                    <div class="modal-header bg-body-tertiary py-3 px-4 border-bottom">
                        <h5 class="modal-title fs-6 fw-bold text-dark d-flex align-items-center" id="cancelTitle{{ $leaveRequest->id }}">
                            <i class="fa-solid fa-ban text-danger me-2"></i>Cancel Approved Leave
                        </h5>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="small text-body-secondary mb-3">Cancelling this request restores the deducted balance to <strong>{{ $leaveRequest->employee?->getFullName() }}</strong>. Cancellation is blocked if the leave period is already in the past or locked by payroll.</p>
                        <label class="form-label small fw-semibold text-dark">Cancellation reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="note" required minlength="5" maxlength="1000" rows="3" placeholder="Explain why this approved leave is being revoked..."></textarea>
                    </div>
                    <div class="modal-footer bg-body-tertiary py-2 px-4 border-top">
                        <button class="btn btn-secondary btn-sm" type="button" data-bs-dismiss="modal">Keep approved leave</button>
                        <button class="btn btn-danger btn-sm px-3" type="submit"><i class="fa-solid fa-ban me-1"></i>Revoke &amp; Restore</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</x-layouts::app>
