<x-layouts::app title="Payroll">
    @php
        $statusMeta = fn (string $status) => match ($status) {
            'draft' => ['Draft', 'text-secondary', 'fa-pen-ruler'],
            'processing' => ['Calculating', 'text-primary', 'fa-calculator'],
            'awaiting_approval' => ['Needs approval', 'text-warning', 'fa-circle-check'],
            'approved' => ['Ready to pay', 'text-primary', 'fa-money-bill-transfer'],
            'paid' => ['Paid', 'text-success', 'fa-circle-check'],
            'closed' => ['Closed', 'text-secondary', 'fa-lock'],
            default => [str($status)->replace('_', ' ')->title(), 'text-secondary', 'fa-circle'],
        };
        $stage = fn (string $status) => match ($status) {
            'draft' => 1,
            'processing' => 2,
            'awaiting_approval' => 3,
            'approved' => 4,
            'paid', 'closed' => 5,
            default => 1,
        };
        $nextAction = fn (string $status) => match ($status) {
            'draft' => 'Generate payroll calculations',
            'processing' => 'Recalculate after resolving payroll inputs',
            'awaiting_approval' => 'Review exceptions and approve payroll',
            'approved' => 'Record salary disbursement',
            'paid' => 'Close the completed payroll cycle',
            'closed' => 'Cycle closed and locked',
            default => 'Review payroll cycle',
        };
    @endphp

    <x-workspace-command-bar title="Payroll Control Center" icon="fa-money-check-dollar" context="Payroll &amp; Compensation">
        <x-slot:filters>
            <form class="reference-filter-form" method="GET">
                <div class="input-group reference-search">
                    <span class="input-group-text"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" name="search" value="{{ request('search') }}" placeholder="Search payroll period">
                </div>
                <select class="form-select reference-status" name="status">
                    <option value="">All statuses</option>
                    @foreach(['draft','processing','awaiting_approval','approved','paid','closed'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ str($status)->replace('_',' ')->title() }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary reference-search-button" type="submit">Filter</button>
            </form>
        </x-slot:filters>
        <x-slot:actions>
            <div class="d-flex flex-wrap gap-2">
                @can('payroll.approve')
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('payroll.review') }}"><i class="fa-solid fa-list-check me-1"></i>Review exceptions</a>
                @endcan
                @can('payroll.view')
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route('payroll.settings') }}"><i class="fa-solid fa-gear me-1"></i>Settings</a>
                @endcan
                @can('payroll.edit')
                    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#createPeriod"><i class="fa-solid fa-plus me-1"></i>New payroll period</button>
                @endcan
            </div>
        </x-slot:actions>
    </x-workspace-command-bar>

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2"><i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
    @endif

    <section class="row g-3 mb-4" aria-label="Payroll summary">
        <div class="col-6 col-xl-3">
            <div class="profile-card h-100 mb-0"><div class="profile-card-body p-3"><div class="d-flex align-items-start justify-content-between gap-3"><div><div class="small text-body-secondary mb-1">Open cycles</div><div class="fs-3 fw-bold lh-1">{{ $summary['open_cycles'] }}</div></div><span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-body-tertiary text-primary" style="width:36px;height:36px"><i class="fa-solid fa-rotate"></i></span></div><div class="small text-body-secondary mt-2">Still in payroll workflow</div></div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="profile-card h-100 mb-0"><div class="profile-card-body p-3"><div class="d-flex align-items-start justify-content-between gap-3"><div><div class="small text-body-secondary mb-1">Needs approval</div><div class="fs-3 fw-bold lh-1">{{ $summary['needs_approval'] }}</div></div><span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-body-tertiary text-warning" style="width:36px;height:36px"><i class="fa-solid fa-user-check"></i></span></div><div class="small text-body-secondary mt-2">Waiting for authorized review</div></div></div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="profile-card h-100 mb-0"><div class="profile-card-body p-3"><div class="d-flex align-items-start justify-content-between gap-3"><div><div class="small text-body-secondary mb-1">Ready to pay</div><div class="fs-3 fw-bold lh-1">{{ $summary['awaiting_payment'] }}</div></div><span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-body-tertiary text-primary" style="width:36px;height:36px"><i class="fa-solid fa-money-bill-transfer"></i></span></div><div class="small text-body-secondary mt-2">Approved cycles awaiting payment</div></div></div>
        </div>
        <div class="col-6 col-xl-3">
            <a class="profile-card h-100 mb-0 d-block text-decoration-none text-reset" href="{{ route('payroll.review') }}"><div class="profile-card-body p-3"><div class="d-flex align-items-start justify-content-between gap-3"><div><div class="small text-body-secondary mb-1">Exceptions</div><div class="fs-3 fw-bold lh-1">{{ $summary['exceptions'] }}</div></div><span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-body-tertiary {{ $summary['exceptions'] > 0 ? 'text-danger' : 'text-success' }}" style="width:36px;height:36px"><i class="fa-solid fa-triangle-exclamation"></i></span></div><div class="small text-body-secondary mt-2">Payroll items that need attention</div></div></a>
        </div>
    </section>

    @if($latestPeriod)
        @php([$latestStatusLabel, $latestStatusTone, $latestStatusIcon] = $statusMeta($latestPeriod->status))
        @php($latestStage = $stage($latestPeriod->status))
        <section class="profile-card mb-4" aria-labelledby="current-payroll-heading">
            <div class="profile-card-header flex-wrap gap-2">
                <div><div class="small text-body-secondary mb-1">Latest payroll cycle</div><h2 class="profile-card-title mb-0" id="current-payroll-heading"><i class="fa-solid fa-calendar-days text-primary"></i><span>{{ $latestPeriod->name }}</span></h2></div>
                <div class="d-flex align-items-center gap-2 small fw-semibold {{ $latestStatusTone }}"><span class="rounded-circle bg-current" aria-hidden="true" style="width:7px;height:7px;background:currentColor"></span><span>{{ $latestStatusLabel }}</span></div>
            </div>
            <div class="profile-card-body p-3 p-lg-4">
                <div class="row g-4 align-items-stretch">
                    <div class="col-12 col-xl-8">
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 mb-4">
                            <div><div class="small text-body-secondary mb-1">Payroll window</div><div class="fw-semibold">{{ $latestPeriod->start_date->format('d M Y') }} – {{ $latestPeriod->end_date->format('d M Y') }}</div><div class="small text-body-secondary mt-1">Payment date: {{ $latestPeriod->payment_date?->format('d M Y') ?? 'Not scheduled' }}</div></div>
                            <div class="text-md-end"><div class="small text-body-secondary mb-1">Net payroll</div><div class="fs-3 fw-bold">${{ number_format((float) $latestPeriod->items_sum_net_salary, 2) }}</div><div class="small text-body-secondary">{{ $latestPeriod->items_count }} employee(s)</div></div>
                        </div>
                        <div class="border rounded-3 p-3">
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-3"><div><div class="small text-uppercase text-body-secondary fw-semibold">Workflow progress</div><div class="small fw-semibold mt-1">{{ $nextAction($latestPeriod->status) }}</div></div><div class="small text-body-secondary">Step {{ $latestStage }} of 5</div></div>
                            <div class="progress" role="progressbar" aria-label="Payroll workflow progress" aria-valuemin="1" aria-valuemax="5" aria-valuenow="{{ $latestStage }}" style="height:5px"><div class="progress-bar" style="width:{{ $latestStage * 20 }}%"></div></div>
                            <div class="d-none d-md-flex justify-content-between mt-2 small text-body-secondary"><span>Draft</span><span>Calculate</span><span>Review</span><span>Approve</span><span>Pay</span></div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4">
                        <div class="border rounded-3 p-3 h-100 d-flex flex-column">
                            <div class="small text-uppercase text-body-secondary fw-semibold mb-2">Next action</div><div class="fw-semibold mb-1">{{ $nextAction($latestPeriod->status) }}</div><p class="small text-body-secondary mb-4">Complete the current step before moving this payroll cycle forward.</p>
                            <div class="mt-auto d-grid gap-2">
                                @if(auth()->user()->can('payroll.process') && in_array($latestPeriod->status, ['draft','processing']))
                                    <form method="POST" action="{{ route('payroll.periods.generate', $latestPeriod) }}">@csrf<button class="btn btn-primary btn-sm w-100" type="submit"><i class="fa-solid fa-calculator me-1"></i>Generate payroll</button></form>
                                @endif
                                @if(auth()->user()->can('payroll.approve') && $latestPeriod->status === 'awaiting_approval')
                                    <a class="btn btn-outline-primary btn-sm" href="{{ route('payroll.review') }}"><i class="fa-solid fa-list-check me-1"></i>Review exceptions first</a>
                                    <form method="POST" action="{{ route('payroll.periods.approve', $latestPeriod) }}">@csrf<button class="btn btn-primary btn-sm w-100" type="submit"><i class="fa-solid fa-check me-1"></i>Approve payroll</button></form>
                                @endif
                                @if(auth()->user()->can('payroll.process') && $latestPeriod->status === 'approved')
                                    <button class="btn btn-primary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#pay{{ $latestPeriod->id }}"><i class="fa-solid fa-money-bill-transfer me-1"></i>Record payment</button>
                                @endif
                                @if(auth()->user()->can('payroll.approve') && $latestPeriod->status === 'paid')
                                    <button class="btn btn-outline-secondary btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#closePayroll{{ $latestPeriod->id }}"><i class="fa-solid fa-lock me-1"></i>Close payroll cycle</button>
                                @endif
                                @if($latestPeriod->status === 'closed')
                                    <div class="small text-success fw-semibold"><i class="fa-solid fa-lock me-1"></i>Closed {{ $latestPeriod->closed_at?->format('d M Y H:i') }}</div>
                                    @if(auth()->user()->hasAnyRole(['Owner','Super Admin']) && auth()->user()->can('payroll.approve'))
                                        <button class="btn btn-outline-warning btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#reopenPayroll{{ $latestPeriod->id }}"><i class="fa-solid fa-lock-open me-1"></i>Reopen closed cycle</button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <section class="profile-card reference-list mb-0" data-list-container>
        <div class="profile-card-header flex-wrap gap-2"><div><h2 class="profile-card-title"><i class="fa-solid fa-clock-rotate-left text-primary"></i><span>Payroll history</span></h2><div class="small text-body-secondary mt-1">Recent cycles, amounts and workflow status.</div></div><span class="status-counter">{{ $periods->total() }}</span></div>
        <div class="profile-card-body p-0">
            @if($periods->count())
                <div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th class="ps-3">Payroll period</th><th>Status</th><th>Employees</th><th>Net payroll</th><th>Pay date</th><th class="text-end pe-3">Action</th></tr></thead><tbody>
                    @foreach($periods as $period)
                        @php([$label, $tone, $icon] = $statusMeta($period->status))
                        <tr>
                            <td class="ps-3"><div class="fw-semibold">{{ $period->name }}</div><div class="small text-body-secondary">{{ $period->start_date->format('d M Y') }} – {{ $period->end_date->format('d M Y') }}</div></td>
                            <td><span class="d-inline-flex align-items-center gap-2 small fw-semibold {{ $tone }}"><span class="rounded-circle" style="width:7px;height:7px;background:currentColor"></span>{{ $label }}</span></td>
                            <td><span class="fw-semibold">{{ $period->items_count }}</span><span class="small text-body-secondary ms-1">staff</span></td>
                            <td><span class="fw-semibold">${{ number_format((float) $period->items_sum_net_salary, 2) }}</span></td>
                            <td><span class="small">{{ $period->payment_date?->format('d M Y') ?? '—' }}</span></td>
                            <td class="text-end pe-3"><div class="d-inline-flex align-items-center gap-1">
                                @if(auth()->user()->can('payroll.process') && in_array($period->status, ['draft','processing']))
                                    <form method="POST" action="{{ route('payroll.periods.generate', $period) }}">@csrf<button class="btn btn-action-link btn-sm" type="submit" title="Generate payroll"><i class="fa-solid fa-calculator"></i></button></form>
                                @elseif(auth()->user()->can('payroll.approve') && $period->status === 'awaiting_approval')
                                    <form method="POST" action="{{ route('payroll.periods.approve', $period) }}">@csrf<button class="btn btn-action-link btn-sm text-primary" type="submit" title="Approve payroll"><i class="fa-solid fa-check"></i></button></form>
                                @elseif(auth()->user()->can('payroll.process') && $period->status === 'approved')
                                    <button class="btn btn-action-link btn-sm text-primary" type="button" data-bs-toggle="modal" data-bs-target="#pay{{ $period->id }}" title="Record payment"><i class="fa-solid fa-money-bill-transfer"></i></button>
                                @elseif(auth()->user()->can('payroll.approve') && $period->status === 'paid')
                                    <button class="btn btn-action-link btn-sm" type="button" data-bs-toggle="modal" data-bs-target="#closePayroll{{ $period->id }}" title="Close payroll"><i class="fa-solid fa-lock"></i></button>
                                @elseif(auth()->user()->hasAnyRole(['Owner','Super Admin']) && auth()->user()->can('payroll.approve') && $period->status === 'closed')
                                    <button class="btn btn-action-link btn-sm text-warning" type="button" data-bs-toggle="modal" data-bs-target="#reopenPayroll{{ $period->id }}" title="Reopen payroll"><i class="fa-solid fa-lock-open"></i></button>
                                @else
                                    <span class="small text-body-secondary"><i class="fa-solid {{ $icon }}"></i></span>
                                @endif
                            </div></td>
                        </tr>
                    @endforeach
                </tbody></table></div>
                <div class="p-3 border-top"><x-pagination-footer :paginator="$periods" /></div>
            @else
                <x-empty-state icon="fa-money-check-dollar" title="No payroll cycles found" message="Create a payroll period or adjust the filters to continue." class="py-5" />
            @endif
        </div>
    </section>

    @can('payroll.edit')
        <div class="modal fade" id="createPeriod" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg"><form class="modal-content" method="POST" action="{{ route('payroll.periods.store') }}">@csrf<div class="modal-header"><div><h2 class="modal-title fs-5"><i class="fa-solid fa-money-check-dollar me-2 text-primary"></i>Create payroll period</h2><div class="small text-body-secondary mt-1">Define the pay window and lock the tax exchange-rate evidence for this cycle.</div></div><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body row g-3"><div class="col-md-6"><label class="form-label">Period name <span class="text-danger">*</span></label><input class="form-control" name="name" required value="Payroll {{ now()->format('m/Y') }}"></div><div class="col-md-6"><label class="form-label">Payment date</label><input class="form-control" type="date" name="payment_date" value="{{ now()->endOfMonth()->toDateString() }}"></div><div class="col-md-6"><label class="form-label">Start date <span class="text-danger">*</span></label><input class="form-control" type="date" name="start_date" required value="{{ now()->startOfMonth()->toDateString() }}"></div><div class="col-md-6"><label class="form-label">End date <span class="text-danger">*</span></label><input class="form-control" type="date" name="end_date" required value="{{ now()->endOfMonth()->toDateString() }}"></div><div class="col-md-4"><label class="form-label">KHR per USD <span class="text-danger">*</span></label><input class="form-control" type="number" step="0.01" min="1" name="tax_exchange_rate_khr" required value="{{ $payrollSettings->khr_per_usd }}"></div><div class="col-md-4"><label class="form-label">Rate date <span class="text-danger">*</span></label><input class="form-control" type="date" name="tax_rate_date" required value="{{ now()->toDateString() }}"></div><div class="col-md-4"><label class="form-label">Rate source <span class="text-danger">*</span></label><input class="form-control" type="url" name="tax_rate_source" required value="https://www.tax.gov.kh/en/exchange-rate"></div><div class="col-12"><label class="form-label">Internal notes</label><textarea class="form-control" name="notes" rows="2" placeholder="Optional payroll remarks or processing notes"></textarea></div></div><x-form-save-actions save-label="Create payroll period" /></form></div></div>
    @endcan

    @can('payroll.process')
        @foreach($periods as $period)
            @if($period->status === 'approved')
                <div class="modal fade" id="pay{{ $period->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('payroll.periods.pay', $period) }}">@csrf<div class="modal-header"><div><h2 class="modal-title fs-5"><i class="fa-solid fa-money-bill-transfer me-2 text-primary"></i>Record payment</h2><div class="small text-body-secondary mt-1">{{ $period->name }} · ${{ number_format((float) $period->items_sum_net_salary, 2) }}</div></div><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><label class="form-label">Payment method <span class="text-danger">*</span></label><select class="form-select" name="payment_method" required><option value="bank_transfer">Bank transfer</option><option value="cash">Cash</option><option value="cheque">Cheque</option><option value="mobile_banking">Mobile banking</option><option value="other">Other</option></select><label class="form-label mt-3">Disbursed at <span class="text-danger">*</span></label><input class="form-control" type="datetime-local" name="paid_at" required value="{{ now()->format('Y-m-d\TH:i') }}"><label class="form-label mt-3">Reference / transaction ID</label><input class="form-control" name="reference_number" maxlength="100" placeholder="Bank or transaction reference"><label class="form-label mt-3">Notes</label><textarea class="form-control" name="notes" rows="2" placeholder="Optional disbursement note"></textarea></div><x-form-save-actions save-label="Record payment" /></form></div></div>
            @endif
        @endforeach
    @endcan

    @can('payroll.approve')
        @foreach($periods as $period)
            @if($period->status === 'paid')
                <div class="modal fade" id="closePayroll{{ $period->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('payroll.periods.approve', $period) }}">@csrf<input type="hidden" name="action" value="close"><div class="modal-header"><div><h2 class="modal-title fs-5"><i class="fa-solid fa-lock me-2"></i>Close payroll cycle</h2><div class="small text-body-secondary mt-1">{{ $period->name }} will become accounting-locked.</div></div><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="alert alert-warning small">Close only after salary disbursement and reconciliation are complete. Attendance and approved leave in this period remain locked.</div><label class="form-label">Closing reason <span class="text-danger">*</span></label><textarea class="form-control" name="reason" required minlength="5" maxlength="1000" rows="3" placeholder="Example: Bank transfer reconciled and payroll reviewed against final register."></textarea></div><div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Cancel</button><button class="btn btn-secondary" type="submit"><i class="fa-solid fa-lock me-1"></i>Close payroll</button></div></form></div></div>
            @endif
            @if($period->status === 'closed' && auth()->user()->hasAnyRole(['Owner','Super Admin']))
                <div class="modal fade" id="reopenPayroll{{ $period->id }}" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('payroll.periods.approve', $period) }}">@csrf<input type="hidden" name="action" value="reopen"><div class="modal-header"><div><h2 class="modal-title fs-5"><i class="fa-solid fa-lock-open me-2 text-warning"></i>Reopen closed payroll</h2><div class="small text-body-secondary mt-1">The cycle returns to Paid. The original payment record is not changed or deleted.</div></div><button class="btn-close" type="button" data-bs-dismiss="modal"></button></div><div class="modal-body"><div class="alert alert-warning small">Use this exceptional action only when post-close reconciliation requires correction. Reopening is fully auditable.</div><label class="form-label">Reopen reason <span class="text-danger">*</span></label><textarea class="form-control" name="reason" required minlength="5" maxlength="1000" rows="3" placeholder="Explain why this closed payroll must be reopened."></textarea></div><div class="modal-footer"><button class="btn btn-light" type="button" data-bs-dismiss="modal">Keep closed</button><button class="btn btn-warning" type="submit"><i class="fa-solid fa-lock-open me-1"></i>Reopen payroll</button></div></form></div></div>
            @endif
        @endforeach
    @endcan
</x-layouts::app>
