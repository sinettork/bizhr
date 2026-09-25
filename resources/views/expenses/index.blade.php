<x-layouts::app title="Expense Review & Approvals">
    <x-workspace-command-bar title="Expense Claims &amp; Reimbursement Review" icon="fa-receipt" context="Finance &amp; Spend Governance">
        <x-slot:filters>
            <form method="GET" class="reference-filter-form" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <select class="form-select reference-status" name="status" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                    <option value="">All claim statuses</option>
                    @foreach(['pending_manager' => 'Pending Manager Review', 'pending_accounting' => 'Pending Finance Review', 'approved' => 'Approved (Awaiting Payout)', 'paid' => 'Paid & Disbursed', 'rejected' => 'Rejected'] as $statusKey => $statusLabel)
                        <option value="{{ $statusKey }}" @selected(request('status') === $statusKey)>{{ $statusLabel }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary reference-search-button">Filter</button>
                @if(request()->filled('status'))
                    <a href="{{ route('expenses.index') }}" class="btn btn-action-link btn-sm"><i class="fa-solid fa-rotate-left"></i><span>Clear</span></a>
                @endif
            </form>
        </x-slot:filters>
    </x-workspace-command-bar>

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3"><i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
    @endif

    {{-- Financial Triage & Exposure Summary (Archetype A) --}}
    @php
        $pageClaims = collect($claims->items());
        $pendingApproval = $pageClaims->whereIn('status', ['pending_manager', 'pending_accounting']);
        $readyToPay = $pageClaims->where('status', 'approved');
        $totalPaid = $pageClaims->where('status', 'paid');
    @endphp

    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-body p-3">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="small text-body-secondary fw-semibold text-uppercase">Awaiting Review</div>
                            <div class="fs-4 fw-bold {{ $pendingApproval->count() > 0 ? 'text-warning' : 'text-dark' }} mt-1">
                                {{ $pendingApproval->count() }} <span class="fs-6 fw-normal text-body-secondary">claim(s)</span>
                            </div>
                        </div>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-warning-subtle text-warning" style="width:36px;height:36px">
                            <i class="fa-solid fa-clock"></i>
                        </span>
                    </div>
                    <div class="small text-body-secondary mt-2">Requires managerial or finance sign-off</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-body p-3">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="small text-body-secondary fw-semibold text-uppercase">Ready to Disburse</div>
                            <div class="fs-4 fw-bold {{ $readyToPay->count() > 0 ? 'text-primary' : 'text-dark' }} mt-1">
                                {{ $readyToPay->count() }} <span class="fs-6 fw-normal text-body-secondary">claim(s)</span>
                            </div>
                        </div>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary" style="width:36px;height:36px">
                            <i class="fa-solid fa-money-bill-transfer"></i>
                        </span>
                    </div>
                    <div class="small text-body-secondary mt-2">Approved by management; awaiting payment</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-body p-3">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="small text-body-secondary fw-semibold text-uppercase">Page Claims Total</div>
                            <div class="fs-4 fw-bold text-dark mt-1">
                                ${{ number_format($pageClaims->sum('amount'), 2) }}
                            </div>
                        </div>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-body-tertiary text-dark" style="width:36px;height:36px">
                            <i class="fa-solid fa-coins"></i>
                        </span>
                    </div>
                    <div class="small text-body-secondary mt-2">Total claim value across {{ $pageClaims->count() }} items</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-body p-3">
                    <div class="d-flex align-items-start justify-content-between">
                        <div>
                            <div class="small text-body-secondary fw-semibold text-uppercase">Completed / Settled</div>
                            <div class="fs-4 fw-bold text-success mt-1">
                                {{ $totalPaid->count() }} <span class="fs-6 fw-normal text-body-secondary">settled</span>
                            </div>
                        </div>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-success-subtle text-success" style="width:36px;height:36px">
                            <i class="fa-solid fa-circle-check"></i>
                        </span>
                    </div>
                    <div class="small text-body-secondary mt-2">Fully reimbursed and archived</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Claims Triage Ledger Table --}}
    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Date</th>
                        <th>Employee</th>
                        <th>Category</th>
                        <th>Business Purpose</th>
                        <th>Receipt</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end pe-3" style="min-width: 150px;">Decision</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($claims as $claim)
                        @php
                            $statusBadge = match($claim->status) {
                                'paid' => 'success',
                                'approved' => 'primary',
                                'rejected' => 'danger',
                                default => 'warning',
                            };
                            $stage = $claim->status === 'pending_manager' ? 'manager' : 'accounting';
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold text-dark">{{ $claim->expense_date?->format('d M Y') }}</div>
                                <small class="text-body-secondary">{{ $claim->expense_date?->diffForHumans() }}</small>
                            </td>
                            <td>
                                <div class="fw-medium text-dark">{{ $claim->employee?->getFullName() }}</div>
                                <small class="text-body-secondary">{{ $claim->employee?->department?->name ?? 'Unassigned' }}</small>
                            </td>
                            <td>
                                <span class="badge text-bg-light border text-dark">{{ $claim->category }}</span>
                            </td>
                            <td>
                                <span class="small text-body-secondary">{{ \Illuminate\Support\Str::limit($claim->business_purpose, 60) }}</span>
                            </td>
                            <td>
                                @if($claim->receipt_path)
                                    <a class="btn btn-outline-secondary btn-sm py-0 px-2" href="{{ route('expenses.receipt', $claim) }}" target="_blank" title="View attached receipt document">
                                        <i class="fa-solid fa-paperclip me-1 text-primary"></i><span class="small">Receipt</span>
                                    </a>
                                @else
                                    <span class="small text-body-tertiary">No receipt</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark fs-6">{{ $claim->currency }} {{ number_format($claim->amount, 2) }}</div>
                            </td>
                            <td>
                                <span class="badge text-bg-{{ $statusBadge }}">{{ str($claim->status)->headline() }}</span>
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                @if(in_array($claim->status, ['pending_manager', 'pending_accounting']))
                                    <button class="btn btn-primary btn-sm px-3" type="button" data-bs-toggle="modal" data-bs-target="#reviewClaim{{ $claim->id }}">
                                        <i class="fa-solid fa-clipboard-check me-1"></i><span>Review</span>
                                    </button>
                                @elseif($claim->status === 'approved')
                                    <button class="btn btn-outline-primary btn-sm px-3" type="button" data-bs-toggle="modal" data-bs-target="#disburseClaim{{ $claim->id }}">
                                        <i class="fa-solid fa-money-bill-transfer me-1"></i><span>Disburse</span>
                                    </button>
                                @else
                                    <span class="small text-body-tertiary">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-body-secondary py-5" colspan="8">
                                <i class="fa-solid fa-receipt fa-2xl d-block mb-3 text-primary opacity-50"></i>
                                <div class="fw-semibold text-dark">No expense claims to review</div>
                                <div class="small text-body-secondary mt-1">Claims submitted by employees for reimbursement will appear here for verification.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$claims" />
    </div>

    {{-- Review & Decision Modals (With Explicit Approve AND Reject workflows) --}}
    @foreach($claims as $claim)
        @if(in_array($claim->status, ['pending_manager', 'pending_accounting']))
            @php $currentStage = $claim->status === 'pending_manager' ? 'manager' : 'accounting'; @endphp
            <div class="modal fade" id="reviewClaim{{ $claim->id }}" tabindex="-1" aria-labelledby="reviewClaimTitle{{ $claim->id }}">
                <div class="modal-dialog modal-dialog-centered">
                    <form class="modal-content" method="POST">
                        @csrf
                        <div class="modal-header bg-body-tertiary py-3 px-4 border-bottom">
                            <h5 class="modal-title fs-6 fw-bold text-dark d-flex align-items-center" id="reviewClaimTitle{{ $claim->id }}">
                                <i class="fa-solid fa-clipboard-check text-primary me-2"></i>Review Expense Claim ({{ str($currentStage)->title() }} Stage)
                            </h5>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="p-3 bg-body-tertiary rounded-3 border mb-3">
                                <div class="row g-2 small">
                                    <div class="col-6">
                                        <span class="text-body-secondary d-block">Claimant</span>
                                        <strong class="text-dark">{{ $claim->employee?->getFullName() }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-body-secondary d-block">Amount</span>
                                        <strong class="text-primary fs-6">{{ $claim->currency }} {{ number_format($claim->amount, 2) }}</strong>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-body-secondary d-block">Expense Date</span>
                                        <span class="text-dark">{{ $claim->expense_date?->format('d M Y') }}</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-body-secondary d-block">Category</span>
                                        <span class="badge text-bg-light border">{{ $claim->category }}</span>
                                    </div>
                                    <div class="col-12 mt-2 pt-2 border-top">
                                        <span class="text-body-secondary d-block">Purpose / Justification</span>
                                        <span class="text-dark">{{ $claim->business_purpose }}</span>
                                    </div>
                                </div>
                            </div>

                            @if($claim->receipt_path)
                                <div class="d-flex align-items-center justify-content-between p-2 px-3 border rounded-3 mb-3 bg-light">
                                    <div class="d-flex align-items-center gap-2 small">
                                        <i class="fa-solid fa-file-invoice text-primary"></i>
                                        <span class="fw-medium text-dark">Attached Receipt Document</span>
                                    </div>
                                    <a class="btn btn-sm btn-outline-primary py-1 px-2" href="{{ route('expenses.receipt', $claim) }}" target="_blank">
                                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Inspect Receipt
                                    </a>
                                </div>
                            @endif

                            <div class="mb-2">
                                <label class="form-label small fw-semibold text-dark" for="decisionNote{{ $claim->id }}">
                                    Reviewer Decision Note <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control" id="decisionNote{{ $claim->id }}" name="note" rows="3" required placeholder="State reason for approval or rationale for rejection..."></textarea>
                                <div class="form-text small text-body-secondary">This note will be recorded in the audit trail and sent to the employee.</div>
                            </div>
                        </div>
                        <div class="modal-footer bg-body-tertiary py-2 px-4 border-top d-flex align-items-center justify-content-between">
                            <button type="submit" formaction="{{ route('expenses.review', [$claim, $currentStage, 'reject']) }}" class="btn btn-outline-danger btn-sm px-3" data-confirm="Reject this expense claim? The employee will be notified.">
                                <i class="fa-solid fa-xmark me-1"></i>Reject Claim
                            </button>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" formaction="{{ route('expenses.review', [$claim, $currentStage, 'approve']) }}" class="btn btn-primary btn-sm px-3">
                                    <i class="fa-solid fa-check me-1"></i>Approve Claim
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @elseif($claim->status === 'approved')
            <div class="modal fade" id="disburseClaim{{ $claim->id }}" tabindex="-1" aria-labelledby="disburseTitle{{ $claim->id }}">
                <div class="modal-dialog modal-dialog-centered">
                    <form class="modal-content" method="POST" action="{{ route('expenses.pay', $claim) }}">
                        @csrf
                        <div class="modal-header bg-body-tertiary py-3 px-4 border-bottom">
                            <h5 class="modal-title fs-6 fw-bold text-dark d-flex align-items-center" id="disburseTitle{{ $claim->id }}">
                                <i class="fa-solid fa-money-bill-transfer text-primary me-2"></i>Record Reimbursement Payout
                            </h5>
                            <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="p-3 bg-body-tertiary rounded-3 border mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="text-body-secondary small">Payee Employee</span>
                                    <strong class="text-dark">{{ $claim->employee?->getFullName() }}</strong>
                                </div>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="text-body-secondary small">Approved Reimbursement Amount</span>
                                    <strong class="fs-5 text-success">{{ $claim->currency }} {{ number_format($claim->amount, 2) }}</strong>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-dark" for="paymentRef{{ $claim->id }}">
                                    Bank Reference / Payment Voucher # <span class="text-danger">*</span>
                                </label>
                                <input class="form-control" id="paymentRef{{ $claim->id }}" name="reference" placeholder="e.g. TRX-90482 or PettyCash-Voucher-12" required>
                                <div class="form-text small text-body-secondary">Required for accounting reconciliation and financial audit trail.</div>
                            </div>
                        </div>
                        <div class="modal-footer bg-body-tertiary py-2 px-4 border-top d-flex align-items-center justify-content-end gap-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm px-3">
                                <i class="fa-solid fa-check me-1"></i>Confirm Disbursement
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    @endforeach
</x-layouts::app>
