@php
    $isManagerStage = $reviewStage === 'manager';
    $queueTitle = $isManagerStage ? 'Manager Leave Review' : 'Final HR Leave Approval';
    $queueContext = $isManagerStage ? 'Team Approvals' : 'HR & Approvals';
    $queueIntro = $isManagerStage
        ? 'Review department requests. Approval sends the request to HR for the final decision.'
        : 'These requests already passed manager review. Approval here is final and updates leave balances.';
    $stageLabel = $isManagerStage ? 'Manager review' : 'Final HR review';
@endphp

<x-layouts::app title="{{ $queueTitle }}">
    <x-workspace-command-bar :title="$queueTitle" icon="fa-list-check" :context="$queueContext">
        <x-slot:actions>
            <span class="status-text text-bg-warning">{{ number_format($statistics['pending_review']) }} waiting review</span>
        </x-slot:actions>
    </x-workspace-command-bar>

    @if (session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="workspace-summary" aria-label="Leave approval summary">
        <div class="workspace-summary-item">
            <div class="label">Waiting for you</div>
            <div class="value">{{ number_format($statistics['pending_review']) }}</div>
        </div>
        <div class="workspace-summary-item">
            <div class="label">Final approvals today</div>
            <div class="value">{{ number_format($statistics['approved_today']) }}</div>
        </div>
        <div class="workspace-summary-item">
            <div class="label">Rejected today</div>
            <div class="value">{{ number_format($statistics['rejected_today']) }}</div>
        </div>
    </div>

    <div class="reference-list" data-list-container>
        <div class="reference-list-toolbar">
            <div>
                <div class="fw-semibold text-dark">{{ $stageLabel }}</div>
                <div class="small text-body-secondary">{{ $queueIntro }}</div>
            </div>
            <span class="small text-body-secondary">Oldest requests first</span>
        </div>

        @forelse($requests as $leaveRequest)
            @php
                $empName = $leaveRequest->employee?->full_name_km ?: $leaveRequest->employee?->full_name_en ?: $leaveRequest->employee?->employee_code;
            @endphp
            <div class="px-3 py-3 border-bottom">
                <div class="row g-3 align-items-center">
                    <div class="col-12 col-lg-4">
                        <div class="fw-semibold text-dark">{{ $empName }}</div>
                        <div class="small text-body-secondary mt-1">
                            {{ $leaveRequest->employee?->employee_code }}
                            @if($leaveRequest->employee?->department?->name)
                                · {{ $leaveRequest->employee->department->name }}
                            @endif
                        </div>
                        <div class="small text-body-secondary mt-1">{{ $leaveRequest->reason ?: 'No reason provided.' }}</div>
                    </div>

                    <div class="col-6 col-lg-2">
                        <div class="small text-body-secondary">Leave type</div>
                        <div class="fw-semibold text-dark">{{ $leaveRequest->leaveType?->name }}</div>
                        <div class="small text-body-secondary">{{ number_format($leaveRequest->total_days ?? 0, 1) }} day{{ ($leaveRequest->total_days ?? 0) == 1 ? '' : 's' }}</div>
                    </div>

                    <div class="col-6 col-lg-2">
                        <div class="small text-body-secondary">Period</div>
                        <div class="fw-semibold text-dark">{{ $leaveRequest->start_date->format('d M Y') }}</div>
                        <div class="small text-body-secondary">to {{ $leaveRequest->end_date->format('d M Y') }}</div>
                    </div>

                    <div class="col-12 col-lg-2">
                        <span class="status-text text-bg-{{ $isManagerStage ? 'warning' : 'primary' }}">{{ $stageLabel }}</span>
                        @if(! $isManagerStage && $leaveRequest->manager)
                            <div class="small text-body-secondary mt-1">Manager: {{ $leaveRequest->manager->name }}</div>
                        @endif
                    </div>

                    <div class="col-12 col-lg-2">
                        <x-decision-actions
                            class="justify-content-lg-end"
                            :approve-url="route('leave.requests.approve', $leaveRequest)"
                            reject-target="#rejectModal{{ $leaveRequest->id }}"
                            :approve-fields="['note' => $isManagerStage ? 'Manager approved' : 'Final HR approved']"
                            :approve-confirm="$isManagerStage ? 'Approve this leave request and send it to HR for final review?' : 'Give final approval to this leave request and update the employee leave balance?'"
                            :approve-confirm-title="$isManagerStage ? 'Confirm manager approval' : 'Confirm final HR approval'"
                        />
                    </div>
                </div>
            </div>
        @empty
            <x-empty-state
                class="py-5 px-3"
                icon="fa-circle-check"
                tone="success"
                title="Approval queue is clear"
                :message="$isManagerStage ? 'No department leave requests are waiting for manager review.' : 'No manager-approved requests are waiting for final HR review.'"
            />
        @endforelse

        @if($requests->count())
            <x-pagination-footer :paginator="$requests" />
        @endif
    </div>

    @if($approvedRequests->isNotEmpty())
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mt-4 mb-2">
            <div>
                <div class="small text-uppercase text-body-secondary fw-semibold">Approved leave</div>
                <h2 class="h6 fw-bold mb-0">Upcoming approved requests</h2>
            </div>
            <div class="small text-body-secondary">Future leave can be cancelled before it starts when payroll is not locked.</div>
        </div>

        <div class="reference-list">
            @foreach($approvedRequests as $leaveRequest)
                @php($empName = $leaveRequest->employee?->full_name_km ?: $leaveRequest->employee?->full_name_en ?: $leaveRequest->employee?->employee_code)
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 px-3 py-3 border-bottom">
                    <div>
                        <div class="fw-semibold text-dark">{{ $empName }} <span class="status-text text-bg-success ms-1">Approved</span></div>
                        <div class="small text-body-secondary mt-1">
                            {{ $leaveRequest->leaveType?->name }} · {{ $leaveRequest->start_date->format('d M Y') }} – {{ $leaveRequest->end_date->format('d M Y') }} · {{ number_format($leaveRequest->total_days ?? 0, 1) }} days
                        </div>
                    </div>
                    <button class="btn btn-action-link btn-sm text-danger" type="button" data-bs-toggle="modal" data-bs-target="#cancelApprovedLeave{{ $leaveRequest->id }}">
                        <i class="fa-solid fa-ban"></i><span>Cancel approved leave</span>
                    </button>
                </div>
            @endforeach
        </div>
    @endif

    @foreach($requests as $leaveRequest)
        <div class="modal fade" id="rejectModal{{ $leaveRequest->id }}" tabindex="-1">
            <div class="modal-dialog">
                <form class="modal-content" method="POST" action="{{ route('leave.requests.reject', $leaveRequest) }}">
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title fs-5">Reject leave request</h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-body-secondary mb-3">Provide a clear reason for rejecting the request for <strong>{{ $leaveRequest->employee?->getFullName() }}</strong>.</p>
                        <label class="form-label">Rejection reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="note" required minlength="3" maxlength="1000" rows="3" placeholder="Reason for rejection..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-danger" type="submit"><i class="fa-solid fa-xmark me-1"></i>Reject request</button>
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
                        <h2 class="modal-title fs-5">Cancel approved leave</h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-body-secondary">This restores the leave balance. Cancellation is blocked after leave starts or when an overlapping payroll period is locked.</p>
                        <label class="form-label">Cancellation reason <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="note" required minlength="5" maxlength="1000" rows="3" placeholder="Explain why the approved leave is being cancelled..."></textarea>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-light" type="button" data-bs-dismiss="modal">Keep approved leave</button>
                        <button class="btn btn-danger" type="submit"><i class="fa-solid fa-ban me-1"></i>Cancel leave</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
</x-layouts::app>
