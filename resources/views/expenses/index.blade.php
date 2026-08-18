<x-layouts::app title="Expense Review & Approvals">
    <x-workspace-command-bar title="Expense Claims & Review" icon="fa-receipt" context="Finance & Claims">
        <x-slot:filters>
            <form method="GET" class="reference-filter-form" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <select class="form-select reference-status" name="status" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                    <option value="">All claim statuses</option>
                    @foreach(['pending_manager','pending_accounting','approved','paid','rejected'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->headline() }}</option>
                    @endforeach
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

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Date</th>
                        <th>Employee</th>
                        <th>Category</th>
                        <th>Business Purpose</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
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
                        @endphp
                        <tr>
                            <td class="ps-3">{{ $claim->expense_date?->format('d M Y') }}</td>
                            <td>
                                <div class="fw-medium text-dark">{{ $claim->employee?->getFullName() }}</div>
                                <small class="text-body-secondary">{{ $claim->employee?->department?->name ?? '—' }}</small>
                            </td>
                            <td><span class="badge text-bg-light border text-dark">{{ $claim->category }}</span></td>
                            <td><span class="small text-body-secondary">{{ \Illuminate\Support\Str::limit($claim->business_purpose, 60) }}</span></td>
                            <td class="fw-bold text-dark">{{ $claim->currency }} {{ number_format($claim->amount, 2) }}</td>
                            <td><span class="badge text-bg-{{ $statusBadge }}">{{ str($claim->status)->headline() }}</span></td>
                            <td class="text-end pe-3 text-nowrap">
                                <a class="btn btn-action-link btn-sm" href="{{ route('expenses.receipt', $claim) }}" target="_blank">
                                    <i class="fa-solid fa-paperclip"></i><span>Receipt</span>
                                </a>
                                @if(in_array($claim->status, ['pending_manager', 'pending_accounting']))
                                    <button class="btn btn-action-link btn-sm text-success" type="button" data-bs-toggle="modal" data-bs-target="#reviewClaim{{ $claim->id }}">
                                        <i class="fa-solid fa-check"></i><span>Review</span>
                                    </button>
                                @elseif($claim->status === 'approved')
                                    <button class="btn btn-action-link btn-sm text-primary" type="button" data-bs-toggle="modal" data-bs-target="#disburseClaim{{ $claim->id }}">
                                        <i class="fa-solid fa-money-bill-transfer"></i><span>Disburse</span>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-body-secondary py-5" colspan="7">
                                <i class="fa-solid fa-receipt fa-xl d-block mb-3 text-primary"></i>No expense claims to review.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$claims" />
    </div>

    @foreach($claims as $claim)
        @if(in_array($claim->status, ['pending_manager', 'pending_accounting']))
            <div class="modal fade" id="reviewClaim{{ $claim->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('expenses.review', [$claim, $claim->status === 'pending_manager' ? 'manager' : 'accounting', 'approve']) }}">
                        @csrf
                        <div class="modal-header">
                            <h2 class="modal-title fs-5">Review Expense Claim</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3 small">
                                <div><span class="text-body-secondary">Employee:</span> <strong>{{ $claim->employee?->getFullName() }}</strong></div>
                                <div><span class="text-body-secondary">Amount:</span> <strong>{{ $claim->currency }} {{ number_format($claim->amount, 2) }}</strong></div>
                                <div><span class="text-body-secondary">Purpose:</span> {{ $claim->business_purpose }}</div>
                            </div>
                            <label class="form-label">Decision note / comment <span class="text-danger">*</span></label>
                            <input class="form-control" name="note" value="Reviewed and approved" required placeholder="Decision note">
                        </div>
                        <x-form-save-actions save-label="Approve claim" />
                    </form>
                </div>
            </div>
        @elseif($claim->status === 'approved')
            <div class="modal fade" id="disburseClaim{{ $claim->id }}" tabindex="-1">
                <div class="modal-dialog">
                    <form class="modal-content" method="POST" action="{{ route('expenses.pay', $claim) }}">
                        @csrf
                        <div class="modal-header">
                            <h2 class="modal-title fs-5">Disburse Expense Payout</h2>
                            <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3 small">
                                <div><span class="text-body-secondary">Pay to:</span> <strong>{{ $claim->employee?->getFullName() }}</strong></div>
                                <div><span class="text-body-secondary">Amount:</span> <strong class="text-success">{{ $claim->currency }} {{ number_format($claim->amount, 2) }}</strong></div>
                            </div>
                            <label class="form-label">Payment reference / transaction ID <span class="text-danger">*</span></label>
                            <input class="form-control" name="reference" placeholder="e.g. TRX-10293 or Bank Ref #" required>
                        </div>
                        <x-form-save-actions save-label="Record disbursement" />
                    </form>
                </div>
            </div>
        @endif
    @endforeach
</x-layouts::app>
