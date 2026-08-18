<x-layouts::app title="Payroll Review & Approvals">
    <x-workspace-command-bar title="Payroll Review & Exception Queue" icon="fa-file-circle-check" context="Payroll & Auditing" />

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
    @endif

    <!-- Overtime Approvals Section -->
    <div class="d-flex align-items-center justify-content-between mb-2">
        <h2 class="h6 fw-bold text-dark mb-0"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i>Overtime Approval Queue</h2>
        <form method="GET" class="reference-filter-form" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
            <select class="form-select reference-status" name="overtime_status" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                <option value="pending">Pending only</option>
                <option value="approved" @selected(request('overtime_status')==='approved')>Approved</option>
                <option value="rejected" @selected(request('overtime_status')==='rejected')>Rejected</option>
                <option value="" @selected(request()->missing('overtime_status'))>All statuses</option>
            </select>
            <button class="btn btn-primary reference-search-button">Filter</button>
        </form>
    </div>

    <div class="reference-list mb-4" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Employee</th>
                        <th>Work Date</th>
                        <th>Overtime Hours</th>
                        <th>Status</th>
                        <th>Review Note</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overtime as $row)
                        @php
                            $isPending = $row->overtime_review_status === 'pending';
                            $statusTone = match($row->overtime_review_status) {
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'warning',
                            };
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold text-dark">{{ $row->employee?->getFullName() }}</div>
                                <small class="text-body-secondary">{{ $row->employee?->department?->name ?? '—' }}</small>
                            </td>
                            <td>{{ $row->work_date->format('d M Y') }}</td>
                            <td><strong class="text-dark">{{ number_format($row->overtime_minutes/60, 2) }} hrs</strong></td>
                            <td><span class="badge text-bg-{{ $statusTone }}">{{ ucfirst($row->overtime_review_status) }}</span></td>
                            <td><span class="small text-body-secondary">{{ $row->overtime_review_note ?: '—' }}</span></td>
                            <td class="text-end pe-3 text-nowrap">
                                @if($isPending)
                                    <form method="POST" action="{{ route('payroll.overtime.review', [$row, 'approve']) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-action-link btn-sm text-success" type="submit">
                                            <i class="fa-solid fa-check"></i><span>Approve</span>
                                        </button>
                                    </form>
                                    <button class="btn btn-action-link btn-sm text-danger" type="button" data-bs-toggle="modal" data-bs-target="#rejectOt{{ $row->id }}">
                                        <i class="fa-solid fa-xmark"></i><span>Reject</span>
                                    </button>
                                @else
                                    <span class="small text-body-secondary">Reviewed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-4 text-center text-body-secondary">
                                <i class="fa-solid fa-circle-check text-success me-1"></i>No overtime records waiting for approval in this queue.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Payroll Calculation Exceptions Section -->
    <div class="d-flex align-items-center justify-content-between mb-2">
        <div>
            <h2 class="h6 fw-bold text-dark mb-0"><i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>Payroll Calculation Exceptions</h2>
            <span class="small text-body-secondary">Resolve exception source before approving a period, then re-generate its draft.</span>
        </div>
    </div>

    <div class="reference-list">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Period</th>
                        <th>Employee</th>
                        <th>Exception Count</th>
                        <th class="pe-3">Calculated Net Salary</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exceptions as $item)
                        <tr>
                            <td class="ps-3 fw-semibold text-dark">{{ $item->period?->name }}</td>
                            <td>{{ $item->employee?->getFullName() }}</td>
                            <td><span class="badge text-bg-warning"><i class="fa-solid fa-triangle-exclamation me-1"></i>{{ $item->exception_count }} exception(s)</span></td>
                            <td class="pe-3 fw-bold text-dark">${{ number_format($item->net_salary, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-4 text-center text-body-secondary">
                                <i class="fa-solid fa-check-double text-success me-1"></i>No payroll calculation exceptions. All payroll entries are balanced!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach($overtime as $row)
        @if($row->overtime_review_status === 'pending')
            <div class="modal fade" id="rejectOt{{ $row->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('payroll.overtime.review', [$row, 'reject']) }}">
                        @csrf
                        <div class="modal-header">
                            <h2 class="modal-title fs-5">Reject Overtime Record</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-body-secondary mb-3">Rejecting overtime for <strong>{{ $row->employee?->getFullName() }}</strong> on {{ $row->work_date->format('d M Y') }} ({{ number_format($row->overtime_minutes/60, 2) }} hrs).</p>
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
        @endif
    @endforeach
</x-layouts::app>
