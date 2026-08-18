<x-layouts::app title="My payslips">
    <x-workspace-command-bar title="My payslips" icon="fa-wallet" context="Payroll" />

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
                        <th>Payment</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        @php($deductions = (float) $item->deduction_amount + (float) $item->loan_deduction + (float) $item->advance_deduction + (float) $item->tax_amount + (float) $item->nssf_employee_amount)
                        <tr>
                            <td class="ps-3 fw-semibold">
                                {{ $item->period?->name }}
                                <small class="d-block text-body-secondary">{{ $item->period?->start_date?->format('d/m/Y') }} – {{ $item->period?->end_date?->format('d/m/Y') }}</small>
                            </td>
                            <td>{{ strtoupper($item->currency) === 'KHR' ? number_format($item->base_salary, 0).' ៛' : '$'.number_format($item->base_salary, 2) }}</td>
                            <td>{{ number_format($item->overtime_hours, 2) }} h</td>
                            <td>{{ strtoupper($item->currency) === 'KHR' ? number_format($deductions, 0).' ៛' : '$'.number_format($deductions, 2) }}</td>
                            <td class="fw-semibold">{{ strtoupper($item->currency) === 'KHR' ? number_format($item->net_salary, 0).' ៛' : '$'.number_format($item->net_salary, 2) }}</td>
                            <td><span class="badge text-bg-{{ $item->payment_status === 'paid' ? 'success' : 'secondary' }}">{{ $item->payment_status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-5 text-center text-body-secondary">No approved payslips yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <x-pagination-footer :paginator="$items" />
    </div>
</x-layouts::app>
