<x-layouts::app title="Leave Requests">
    <x-workspace-command-bar title="My Leave & Time Off" icon="fa-calendar-day" context="Personal Workspace">
        <x-slot:actions>
            @can('attendance.checkin')
                <a class="btn btn-action-link btn-sm" href="{{ route('attendance.checkinout') }}"><i class="fa-solid fa-user-clock"></i><span>Attendance</span></a>
            @endcan
            <x-list-actions :add-modal="'newLeaveRequest'" add-label="New leave request" />
        </x-slot:actions>
    </x-workspace-command-bar>

    @if (session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger"><ul class="mb-0 ps-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="workspace-summary" aria-label="Leave summary">
        <div class="workspace-summary-item">
            <div class="label">Remaining leave</div>
            <div class="value">{{ number_format($statistics['remaining'], 1) }}</div>
            <div class="small text-body-secondary">Days available this year</div>
        </div>
        <div class="workspace-summary-item">
            <div class="label">Waiting approval</div>
            <div class="value">{{ number_format($statistics['pending']) }}</div>
            <div class="small text-body-secondary">Pending requests</div>
        </div>
        <div class="workspace-summary-item">
            <div class="label">Approved</div>
            <div class="value">{{ number_format($statistics['approved']) }}</div>
            <div class="small text-body-secondary">Approved requests</div>
        </div>
        <div class="workspace-summary-item">
            <div class="label">All requests</div>
            <div class="value">{{ number_format($statistics['total']) }}</div>
            <div class="small text-body-secondary">Request history</div>
        </div>
    </div>

    @if($balances->isNotEmpty())
        <section class="reference-list mb-3" aria-labelledby="leave-balances-heading">
            <div class="reference-list-toolbar">
                <div>
                    <div class="small text-uppercase text-body-secondary fw-semibold">Balance</div>
                    <div class="fw-semibold text-dark" id="leave-balances-heading">{{ now()->year }} leave balances</div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Leave type</th>
                            <th>Allocated</th>
                            <th>Used</th>
                            <th class="pe-3">Remaining</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($balances as $balance)
                            <tr>
                                <td class="ps-3 fw-semibold text-dark">{{ $balance->leaveType?->name ?? 'Leave' }}</td>
                                <td>{{ number_format((float) $balance->allocated_days, 1) }} days</td>
                                <td>{{ number_format((float) $balance->used_days, 1) }} days</td>
                                <td class="pe-3 fw-semibold">{{ number_format((float) $balance->remaining_days, 1) }} days</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Leave Type</th>
                        <th>Dates</th>
                        <th>Duration</th>
                        <th>Reason</th>
                        <th>Submitted</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $leaveRequest)
                        @php
                            [$statusDot, $statusText] = match($leaveRequest->status) {
                                'approved' => ['bg-success', 'Approved'],
                                'rejected' => ['bg-danger', 'Rejected'],
                                'withdrawn' => ['bg-secondary', 'Withdrawn'],
                                'cancelled' => ['bg-secondary', 'Cancelled'],
                                'manager_approved' => ['bg-primary', 'Awaiting HR'],
                                default => ['bg-warning', 'Pending manager'],
                            };
                            $canWithdraw = in_array($leaveRequest->status, ['pending', 'manager_approved'], true);
                        @endphp
                        <tr>
                            <td class="ps-3 fw-medium text-dark">{{ $leaveRequest->leaveType?->name }}</td>
                            <td>{{ $leaveRequest->start_date->format('d M Y') }} – {{ $leaveRequest->end_date->format('d M Y') }}</td>
                            <td>{{ number_format($leaveRequest->total_days ?? 0, 1) }} day{{ ($leaveRequest->total_days ?? 0) == 1 ? '' : 's' }}</td>
                            <td><span class="small text-body-secondary">{{ \Illuminate\Support\Str::limit($leaveRequest->reason, 70) ?: '—' }}</span></td>
                            <td><span class="small text-body-secondary">{{ $leaveRequest->created_at?->format('d M Y') ?? '—' }}</span></td>
                            <td>
                                <span class="d-inline-flex align-items-center gap-2 small fw-medium text-body-secondary">
                                    <span class="rounded-circle {{ $statusDot }}" style="width:7px;height:7px" aria-hidden="true"></span>
                                    {{ $statusText }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                @if ($canWithdraw)
                                    <form
                                        class="d-inline"
                                        method="POST"
                                        action="{{ route('leave.requests.withdraw', $leaveRequest) }}"
                                        data-confirm="Withdraw this leave request? It will stop moving through the approval workflow."
                                        data-confirm-title="Withdraw leave request"
                                        data-confirm-action="Withdraw"
                                        data-confirm-tone="warning"
                                    >
                                        @csrf
                                        <button class="btn btn-action-link btn-sm text-warning-emphasis" type="submit">
                                            <i class="fa-solid fa-rotate-left me-1"></i>Withdraw
                                        </button>
                                    </form>
                                @else
                                    <span class="small text-body-secondary">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-0">
                                <x-empty-state class="py-5 px-3" icon="fa-calendar-xmark" title="No leave requests yet" message="Your leave request history will appear here after you submit your first request." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$requests" />
    </div>

    <div class="modal fade" id="newLeaveRequest" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" method="POST" action="{{ route('leave.requests.store') }}">
                @csrf
                <div class="modal-header">
                    <h2 class="modal-title fs-5"><i class="fa-solid fa-calendar-plus me-2 text-primary"></i>Request time off</h2>
                    <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-body-secondary mb-3">Select the leave type and dates. BizHR calculates working days and validates the request against your available balance and existing leave.</p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Leave type <span class="text-danger">*</span></label>
                            <select class="form-select" name="leave_type_id" required>
                                <option value="">Select type...</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}" @selected(old('leave_type_id') == $type->id)>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Start date <span class="text-danger">*</span></label>
                            <input class="form-control" name="start_date" type="date" value="{{ old('start_date', today()->toDateString()) }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End date <span class="text-danger">*</span></label>
                            <input class="form-control" name="end_date" type="date" value="{{ old('end_date', today()->toDateString()) }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Reason / Justification</label>
                            <textarea class="form-control" name="reason" rows="3" placeholder="Provide notes or reason for this leave request...">{{ old('reason') }}</textarea>
                        </div>
                    </div>
                </div>
                <x-form-save-actions save-label="Submit leave request" />
            </form>
        </div>
    </div>
</x-layouts::app>
