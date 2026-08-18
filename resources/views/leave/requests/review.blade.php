<x-layouts::app title="Review Leave Requests">
    <x-workspace-command-bar title="Leave Approval Queue" icon="fa-list-check" context="HR & Approvals">
        <x-slot:actions>
            <span class="badge text-bg-warning">
                <i class="fa-solid fa-clock me-1"></i>{{ number_format($statistics['pending_review']) }} waiting review
            </span>
        </x-slot:actions>
    </x-workspace-command-bar>

    @if (session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="reference-list" data-list-container>
        @if($requests->count())
            <div class="p-3 border-bottom bg-body-tertiary d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div>
                    <div class="fw-semibold text-dark">Requests needing a decision</div>
                    <div class="small text-body-secondary">Review the employee, leave period and reason before approving or rejecting.</div>
                </div>
                <span class="badge text-bg-light border text-dark">Oldest requests first</span>
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
                                                <span class="badge text-bg-warning"><i class="fa-solid fa-hourglass-half me-1"></i>Pending</span>
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
                                    <div class="d-flex flex-lg-column justify-content-end gap-2">
                                        <form method="POST" action="{{ route('leave.requests.approve', $leaveRequest) }}" class="flex-fill">
                                            @csrf
                                            <input type="hidden" name="note" value="Approved">
                                            <button class="btn btn-success btn-sm w-100" type="submit">
                                                <i class="fa-solid fa-check me-1"></i>Approve
                                            </button>
                                        </form>
                                        <button class="btn btn-outline-danger btn-sm flex-fill" type="button" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $leaveRequest->id }}">
                                            <i class="fa-solid fa-xmark me-1"></i>Reject
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3 pt-3 border-top">
                                <div class="text-body-secondary small mb-1">Employee reason</div>
                                <div class="small text-dark">{{ $leaveRequest->reason ?: 'No reason provided.' }}</div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
            <x-pagination-footer :paginator="$requests" />
        @else
            <div class="empty-state py-5 px-3">
                <i class="fa-solid fa-circle-check fa-2xl d-block mb-3 text-success"></i>
                <h2 class="h6 mb-1">Approval queue is clear</h2>
                <p class="text-body-secondary mb-0">All leave requests have been reviewed. New requests will appear here when they need a decision.</p>
            </div>
        @endif
    </div>

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
</x-layouts::app>
