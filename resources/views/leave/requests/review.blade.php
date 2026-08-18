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
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Employee</th>
                        <th>Leave Type</th>
                        <th>Dates & Duration</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $leaveRequest)
                        @php
                            $empName = $leaveRequest->employee?->full_name_km ?: $leaveRequest->employee?->full_name_en ?: $leaveRequest->employee?->employee_code;
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="fw-medium text-dark">{{ $empName }}</div>
                                <small class="text-body-secondary">{{ $leaveRequest->employee?->department?->name ?? '—' }}</small>
                            </td>
                            <td>
                                <span class="fw-medium">{{ $leaveRequest->leaveType?->name }}</span>
                            </td>
                            <td>
                                <div>{{ $leaveRequest->start_date->format('d M Y') }} – {{ $leaveRequest->end_date->format('d M Y') }}</div>
                                <small class="text-body-secondary">{{ number_format($leaveRequest->total_days ?? 0, 1) }} day{{ ($leaveRequest->total_days ?? 0) == 1 ? '' : 's' }}</small>
                            </td>
                            <td>
                                <span class="small text-body-secondary">{{ \Illuminate\Support\Str::limit($leaveRequest->reason, 80) ?: '—' }}</span>
                            </td>
                            <td>
                                <span class="badge text-bg-warning"><i class="fa-solid fa-hourglass-half me-1"></i>Pending</span>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                <form method="POST" action="{{ route('leave.requests.approve', $leaveRequest) }}" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="note" value="Approved">
                                    <button class="btn btn-action-link btn-sm text-success" type="submit">
                                        <i class="fa-solid fa-check"></i><span>Approve</span>
                                    </button>
                                </form>
                                <button class="btn btn-action-link btn-sm text-danger" type="button" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $leaveRequest->id }}">
                                    <i class="fa-solid fa-xmark"></i><span>Reject</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-body-secondary py-5" colspan="6">
                                <i class="fa-solid fa-circle-check fa-xl d-block mb-3 text-success"></i>All leave requests have been reviewed. The approval queue is empty!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$requests" />
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
