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

    <div data-list-container>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div>
                <h2 class="h6 fw-bold mb-1">Payroll lifecycle</h2>
                <p class="small text-body-secondary mb-0">Process each cycle from draft calculation through approval and disbursement.</p>
            </div>
            <span class="badge rounded-pill text-bg-light border px-3 py-2">{{ $periods->total() }} period(s)</span>
        </div>

        <div class="vstack gap-3">
            @forelse($periods as $period)
                @php
                    $statusBadge = match($period->status) {
                        'paid' => 'success',
                        'approved' => 'primary',
                        'awaiting_approval' => 'warning',
                        'processing', 'draft' => 'info',
                        default => 'secondary',
                    };
                    $step = match($period->status) {
                        'draft' => 1,
                        'processing' => 2,
                        'awaiting_approval' => 3,
                        'approved' => 4,
                        'paid', 'closed' => 5,
                        default => 1,
                    };
                    $nextAction = match($period->status) {
                        'draft', 'processing' => 'Generate payroll calculations',
                        'awaiting_approval' => 'Review and approve payroll',
                        'approved' => 'Record salary disbursement',
                        'paid' => 'Payment recorded',
                        'closed' => 'Payroll cycle closed',
                        default => 'Review payroll cycle',
                    };
                @endphp

                <article class="card border-0 shadow-sm">
                    <div class="card-body p-3 p-lg-4">
                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                            <div>
                                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                    <h3 class="h6 fw-bold mb-0">{{ $period->name }}</h3>
                                    <span class="badge text-bg-{{ $statusBadge }}">{{ str($period->status)->replace('_',' ')->title() }}</span>
                                </div>
                                <div class="small text-body-secondary">
                                    {{ $period->start_date->format('d M Y') }} – {{ $period->end_date->format('d M Y') }}
                                    @if($period->payment_date)<span class="mx-1">•</span>Pay date {{ $period->payment_date->format('d M Y') }}@endif
                                </div>
                            </div>
                            <div class="text-lg-end">
                                <div class="small text-body-secondary">Net payroll</div>
                                <div class="fs-4 fw-bold">${{ number_format((float)$period->items_sum_net_salary, 2) }}</div>
                                <div class="small text-body-secondary">{{ $period->items_count }} employee(s)</div>
                            </div>
                        </div>

                        <div class="row g-3 mt-1 align-items-stretch">
                            <div class="col-12 col-xl-8">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                                        <div class="small text-uppercase text-body-secondary fw-semibold">Cycle progress</div>
                                        <div class="small fw-semibold">{{ $nextAction }}</div>
                                    </div>
                                    <div class="progress" role="progressbar" aria-label="Payroll progress" aria-valuemin="1" aria-valuemax="5" aria-valuenow="{{ $step }}" style="height:6px">
                                        <div class="progress-bar" style="width: {{ $step * 20 }}%"></div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-2 small text-body-secondary">
                                        <span>Draft</span><span>Calculate</span><span>Review</span><span>Approve</span><span>Pay</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-xl-4">
                                <div class="border rounded-3 p-3 h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="small text-uppercase text-body-secondary fw-semibold mb-2">Next action</div>
                                        <div class="fw-semibold">{{ $nextAction }}</div>
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 justify-content-xl-end mt-3">
                                        @if(auth()->user()->can('payroll.process') && in_array($period->status, ['draft','processing']))
                                            <form method="POST" action="{{ route('payroll.periods.generate', $period) }}">@csrf
                                                <button class="btn btn-primary btn-sm" type="submit"><i class="fa-solid fa-calculator me-1"></i>Generate</button>
                                            </form>
                                        @endif
                                        @if(auth()->user()->can('payroll.approve') && $period->status === 'awaiting_approval')
                                            <form method="POST" action="{{ route('payroll.periods.approve', $period) }}">@csrf
                                                <button class="btn btn-success btn-sm" type="submit"><i class="fa-solid fa-check me-1"></i>Approve payroll</button>
                                            </form>
                                        @endif
                                        @if(auth()->user()->can('payroll.process') && $period->status === 'approved')
                                            <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#pay{{ $period->id }}"><i class="fa-solid fa-money-bill-transfer me-1"></i>Record payment</button>
                                        @endif
                                        @if(in_array($period->status, ['paid','closed']))
                                            <span class="badge text-bg-success px-3 py-2"><i class="fa-solid fa-check me-1"></i>Completed</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="card border-0 shadow-sm">
                    <div class="card-body py-5 text-center">
                        <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center mb-3" style="width:52px;height:52px"><i class="fa-solid fa-money-check-dollar fa-lg"></i></div>
                        <h2 class="h6 fw-bold">No payroll cycles yet</h2>
                        <p class="text-body-secondary small mb-0">Create a payroll period to begin the calculation and approval workflow.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="mt-3"><x-pagination-footer :paginator="$periods" /></div>
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
                        <div class="col-md-4"><label class="form-label">Period Name <span class="text-danger">*</span></label><input class="form-control" name="name" required value="Payroll {{ now()->format('m/Y') }}"></div>
                        <div class="col-md-3"><label class="form-label">Start date <span class="text-danger">*</span></label><input class="form-control" type="date" name="start_date" required value="{{ now()->startOfMonth()->toDateString() }}"></div>
                        <div class="col-md-3"><label class="form-label">End date <span class="text-danger">*</span></label><input class="form-control" type="date" name="end_date" required value="{{ now()->endOfMonth()->toDateString() }}"></div>
                        <div class="col-md-2"><label class="form-label">Payment date</label><input class="form-control" type="date" name="payment_date" value="{{ now()->endOfMonth()->toDateString() }}"></div>
                        <div class="col-md-4"><label class="form-label">Tax exchange rate (KHR/USD) <span class="text-danger">*</span></label><input class="form-control" type="number" step="0.01" min="1" name="tax_exchange_rate_khr" required value="{{ \App\Models\PayrollSetting::forCompany(\App\Models\Company::query()->value('id'))->khr_per_usd }}"></div>
                        <div class="col-md-3"><label class="form-label">Rate date <span class="text-danger">*</span></label><input class="form-control" type="date" name="tax_rate_date" required value="{{ now()->toDateString() }}"></div>
                        <div class="col-md-5"><label class="form-label">Rate source <span class="text-danger">*</span></label><input class="form-control" type="url" name="tax_rate_source" required value="https://www.tax.gov.kh/en/exchange-rate"></div>
                        <div class="col-12"><label class="form-label">Notes &amp; Guidelines</label><textarea class="form-control" name="notes" rows="2" placeholder="Optional internal payroll remarks..."></textarea></div>
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
                            <div class="modal-header"><h2 class="modal-title fs-5"><i class="fa-solid fa-money-bill-transfer me-2 text-primary"></i>Record payment · {{ $period->name }}</h2><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div>
                            <div class="modal-body">
                                <label class="form-label">Payment Method</label>
                                <select class="form-select" name="payment_method"><option value="bank_transfer">Bank transfer</option><option value="cash">Cash</option><option value="cheque">Cheque</option><option value="mobile_banking">Mobile banking</option><option value="other">Other</option></select>
                                <label class="form-label mt-3">Disbursed timestamp <span class="text-danger">*</span></label><input class="form-control" type="datetime-local" name="paid_at" required value="{{ now()->format('Y-m-d\TH:i') }}">
                                <label class="form-label mt-3">Reference Number / Transaction ID</label><input class="form-control" name="reference_number" placeholder="Bank ref #">
                                <label class="form-label mt-3">Notes</label><textarea class="form-control" name="notes" placeholder="Disbursement remarks..."></textarea>
                            </div>
                            <div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary" type="submit">Record payment</button></div>
                        </form>
                    </div>
                </div>
            @endif
        @endforeach
    @endif
</x-layouts::app>