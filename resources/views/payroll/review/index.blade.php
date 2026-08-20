<x-layouts::app title="Payroll Review & Approvals">
    <x-workspace-command-bar title="Payroll Review & Exception Queue" icon="fa-file-circle-check" context="Payroll & Auditing">
        <x-slot:filters>
            <form method="GET" class="reference-filter-form" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <select class="form-select reference-status" name="overtime_status" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                    <option value="pending">Pending overtime</option>
                    <option value="approved" @selected(request('overtime_status')==='approved')>Approved overtime</option>
                    <option value="rejected" @selected(request('overtime_status')==='rejected')>Rejected overtime</option>
                    <option value="" @selected(request()->missing('overtime_status'))>All overtime</option>
                </select>
                <button class="btn btn-primary reference-search-button">Filter</button>
            </form>
        </x-slot:filters>
    </x-workspace-command-bar>

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
    @endif

    <div class="workspace-summary" aria-label="Payroll review summary">
        <div class="workspace-summary-item">
            <div class="label">Overtime in view</div>
            <div class="value">{{ number_format($overtime->total()) }}</div>
        </div>
        <div class="workspace-summary-item">
            <div class="label">Payroll exceptions</div>
            <div class="value">{{ number_format($exceptions->total()) }}</div>
        </div>
    </div>

    <div class="d-flex align-items-end justify-content-between gap-2 mb-2">
        <div>
            <div class="small text-uppercase text-body-secondary fw-semibold">Overtime</div>
            <h2 class="h6 fw-bold mb-0">Approval queue</h2>
        </div>
        <div class="small text-body-secondary">Finalized payroll locks overtime decisions for covered dates.</div>
    </div>

    <div class="reference-list mb-4" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Employee</th>
                        <th>Work date</th>
                        <th>Overtime</th>
                        <th>Status</th>
                        <th>Review note</th>
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
                            <td class="fw-semibold text-dark">{{ number_format($row->overtime_minutes / 60, 2) }} h</td>
                            <td><span class="status-text text-bg-{{ $statusTone }}">{{ ucfirst($row->overtime_review_status) }}</span></td>
                            <td><span class="small text-body-secondary">{{ $row->overtime_review_note ?: '—' }}</span></td>
                            <td class="text-end pe-3 text-nowrap">
                                @if($isPending)
                                    <form method="POST" action="{{ route('payroll.overtime.review', [$row, 'approve']) }}" class="d-inline" data-confirm="Approve this overtime record? Re-generate any open payroll draft afterward so the change is included." data-confirm-title="Approve overtime" data-confirm-action="Approve" data-confirm-tone="primary">
                                        @csrf
                                        <button class="btn btn-primary btn-sm" type="submit"><i class="fa-solid fa-check me-1"></i>Approve</button>
                                    </form>
                                    <button class="btn btn-action-link btn-sm text-danger" type="button" data-bs-toggle="modal" data-bs-target="#rejectOt{{ $row->id }}"><i class="fa-solid fa-xmark"></i><span>Reject</span></button>
                                @else
                                    <span class="small text-body-secondary">Reviewed</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-0"><x-empty-state class="py-5 px-3" icon="fa-circle-check" tone="success" title="Overtime queue is clear" message="No overtime records match this review view." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$overtime" />
    </div>

    <div class="d-flex align-items-end justify-content-between gap-2 mb-2">
        <div>
            <div class="small text-uppercase text-body-secondary fw-semibold">Payroll calculation</div>
            <h2 class="h6 fw-bold mb-0">Exceptions</h2>
        </div>
        <div class="small text-body-secondary">Resolve the source and regenerate the draft before approval.</div>
    </div>

    <div class="reference-list">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Period</th>
                        <th>Employee</th>
                        <th>Exceptions</th>
                        <th class="pe-3">Calculated net salary</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exceptions as $item)
                        <tr>
                            <td class="ps-3 fw-semibold text-dark">{{ $item->period?->name }}</td>
                            <td>{{ $item->employee?->getFullName() }}</td>
                            <td><span class="status-text text-bg-warning">{{ $item->exception_count }} exception{{ $item->exception_count == 1 ? '' : 's' }}</span></td>
                            <td class="pe-3 fw-semibold text-dark">${{ number_format($item->net_salary, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-0"><x-empty-state class="py-5 px-3" icon="fa-check-double" tone="success" title="No payroll exceptions" message="Current payroll calculations have no recorded exceptions." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$exceptions" />
    </div>

    @foreach($overtime as $row)
        @if($row->overtime_review_status === 'pending')
            <div class="modal fade" id="rejectOt{{ $row->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('payroll.overtime.review', [$row, 'reject']) }}">
                        @csrf
                        <div class="modal-header">
                            <h2 class="modal-title fs-5">Reject overtime</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="small text-body-secondary mb-3">{{ $row->employee?->getFullName() }} · {{ $row->work_date->format('d M Y') }} · {{ number_format($row->overtime_minutes / 60, 2) }} h</p>
                            <label class="form-label">Rejection reason <span class="text-danger">*</span></label>
                            <textarea class="form-control" name="note" required minlength="3" rows="3" placeholder="Explain why the overtime cannot be approved."></textarea>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                            <button class="btn btn-danger" type="submit"><i class="fa-solid fa-xmark me-1"></i>Reject overtime</button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach
</x-layouts::app>
