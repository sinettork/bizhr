<x-layouts::app title="Payroll Periods">
    <x-workspace-command-bar title="Payroll Cycles &amp; Disbursements" icon="fa-money-check-dollar" context="Payroll &amp; Compensation">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search payroll period..." hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="false" hx-trigger="keyup changed delay:500ms">
                </div>
                <select class="form-select reference-status" name="status" hx-get="{{ request()->url() }}" hx-target="[data-list-container]" hx-swap="outerHTML" hx-push-url="true" hx-trigger="change">
                    <option value="">All statuses</option>
                    @foreach(['draft','processing','awaiting_approval','approved','paid','closed'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->replace('_',' ')->title() }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary reference-search-button">Filter</button>
            </form>
        </x-slot:filters>
        <x-slot:actions>
            <x-list-actions :add-modal="auth()->user()->can('payroll.edit') ? 'createPeriod' : null" add-label="Create payroll period" />
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
                        <th class="ps-3">Period Name</th>
                        <th>Dates</th>
                        <th>Payment Date</th>
                        <th>Employees</th>
                        <th>Total Net Payout</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($periods as $period)
                        @php
                            $statusBadge = match($period->status) {
                                'paid' => 'success',
                                'approved' => 'primary',
                                'awaiting_approval' => 'warning',
                                'processing', 'draft' => 'info',
                                default => 'secondary',
                            };
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold text-dark">{{ $period->name }}</div>
                            </td>
                            <td>{{ $period->start_date->format('d/m/Y') }} – {{ $period->end_date->format('d/m/Y') }}</td>
                            <td>{{ $period->payment_date?->format('d M Y') ?? '—' }}</td>
                            <td>{{ $period->items_count }} employee(s)</td>
                            <td class="fw-bold text-dark">${{ number_format((float)$period->items_sum_net_salary, 2) }}</td>
                            <td><span class="badge text-bg-{{ $statusBadge }}">{{ str($period->status)->replace('_',' ')->title() }}</span></td>
                            <td class="text-end pe-3 text-nowrap">
                                @if(auth()->user()->can('payroll.process') && in_array($period->status, ['draft','processing']))
                                    <form method="POST" action="{{ route('payroll.periods.generate', $period) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-action-link btn-sm" type="submit">
                                            <i class="fa-solid fa-calculator"></i><span>Generate</span>
                                        </button>
                                    </form>
                                @endif

                                @if(auth()->user()->can('payroll.approve') && $period->status === 'awaiting_approval')
                                    <form method="POST" action="{{ route('payroll.periods.approve', $period) }}" class="d-inline">
                                        @csrf
                                        <button class="btn btn-action-link btn-sm text-success" type="submit">
                                            <i class="fa-solid fa-check"></i><span>Approve</span>
                                        </button>
                                    </form>
                                @endif

                                @if(auth()->user()->can('payroll.process') && $period->status === 'approved')
                                    <button class="btn btn-action-link btn-sm text-primary" type="button" data-bs-toggle="modal" data-bs-target="#pay{{ $period->id }}">
                                        <i class="fa-solid fa-money-bill-transfer"></i><span>Record Payment</span>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="text-center text-body-secondary py-5" colspan="7">
                                <i class="fa-solid fa-money-check-dollar fa-xl d-block mb-3 text-primary"></i>No payroll periods recorded. Click "Create payroll period" to start a new cycle.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$periods" />
    </div>

    @if(auth()->user()->can('payroll.edit'))
        <div class="modal fade" id="createPeriod" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <form class="modal-content" method="POST" action="{{ route('payroll.periods.store') }}">
                    @csrf
                    <div class="modal-header">
                        <h2 class="modal-title fs-5"><i class="fa-solid fa-money-check-dollar me-2 text-primary"></i>Create payroll period</h2>
                        <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Period Name <span class="text-danger">*</span></label>
                            <input class="form-control" name="name" required value="Payroll {{ now()->format('m/Y') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Start date <span class="text-danger">*</span></label>
                            <input class="form-control" type="date" name="start_date" required value="{{ now()->startOfMonth()->toDateString() }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">End date <span class="text-danger">*</span></label>
                            <input class="form-control" type="date" name="end_date" required value="{{ now()->endOfMonth()->toDateString() }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Payment date</label>
                            <input class="form-control" type="date" name="payment_date" value="{{ now()->endOfMonth()->toDateString() }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Tax exchange rate (KHR/USD) <span class="text-danger">*</span></label>
                            <input class="form-control" type="number" step="0.01" min="1" name="tax_exchange_rate_khr" required value="{{ \App\Models\PayrollSetting::forCompany(\App\Models\Company::query()->value('id'))->khr_per_usd }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Rate date <span class="text-danger">*</span></label>
                            <input class="form-control" type="date" name="tax_rate_date" required value="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Rate source <span class="text-danger">*</span></label>
                            <input class="form-control" type="url" name="tax_rate_source" required value="https://www.tax.gov.kh/en/exchange-rate">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes &amp; Guidelines</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="Optional internal payroll remarks..."></textarea>
                        </div>
                    </div>
                    <x-form-save-actions save-label="Create payroll period" />
                </form>
            </div>
        </div>
    @endif

    @if(auth()->user()->can('payroll.process'))
        @foreach($periods as $period)
            @if($period->status === 'approved')
                <div class="modal fade" id="pay{{ $period->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <form class="modal-content" method="POST" action="{{ route('payroll.periods.pay', $period) }}">
                            @csrf
                            <div class="modal-header">
                                <h2 class="modal-title fs-5"><i class="fa-solid fa-money-bill-transfer me-2 text-primary"></i>Record payment · {{ $period->name }}</h2>
                                <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <label class="form-label">Payment Method</label>
                                <select class="form-select" name="payment_method">
                                    <option value="bank_transfer">Bank transfer</option>
                                    <option value="cash">Cash</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="mobile_banking">Mobile banking</option>
                                    <option value="other">Other</option>
                                </select>
                                <label class="form-label mt-3">Disbursed timestamp <span class="text-danger">*</span></label>
                                <input class="form-control" type="datetime-local" name="paid_at" required value="{{ now()->format('Y-m-d\TH:i') }}">
                                <label class="form-label mt-3">Reference Number / Transaction ID</label>
                                <input class="form-control" name="reference_number" placeholder="Bank ref #">
                                <label class="form-label mt-3">Notes</label>
                                <textarea class="form-control" name="notes" placeholder="Disbursement remarks..."></textarea>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-primary" type="submit">Record payment</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</x-layouts::app>