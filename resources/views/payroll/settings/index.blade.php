<x-layouts::app title="Payroll settings">
    <x-workspace-command-bar title="Payroll settings" icon="fa-sliders" context="Payroll configuration" />
    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif
    <div class="card shadow-sm border-0"><form method="POST" action="{{ route('payroll.settings.update') }}">@csrf @method('PUT')<div class="card-body row g-3"><div class="col-md-3"><label class="form-label">KHR per USD</label><input class="form-control" type="number" step=".01" name="khr_per_usd" value="{{ $settings->khr_per_usd }}" required></div><div class="col-md-3"><label class="form-label">Working days/month</label><input class="form-control" type="number" name="working_days_per_month" value="{{ $settings->working_days_per_month }}" required></div><div class="col-md-3"><label class="form-label">Hours/day</label><input class="form-control" type="number" step=".25" name="hours_per_day" value="{{ $settings->hours_per_day }}" required></div><div class="col-md-3"><label class="form-label">Overtime multiplier</label><input class="form-control" type="number" step=".01" name="default_overtime_multiplier" value="{{ $settings->default_overtime_multiplier }}" required></div><div class="col-md-3"><label class="form-label">Dependent relief (KHR)</label><input class="form-control" type="number" name="dependent_relief_khr" value="{{ $settings->dependent_relief_khr }}" required></div><div class="col-md-3"><label class="form-label">NSSF employee health %</label><input class="form-control" type="number" step=".01" name="nssf_employee_health_rate" value="{{ $settings->nssf_employee_health_rate }}" required></div><div class="col-md-3"><label class="form-label">NSSF employer health %</label><input class="form-control" type="number" step=".01" name="nssf_employer_health_rate" value="{{ $settings->nssf_employer_health_rate }}" required></div><div class="col-md-3"><label class="form-label">NSSF employer risk %</label><input class="form-control" type="number" step=".01" name="nssf_employer_risk_rate" value="{{ $settings->nssf_employer_risk_rate }}" required></div><div class="col-12 d-flex flex-wrap gap-4"><input type="hidden" name="require_overtime_approval" value="0"><label class="form-check"><input class="form-check-input" type="checkbox" name="require_overtime_approval" value="1" @checked($settings->require_overtime_approval)><span class="form-check-label">Require overtime approval</span></label><input type="hidden" name="deduct_unpaid_absence" value="0"><label class="form-check"><input class="form-check-input" type="checkbox" name="deduct_unpaid_absence" value="1" @checked($settings->deduct_unpaid_absence)><span class="form-check-label">Deduct unpaid absence</span></label><input type="hidden" name="salary_tax_enabled" value="0"><label class="form-check"><input class="form-check-input" type="checkbox" name="salary_tax_enabled" value="1" @checked($settings->salary_tax_enabled)><span class="form-check-label">Calculate salary tax</span></label></div></div><div class="card-footer bg-white text-end">
    @if(auth()->user()->can('payroll.approve'))
        <button class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save settings</button>
    @endif
    </div></form></div>
    <div class="reference-list mt-3"><div class="reference-list-toolbar"><strong>Public holidays</strong><span class="small text-body-secondary">Use this list to verify upcoming payroll calculations.</span></div><div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead><tr><th class="ps-3">Date</th><th>Holiday</th><th>Pay treatment</th></tr></thead><tbody>
    @forelse($holidays as $holiday)
        <tr><td class="ps-3">{{ $holiday->holiday_date->format('d/m/Y') }}</td><td>{{ $holiday->name }}</td><td>{{ $holiday->is_paid ? 'Paid holiday' : 'Unpaid holiday' }}</td></tr>
    @empty
        <tr><td colspan="3" class="py-4 text-center text-body-secondary">No public holidays configured.</td></tr>
    @endforelse
    </tbody></table></div></div>
</x-layouts::app>
