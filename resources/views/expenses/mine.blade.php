<x-layouts::app title="My Expenses & Claims">
    <x-workspace-command-bar title="My Expense Reimbursements" icon="fa-money-bill-wave" context="Personal Workspace">
        <x-slot:actions>
            <x-list-actions :add-modal="'claimForm'" add-label="Submit new claim" />
        </x-slot:actions>
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
                        <th class="ps-3">Expense Date</th>
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
                            <td><span class="badge text-bg-light border text-dark">{{ $claim->category }}</span></td>
                            <td><span class="small text-body-secondary">{{ \Illuminate\Support\Str::limit($claim->business_purpose, 80) }}</span></td>
                            <td class="fw-bold text-dark">{{ $claim->currency }} {{ number_format($claim->amount, 2) }}</td>
                            <td><span class="badge text-bg-{{ $statusBadge }}">{{ str($claim->status)->headline() }}</span></td>
                            <td class="text-end pe-3 text-nowrap">
                                <a class="btn btn-action-link btn-sm" href="{{ route('expenses.receipt', $claim) }}" target="_blank">
                                    <i class="fa-solid fa-file-arrow-down"></i><span>Receipt</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-body-secondary py-5" colspan="6">
                                <i class="fa-solid fa-receipt fa-xl d-block mb-3 text-primary"></i>You have not submitted any expense reimbursement claims.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$claims" />
    </div>

    <div class="modal fade" id="claimForm" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" method="POST" enctype="multipart/form-data" action="{{ route('expenses.store') }}">
                @csrf
                <div class="modal-header">
                    <h2 class="modal-title fs-5"><i class="fa-solid fa-file-invoice-dollar me-2 text-primary"></i>New expense reimbursement claim</h2>
                    <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Expense date <span class="text-danger">*</span></label>
                        <input class="form-control" type="date" name="expense_date" max="{{ today()->format('Y-m-d') }}" required value="{{ today()->format('Y-m-d') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <input class="form-control" name="category" required placeholder="e.g. Travel, Meals, Supplies">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input class="form-control" type="number" min="0.01" step="0.01" name="amount" required placeholder="0.00">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Currency</label>
                        <select class="form-select" name="currency">
                            <option>USD</option>
                            <option>KHR</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Business purpose <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="business_purpose" required minlength="10" rows="3" placeholder="Provide justification and context for this expenditure..."></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Receipt document (PDF, JPG, PNG) <span class="text-danger">*</span></label>
                        <input class="form-control" type="file" name="receipt" accept=".pdf,.jpg,.jpeg,.png" required>
                    </div>
                </div>
                <x-form-save-actions save-label="Submit claim for review" />
            </form>
        </div>
    </div>
</x-layouts::app>
