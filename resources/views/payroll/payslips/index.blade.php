@php
    $latestPayslip = $items->first();
    $money = static function ($amount, $currency): string {
        return strtoupper((string) $currency) === 'KHR'
            ? number_format((float) $amount, 0).' ៛'
            : '$'.number_format((float) $amount, 2);
    };
@endphp

<x-layouts::app title="My payslips">
    <x-workspace-command-bar title="My Payslips" icon="fa-wallet" context="Personal Workspace">
        <x-slot:actions>
            @can('attendance.checkin')
                <a class="btn btn-action-link btn-sm" href="{{ route('attendance.checkinout') }}"><i class="fa-solid fa-user-clock"></i><span>Attendance</span></a>
            @endcan
        </x-slot:actions>
    </x-workspace-command-bar>

    @if($latestPayslip)
        @php
            $latestDeductions = (float) $latestPayslip->deduction_amount
                + (float) $latestPayslip->loan_deduction
                + (float) $latestPayslip->advance_deduction
                + (float) $latestPayslip->tax_amount
                + (float) $latestPayslip->nssf_employee_amount;
            $paymentTone = $latestPayslip->payment_status === 'paid' ? 'success' : 'warning';
        @endphp

        <div class="row g-3 mb-3">
            <div class="col-12 col-xl-5">
                <section class="profile-card h-100 mb-0">
                    <div class="profile-card-header">
                        <div>
                            <div class="small text-body-secondary">Latest payslip</div>
                            <h2 class="profile-card-title mb-0"><i class="fa-solid fa-money-check-dollar text-primary"></i><span>{{ $latestPayslip->period?->name ?? 'Payroll period' }}</span></h2>
                        </div>
                        <span class="status-text text-bg-{{ $paymentTone }}">{{ str($latestPayslip->payment_status)->replace('_', ' ')->title() }}</span>
                    </div>
                    <div class="profile-card-body">
                        <div class="small text-body-secondary">Net pay</div>
                        <div class="display-6 fw-bold text-dark mt-1">{{ $money($latestPayslip->net_salary, $latestPayslip->currency) }}</div>
                        <div class="small text-body-secondary mt-2">
                            {{ $latestPayslip->period?->start_date?->format('d M Y') }} – {{ $latestPayslip->period?->end_date?->format('d M Y') }}
                        </div>
                        <div class="alert alert-light border small mt-3 mb-0">
                            @if($latestPayslip->payment_status === 'paid')
                                <i class="fa-solid fa-circle-check text-success me-1"></i>Your payroll has been marked paid for this period.
                            @else
                                <i class="fa-solid fa-clock text-warning me-1"></i>This payslip is approved but payment has not yet been marked paid.
                            @endif
                        </div>
                    </div>
                </section>
            </div>

            <div class="col-12 col-xl-7">
                <div class="row g-2 h-100">
                    @foreach([
                        ['Base pay', $money($latestPayslip->base_salary, $latestPayslip->currency), 'fa-coins', 'primary'],
                        ['Overtime', number_format((float) $latestPayslip->overtime_hours, 2).' h', 'fa-clock', 'info'],
                        ['Tax & deductions', $money($latestDeductions, $latestPayslip->currency), 'fa-receipt', 'warning'],
                        ['Payslips available', number_format($items->total()), 'fa-file-invoice-dollar', 'secondary'],
                    ] as [$label, $value, $icon, $tone])
                        <div class="col-6">
                            <section class="profile-card h-100 mb-0">
                                <div class="profile-card-body py-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="metric-icon bg-{{ $tone }}-subtle text-{{ $tone }}"><i class="fa-solid {{ $icon }}"></i></span>
                                        <div class="min-w-0">
                                            <div class="small text-body-secondary">{{ $label }}</div>
                                            <div class="fw-bold text-dark text-truncate">{{ $value }}</div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <div class="d-flex align-items-end justify-content-between gap-2 mb-2">
        <div>
            <div class="small text-uppercase text-body-secondary fw-semibold">Payroll history</div>
            <h2 class="h6 fw-bold mb-0">Previous payslips</h2>
        </div>
        <div class="small text-body-secondary">Only approved, paid, or closed payroll periods are shown.</div>
    </div>

    <div class="reference-list" data-list-container>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Period</th>
                        <th>Base pay</th>
                        <th>Overtime</th>
                        <th>Tax & deductions</th>
                        <th>Net pay</th>
                        <th class="pe-3">Payment</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php
                            $deductions = (float) $item->deduction_amount
                                + (float) $item->loan_deduction
                                + (float) $item->advance_deduction
                                + (float) $item->tax_amount
                                + (float) $item->nssf_employee_amount;
                        @endphp
                        <tr>
                            <td class="ps-3 fw-semibold">
                                {{ $item->period?->name }}
                                <small class="d-block text-body-secondary">{{ $item->period?->start_date?->format('d M Y') }} – {{ $item->period?->end_date?->format('d M Y') }}</small>
                            </td>
                            <td>{{ $money($item->base_salary, $item->currency) }}</td>
                            <td>{{ number_format((float) $item->overtime_hours, 2) }} h</td>
                            <td>{{ $money($deductions, $item->currency) }}</td>
                            <td class="fw-bold text-dark">{{ $money($item->net_salary, $item->currency) }}</td>
                            <td class="pe-3">
                                <span class="badge text-bg-{{ $item->payment_status === 'paid' ? 'success' : 'warning' }}">{{ str($item->payment_status)->replace('_', ' ')->title() }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-0">
                                <x-empty-state class="py-5 px-3" icon="fa-wallet" title="No payslips yet" message="Your approved payslips will appear here after payroll is processed." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$items" />
    </div>
</x-layouts::app>
