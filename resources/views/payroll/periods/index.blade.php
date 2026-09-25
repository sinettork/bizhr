<x-layouts::app title="Payroll">
    @php
        $statusMeta = fn (string $status) => match ($status) {
            'draft' => ['Draft Cycle', 'text-secondary', 'fa-pen-ruler', 'bg-secondary-subtle'],
            'processing' => ['Calculating Pay', 'text-primary', 'fa-calculator', 'bg-primary-subtle'],
            'awaiting_approval' => ['Needs Executive Sign-off', 'text-warning', 'fa-circle-check', 'bg-warning-subtle'],
            'approved' => ['Ready for Disbursement', 'text-primary', 'fa-money-bill-transfer', 'bg-primary-subtle'],
            'paid' => ['Disbursed & Paid', 'text-success', 'fa-circle-check', 'bg-success-subtle'],
            'closed' => ['Closed & Locked', 'text-secondary', 'fa-lock', 'bg-body-secondary'],
            default => [str($status)->replace('_', ' ')->title(), 'text-secondary', 'fa-circle', 'bg-body-secondary'],
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
            'draft' => 'Generate gross-to-net payroll calculations',
            'processing' => 'Recalculate after resolving payroll inputs & overtime',
            'awaiting_approval' => 'Audit exceptions and grant management sign-off',
            'approved' => 'Record salary disbursement and payment reference',
            'paid' => 'Audit registers and close accounting period',
            'closed' => 'Cycle closed, locked and archived',
            default => 'Review payroll cycle',
        };
    @endphp

    <x-workspace-command-bar title="Payroll Control Center" icon="fa-money-check-dollar" context="Payroll &amp; Compensation Governance">
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
                @if(request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('payroll.periods.index') }}" class="btn btn-action-link btn-sm"><i class="fa-solid fa-rotate-left"></i><span>Clear</span></a>
                @endif
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
                    <button class="btn btn-primary btn-sm px-3" type="button" data-bs-toggle="modal" data-bs-target="#createPeriod"><i class="fa-solid fa-plus me-1"></i>New payroll period</button>
                @endcan
            </div>
        </x-slot:actions>
    </x-workspace-command-bar>

    @if(session('status'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-3"><i class="fa-solid fa-circle-check"></i><span>{{ session('status') }}</span></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3"><i class="fa-solid fa-circle-exclamation"></i><span>{{ $errors->first() }}</span></div>
    @endif

    {{-- Financial Health & Operations Summary Strip --}}
    <section class="row g-3 mb-4" aria-label="Payroll summary">
        <div class="col-6 col-xl-3">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-body p-3">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <div class="small text-body-secondary fw-semibold text-uppercase">Open Cycles</div>
                            <div class="fs-4 fw-bold text-dark mt-1">{{ $summary['open_cycles'] }}</div>
                        </div>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-body-tertiary text-primary" style="width:36px;height:36px">
                            <i class="fa-solid fa-rotate"></i>
                        </span>
                    </div>
                    <div class="small text-body-secondary mt-2">Active payroll workflows</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-body p-3">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <div class="small text-body-secondary fw-semibold text-uppercase">Needs Approval</div>
                            <div class="fs-4 fw-bold {{ $summary['needs_approval'] > 0 ? 'text-warning' : 'text-dark' }} mt-1">{{ $summary['needs_approval'] }}</div>
                        </div>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-warning-subtle text-warning" style="width:36px;height:36px">
                            <i class="fa-solid fa-user-check"></i>
                        </span>
                    </div>
                    <div class="small text-body-secondary mt-2">Awaiting authorized sign-off</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <div class="profile-card h-100 mb-0">
                <div class="profile-card-body p-3">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <div class="small text-body-secondary fw-semibold text-uppercase">Ready to Pay</div>
                            <div class="fs-4 fw-bold {{ $summary['awaiting_payment'] > 0 ? 'text-primary' : 'text-dark' }} mt-1">{{ $summary['awaiting_payment'] }}</div>
                        </div>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-primary-subtle text-primary" style="width:36px;height:36px">
                            <i class="fa-solid fa-money-bill-transfer"></i>
                        </span>
                    </div>
                    <div class="small text-body-secondary mt-2">Approved cycles ready for payout</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-xl-3">
            <a class="profile-card h-100 mb-0 d-block text-decoration-none text-reset" href="{{ route('payroll.review') }}">
                <div class="profile-card-body p-3">
                    <div class="d-flex align-items-start justify-content-between gap-3">
                        <div>
                            <div class="small text-body-secondary fw-semibold text-uppercase">Exceptions Queue</div>
                            <div class="fs-4 fw-bold {{ $summary['exceptions'] > 0 ? 'text-danger' : 'text-success' }} mt-1">{{ $summary['exceptions'] }}</div>
                        </div>
                        <span class="d-inline-flex align-items-center justify-content-center rounded-3 {{ $summary['exceptions'] > 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}" style="width:36px;height:36px">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </span>
                    </div>
                    <div class="small {{ $summary['exceptions'] > 0 ? 'text-danger fw-semibold' : 'text-body-secondary' }} mt-2">
                        {{ $summary['exceptions'] > 0 ? 'Resolve items before sign-off' : 'All payroll items clear' }}
                    </div>
                </div>
            </a>
        </div>
    </section>

    {{-- Active Payroll Period Hero: Linear Stage-Gate Stepper (Archetype B) --}}
    @if($latestPeriod)
        @php([$latestStatusLabel, $latestStatusTone, $latestStatusIcon, $latestBgTone] = $statusMeta($latestPeriod->status))
        @php($latestStage = $stage($latestPeriod->status))
        <section class="profile-card mb-4 border-top border-3 border-primary" aria-labelledby="current-payroll-heading">
            <div class="profile-card-header flex-wrap gap-2 py-3 px-4 bg-white border-bottom">
                <div>
                    <div class="small text-uppercase text-body-secondary fw-semibold">Active Payroll Workflow</div>
                    <h2 class="profile-card-title fs-5 fw-bold mb-0 text-dark" id="current-payroll-heading">
                        <i class="fa-solid fa-calendar-days text-primary me-2"></i><span>{{ $latestPeriod->name }}</span>
                    </h2>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge {{ $latestBgTone }} {{ $latestStatusTone }} border px-3 py-2 fs-6">
                        <i class="fa-solid {{ $latestStatusIcon }} me-1"></i>{{ $latestStatusLabel }}
                    </span>
                </div>
            </div>

            <div class="profile-card-body p-3 p-lg-4">
                <div class="row g-4 align-items-stretch">
                    {{-- Left Column: Financials & Stepper --}}
                    <div class="col-12 col-xl-8">
                        <div class="d-flex flex-column flex-md-row justify-content-between gap-3 p-3 bg-body-tertiary rounded-3 border mb-3">
                            <div>
                                <div class="small text-body-secondary fw-semibold text-uppercase">Pay Window</div>
                                <div class="fw-bold text-dark fs-6 mt-1">{{ $latestPeriod->start_date->format('d M Y') }} – {{ $latestPeriod->end_date->format('d M Y') }}</div>
                                <div class="small text-body-secondary mt-1">
                                    <i class="fa-regular fa-calendar-check me-1 text-primary"></i>Scheduled Disbursal: <strong>{{ $latestPeriod->payment_date?->format('d M Y') ?? 'Not scheduled' }}</strong>
                                </div>
                            </div>
                            <div class="text-md-end">
                                <div class="small text-body-secondary fw-semibold text-uppercase">Estimated Net Payroll</div>
                                <div class="fs-3 fw-bold text-primary">${{ number_format((float) $latestPeriod->items_sum_net_salary, 2) }}</div>
                                <div class="small text-body-secondary">{{ $latestPeriod->items_count }} employee payroll records</div>
                            </div>
                        </div>

                        {{-- Exception Warning Callout --}}
                        @if($summary['exceptions'] > 0 && in_array($latestPeriod->status, ['draft', 'processing', 'awaiting_approval']))
                            <div class="alert alert-warning d-flex align-items-center justify-content-between gap-2 p-2 px-3 mb-3">
                                <div class="d-flex align-items-center gap-2 small">
                                    <i class="fa-solid fa-triangle-exclamation text-warning fs-6"></i>
                                    <span><strong>{{ $summary['exceptions'] }} exceptions detected</strong> (unapproved overtime or input anomalies). Verify these before final sign-off.</span>
                                </div>
                                <a href="{{ route('payroll.review') }}" class="btn btn-sm btn-outline-dark text-nowrap py-0 px-2" style="font-size: .78rem;">
                                    Audit exceptions
                                </a>
                            </div>
                        @endif

                        {{-- Linear Process Stepper --}}
                        <div class="border rounded-3 p-3 bg-white">
                            <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                                <div>
                                    <div class="small text-uppercase text-body-secondary fw-semibold">Stage-Gate Progress</div>
                                    <div class="small fw-bold text-dark mt-1">{{ $nextAction($latestPeriod->status) }}</div>
                                </div>
                                <span class="badge text-bg-light border font-monospace">Step {{ $latestStage }} of 5</span>
                            </div>
                            <div class="progress mb-2" role="progressbar" aria-label="Payroll workflow progress" aria-valuemin="1" aria-valuemax="5" aria-valuenow="{{ $latestStage }}" style="height:6px">
                                <div class="progress-bar bg-primary" style="width:{{ $latestStage * 20 }}%"></div>
                            </div>
                            <div class="d-none d-md-flex justify-content-between small text-body-secondary">
                                <span class="{{ $latestStage >= 1 ? 'fw-bold text-dark' : '' }}">1. Draft</span>
                                <span class="{{ $latestStage >= 2 ? 'fw-bold text-dark' : '' }}">2. Calculate</span>
                                <span class="{{ $latestStage >= 3 ? 'fw-bold text-dark' : '' }}">3. Exceptions Review</span>
                                <span class="{{ $latestStage >= 4 ? 'fw-bold text-dark' : '' }}">4. Management Sign-off</span>
                                <span class="{{ $latestStage >= 5 ? 'fw-bold text-dark' : '' }}">5. Disburse &amp; Lock</span>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Next Action Execution Gate --}}
                    <div class="col-12 col-xl-4">
                        <div class="border rounded-3 p-3 bg-body-tertiary h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="small text-uppercase text-body-secondary fw-semibold mb-1">Required Next Action</div>
                                <div class="fw-bold text-dark fs-6 mb-2">{{ $nextAction($latestPeriod->status) }}</div>
                                <p class="small text-body-secondary mb-3">
                                    Each stage must be completed sequentially to maintain audit compliance and prevent payroll calculation errors.
                                </p>
                            </div>

                            <div class="d-grid gap-2">
                                @if(auth()->user()->can('payroll.process') && in_array($latestPeriod->status, ['draft','processing']))
                                    <form method="POST" action="{{ route('payroll.periods.generate', $latestPeriod) }}">
                                        @csrf
                                        <button class="btn btn-primary btn-sm w-100 py-2" type="submit">
                                            <i class="fa-solid fa-calculator me-1"></i>Generate Payroll Run
                                        </button>
                                    </form>
                                @endif

                                @if(auth()->user()->can('payroll.approve') && $latestPeriod->status === 'awaiting_approval')
                                    <form method="POST" action="{{ route('payroll.periods.approve', $latestPeriod) }}">
                                        @csrf
                                        <button class="btn btn-primary btn-sm w-100 py-2" type="submit" data-confirm="Grant executive sign-off for this payroll cycle? This authorizes disbursement.">
                                            <i class="fa-solid fa-check me-1"></i>Approve &amp; Authorize Payroll
                                        </button>
                                    </form>
                                @endif

                                @if(auth()->user()->can('payroll.process') && $latestPeriod->status === 'approved')
                                    <button class="btn btn-primary btn-sm w-100 py-2" type="button" data-bs-toggle="modal" data-bs-target="#pay{{ $latestPeriod->id }}">
                                        <i class="fa-solid fa-money-bill-transfer me-1"></i>Record Bank Disbursement
                                    </button>
                                @endif

                                @if(auth()->user()->can('payroll.approve') && $latestPeriod->status === 'paid')
                                    <button class="btn btn-outline-secondary btn-sm w-100 py-2" type="button" data-bs-toggle="modal" data-bs-target="#closePayroll{{ $latestPeriod->id }}">
                                        <i class="fa-solid fa-lock me-1"></i>Close &amp; Lock Period
                                    </button>
                                @endif

                                @if($latestPeriod->status === 'closed')
                                    <div class="small text-success fw-semibold p-2 bg-success-subtle rounded border border-success-subtle text-center">
                                        <i class="fa-solid fa-lock me-1"></i>Period closed on {{ $latestPeriod->closed_at?->format('d M Y H:i') }}
                                    </div>
                                    @if(auth()->user()->hasAnyRole(['Owner','Super Admin']) && auth()->user()->can('payroll.approve'))
                                        <button class="btn btn-outline-warning btn-sm w-100 mt-2" type="button" data-bs-toggle="modal" data-bs-target="#reopenPayroll{{ $latestPeriod->id }}">
                                            <i class="fa-solid fa-lock-open me-1"></i>Reopen Closed Period
                                        </button>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- Historical Payroll Cycles Ledger --}}
    <section class="profile-card reference-list mb-0" data-list-container>
        <div class="profile-card-header flex-wrap gap-2 py-3 px-4 bg-white border-bottom">
            <div>
                <h2 class="profile-card-title fs-6 fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-clock-rotate-left text-primary me-2"></i><span>Payroll Cycle History</span>
                </h2>
                <div class="small text-body-secondary mt-1">Previous payroll runs, disbursed totals, and audit records.</div>
            </div>
            <span class="badge text-bg-light border font-monospace">{{ $periods->total() }} total</span>
        </div>

        <div class="profile-card-body p-0">
            @if($periods->count())
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Payroll Cycle</th>
                                <th>Status</th>
                                <th>Employees</th>
                                <th>Net Payroll</th>
                                <th>Payment Date</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($periods as $period)
                                @php([$label, $tone, $icon, $bgTone] = $statusMeta($period->status))
                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-bold text-dark">{{ $period->name }}</div>
                                        <div class="small text-body-secondary font-monospace">{{ $period->start_date->format('d M Y') }} – {{ $period->end_date->format('d M Y') }}</div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $bgTone }} {{ $tone }} border font-monospace">
                                            <i class="fa-solid {{ $icon }} me-1"></i>{{ $label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">{{ $period->items_count }}</span> <span class="small text-body-secondary">staff</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark">${{ number_format((float) $period->items_sum_net_salary, 2) }}</span>
                                    </td>
                                    <td>
                                        <span class="small text-body-secondary">{{ $period->payment_date?->format('d M Y') ?? '—' }}</span>
                                    </td>
                                    <td class="text-end pe-3 text-nowrap">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            @if(auth()->user()->can('payroll.process') && in_array($period->status, ['draft','processing']))
                                                <form method="POST" action="{{ route('payroll.periods.generate', $period) }}">
                                                    @csrf
                                                    <button class="btn btn-action-link btn-sm text-primary" type="submit" title="Calculate payroll"><i class="fa-solid fa-calculator"></i><span>Calculate</span></button>
                                                </form>
                                            @elseif(auth()->user()->can('payroll.approve') && $period->status === 'awaiting_approval')
                                                <form method="POST" action="{{ route('payroll.periods.approve', $period) }}">
                                                    @csrf
                                                    <button class="btn btn-action-link btn-sm text-primary" type="submit" title="Approve payroll"><i class="fa-solid fa-check"></i><span>Approve</span></button>
                                                </form>
                                            @elseif(auth()->user()->can('payroll.process') && $period->status === 'approved')
                                                <button class="btn btn-action-link btn-sm text-primary" type="button" data-bs-toggle="modal" data-bs-target="#pay{{ $period->id }}" title="Record payment"><i class="fa-solid fa-money-bill-transfer"></i><span>Pay</span></button>
                                            @elseif(auth()->user()->can('payroll.approve') && $period->status === 'paid')
                                                <button class="btn btn-action-link btn-sm text-secondary" type="button" data-bs-toggle="modal" data-bs-target="#closePayroll{{ $period->id }}" title="Close payroll"><i class="fa-solid fa-lock"></i><span>Close</span></button>
                                            @elseif(auth()->user()->hasAnyRole(['Owner','Super Admin']) && auth()->user()->can('payroll.approve') && $period->status === 'closed')
                                                <button class="btn btn-action-link btn-sm text-warning" type="button" data-bs-toggle="modal" data-bs-target="#reopenPayroll{{ $period->id }}" title="Reopen payroll"><i class="fa-solid fa-lock-open"></i><span>Reopen</span></button>
                                            @else
                                                <span class="small text-body-secondary"><i class="fa-solid {{ $icon }}"></i></span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top"><x-pagination-footer :paginator="$periods" /></div>
            @else
                <x-empty-state icon="fa-money-check-dollar" title="No payroll cycles found" message="Create a payroll period or adjust the filters to continue." class="py-5" />
            @endif
        </div>
    </section>

    {{-- Modals for Creating Period, Recording Payment, and Closing/Reopening --}}
    @can('payroll.edit')
        <div class="modal fade" id="createPeriod" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <form class="modal-content" method="POST" action="{{ route('payroll.periods.store') }}">
                    @csrf
                    <div class="modal-header bg-body-tertiary py-3 px-4 border-bottom">
                        <div>
                            <h5 class="modal-title fs-6 fw-bold text-dark d-flex align-items-center">
                                <i class="fa-solid fa-money-check-dollar me-2 text-primary"></i>Create New Payroll Period
                            </h5>
                            <div class="small text-body-secondary mt-1">Define the pay window and lock the statutory tax exchange-rate evidence for this cycle.</div>
                        </div>
                        <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Period Name <span class="text-danger">*</span></label>
                            <input class="form-control" name="name" required value="Payroll {{ now()->format('m/Y') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Scheduled Payment Date</label>
                            <input class="form-control" type="date" name="payment_date" value="{{ now()->endOfMonth()->toDateString() }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Pay Window Start Date <span class="text-danger">*</span></label>
                            <input class="form-control" type="date" name="start_date" required value="{{ now()->startOfMonth()->toDateString() }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-dark">Pay Window End Date <span class="text-danger">*</span></label>
                            <input class="form-control" type="date" name="end_date" required value="{{ now()->endOfMonth()->toDateString() }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">KHR per USD Rate <span class="text-danger">*</span></label>
                            <input class="form-control font-monospace" type="number" step="0.01" min="1" name="tax_exchange_rate_khr" required value="{{ $payrollSettings->khr_per_usd }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Rate Date <span class="text-danger">*</span></label>
                            <input class="form-control" type="date" name="tax_rate_date" required value="{{ now()->toDateString() }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Statutory Rate Source <span class="text-danger">*</span></label>
                            <input class="form-control" type="url" name="tax_rate_source" required value="https://www.tax.gov.kh/en/exchange-rate">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark">Internal Notes</label>
                            <textarea class="form-control" name="notes" rows="2" placeholder="Optional payroll remarks or processing notes"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-body-tertiary py-2 px-4 border-top">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fa-solid fa-plus me-1"></i>Create Period</button>
                    </div>
                </form>
            </div>
        </div>
    @endcan

    @can('payroll.process')
        @foreach($periods as $period)
            @if($period->status === 'approved')
                <div class="modal fade" id="pay{{ $period->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form class="modal-content" method="POST" action="{{ route('payroll.periods.pay', $period) }}">
                            @csrf
                            <div class="modal-header bg-body-tertiary py-3 px-4 border-bottom">
                                <div>
                                    <h5 class="modal-title fs-6 fw-bold text-dark d-flex align-items-center">
                                        <i class="fa-solid fa-money-bill-transfer me-2 text-primary"></i>Record Salary Disbursement
                                    </h5>
                                    <div class="small text-body-secondary mt-1">{{ $period->name }} · ${{ number_format((float) $period->items_sum_net_salary, 2) }}</div>
                                </div>
                                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body p-4">
                                <label class="form-label small fw-semibold text-dark">Payment Method <span class="text-danger">*</span></label>
                                <select class="form-select" name="payment_method" required>
                                    <option value="bank_transfer">Bank transfer</option>
                                    <option value="cash">Cash</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="mobile_banking">Mobile banking</option>
                                    <option value="other">Other</option>
                                </select>
                                <label class="form-label small fw-semibold text-dark mt-3">Disbursed At <span class="text-danger">*</span></label>
                                <input class="form-control" type="datetime-local" name="paid_at" required value="{{ now()->format('Y-m-d\TH:i') }}">
                                <label class="form-label small fw-semibold text-dark mt-3">Bank Batch / Transaction ID</label>
                                <input class="form-control" name="reference_number" maxlength="100" placeholder="Bank batch or transaction reference">
                                <label class="form-label small fw-semibold text-dark mt-3">Disbursement Notes</label>
                                <textarea class="form-control" name="notes" rows="2" placeholder="Optional disbursement remarks"></textarea>
                            </div>
                            <div class="modal-footer bg-body-tertiary py-2 px-4 border-top">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fa-solid fa-check me-1"></i>Confirm Disbursement</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @endforeach
    @endcan

    @can('payroll.approve')
        @foreach($periods as $period)
            @if($period->status === 'paid')
                <div class="modal fade" id="closePayroll{{ $period->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form class="modal-content" method="POST" action="{{ route('payroll.periods.approve', $period) }}">
                            @csrf
                            <input type="hidden" name="action" value="close">
                            <div class="modal-header bg-body-tertiary py-3 px-4 border-bottom">
                                <div>
                                    <h5 class="modal-title fs-6 fw-bold text-dark d-flex align-items-center">
                                        <i class="fa-solid fa-lock me-2 text-secondary"></i>Close &amp; Lock Payroll Cycle
                                    </h5>
                                    <div class="small text-body-secondary mt-1">{{ $period->name }} will become accounting-locked.</div>
                                </div>
                                <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="alert alert-warning small">Close only after bank disbursement and final reconciliation are complete. Attendance and leave records in this cycle remain locked.</div>
                                <label class="form-label small fw-semibold text-dark">Closing Reason / Sign-off Note <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="reason" required minlength="5" maxlength="1000" rows="3" placeholder="Example: Bank transfer reconciled against general ledger."></textarea>
                            </div>
                            <div class="modal-footer bg-body-tertiary py-2 px-4 border-top">
                                <button class="btn btn-secondary btn-sm" type="button" data-bs-dismiss="modal">Cancel</button>
                                <button class="btn btn-dark btn-sm px-3" type="submit"><i class="fa-solid fa-lock me-1"></i>Lock Payroll Cycle</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
            @if($period->status === 'closed' && auth()->user()->hasAnyRole(['Owner','Super Admin']))
                <div class="modal fade" id="reopenPayroll{{ $period->id }}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <form class="modal-content" method="POST" action="{{ route('payroll.periods.approve', $period) }}">
                            @csrf
                            <input type="hidden" name="action" value="reopen">
                            <div class="modal-header bg-body-tertiary py-3 px-4 border-bottom">
                                <div>
                                    <h5 class="modal-title fs-6 fw-bold text-dark d-flex align-items-center">
                                        <i class="fa-solid fa-lock-open me-2 text-warning"></i>Reopen Closed Payroll
                                    </h5>
                                    <div class="small text-body-secondary mt-1">Returns cycle to Paid status with full audit trail.</div>
                                </div>
                                <button class="btn-close" type="button" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body p-4">
                                <div class="alert alert-warning small">Use only when post-close audit reconciliation requires critical correction. Reopening is fully auditable.</div>
                                <label class="form-label small fw-semibold text-dark">Reopen Reason <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="reason" required minlength="5" maxlength="1000" rows="3" placeholder="Explain why this closed payroll must be reopened."></textarea>
                            </div>
                            <div class="modal-footer bg-body-tertiary py-2 px-4 border-top">
                                <button class="btn btn-secondary btn-sm" type="button" data-bs-dismiss="modal">Keep Closed</button>
                                <button class="btn btn-warning btn-sm px-3" type="submit"><i class="fa-solid fa-lock-open me-1"></i>Reopen Payroll</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @endforeach
    @endcan
</x-layouts::app>
