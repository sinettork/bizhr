<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\PayrollItem;
use App\Models\PayrollPeriod;
use App\Models\PayrollSetting;
use App\Models\PublicHoliday;
use App\Services\PayrollCalculatorService;
use App\Services\PayrollWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function periods(Request $request): View
    {
        $companyId = $this->companyId();
        $query = PayrollPeriod::query()->where('company_id', $companyId)->withCount('items')->withSum('items', 'net_salary')
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.trim($request->string('search')).'%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')));

        return view('payroll.periods.index', ['periods' => $query->latest('start_date')->paginate($this->perPage($request, 20))->withQueryString()]);
    }

    public function storePeriod(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'], 'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'], 'payment_date' => ['nullable', 'date', 'after_or_equal:end_date'],
            'notes' => ['nullable', 'string', 'max:2000'], 'tax_exchange_rate_khr' => ['required', 'numeric', 'min:1', 'max:100000'],
            'tax_rate_date' => ['required', 'date'], 'tax_rate_source' => ['required', 'url', 'max:255'],
        ]);
        $companyId = $this->companyId();
        $overlap = PayrollPeriod::query()->where('company_id', $companyId)->whereDate('start_date', '<=', $data['end_date'])->whereDate('end_date', '>=', $data['start_date'])->exists();
        if ($overlap) {
            return back()->withInput()->withErrors(['start_date' => 'A payroll period already covers part of this date range.']);
        }
        PayrollPeriod::query()->create(['company_id' => $companyId, ...$data]);

        return back()->with('status', 'Payroll period created.');
    }

    public function generate(PayrollPeriod $period, PayrollCalculatorService $calculator, Request $request): RedirectResponse
    {
        $this->ensurePeriodCompany($period);
        Gate::forUser($request->user())->authorize('process', $period);
        $count = $calculator->generate($period, $request->user());

        return back()->with('status', "Generated payroll for {$count} employee(s); it is ready for review.");
    }

    public function approve(PayrollPeriod $period, PayrollWorkflowService $workflow, Request $request): RedirectResponse
    {
        $this->ensurePeriodCompany($period);
        Gate::forUser($request->user())->authorize('approve', $period);
        $workflow->approve($period, $request->user());

        return back()->with('status', 'Payroll period approved.');
    }

    public function pay(Request $request, PayrollPeriod $period, PayrollWorkflowService $workflow): RedirectResponse
    {
        $this->ensurePeriodCompany($period);
        Gate::forUser($request->user())->authorize('pay', $period);
        $data = $request->validate(['payment_method' => ['required', 'in:bank_transfer,cash,cheque,mobile_banking,other'], 'reference_number' => ['nullable', 'string', 'max:100', 'unique:payroll_payments,reference_number'], 'paid_at' => ['required', 'date'], 'notes' => ['nullable', 'string', 'max:2000']]);
        $workflow->recordPayment($period, $request->user(), $data);

        return back()->with('status', 'Payroll payment recorded and payslips marked paid.');
    }

    public function settings(Request $request): View
    {
        return view('payroll.settings.index', ['settings' => PayrollSetting::forCompany($this->companyId()), 'holidays' => PublicHoliday::query()->where('company_id', $this->companyId())->orderBy('holiday_date')->paginate($this->perPage($request, 20))->withQueryString()]);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $data = $request->validate(['khr_per_usd' => ['required', 'numeric', 'min:1', 'max:100000'], 'working_days_per_month' => ['required', 'integer', 'between:1,31'], 'hours_per_day' => ['required', 'numeric', 'between:0.25,24'], 'default_overtime_multiplier' => ['required', 'numeric', 'between:1,10'], 'dependent_relief_khr' => ['required', 'integer', 'min:0'], 'nssf_employee_health_rate' => ['required', 'numeric', 'between:0,100'], 'nssf_employer_health_rate' => ['required', 'numeric', 'between:0,100'], 'nssf_employer_risk_rate' => ['required', 'numeric', 'between:0,100']]);
        foreach (['require_overtime_approval', 'deduct_unpaid_absence', 'salary_tax_enabled'] as $field) {
            $data[$field] = $request->boolean($field);
        }
        PayrollSetting::query()->updateOrCreate(['company_id' => $this->companyId()], [...$data, 'updated_by' => $request->user()->id]);

        return back()->with('status', 'Payroll settings saved. Changes apply to future payroll calculations.');
    }

    public function review(Request $request): View
    {
        $companyId = $this->companyId();
        $overtime = Attendance::query()->with('employee.department')->where('overtime_minutes', '>', 0)->whereHas('employee', fn ($q) => $q->where('company_id', $companyId))->when($request->filled('overtime_status'), fn ($q) => $q->where('overtime_review_status', $request->string('overtime_status')))->latest('work_date')->paginate(15, ['*'], 'overtime_page');
        $exceptions = PayrollItem::query()->with(['employee', 'period'])->where('exception_count', '>', 0)->whereHas('period', fn ($q) => $q->where('company_id', $companyId))->latest('id')->paginate(15, ['*'], 'exception_page');

        return view('payroll.review.index', compact('overtime', 'exceptions'));
    }

    public function reviewOvertime(Request $request, Attendance $attendance, string $decision): RedirectResponse
    {
        abort_unless(in_array($decision, ['approve', 'reject'], true), 404);
        abort_unless($attendance->employee()->where('company_id', $this->companyId())->exists(), 404);
        Gate::forUser($request->user())->authorize('reviewOvertime', $attendance);
        $data = $request->validate(['note' => [$decision === 'reject' ? 'required' : 'nullable', 'string', 'min:3', 'max:1000']]);
        $attendance->forceFill(['overtime_approved' => $decision === 'approve', 'overtime_review_status' => $decision === 'approve' ? 'approved' : 'rejected', 'overtime_review_note' => $data['note'] ?? null, 'overtime_approved_by' => $request->user()->id, 'overtime_approved_at' => now()])->save();

        return back()->with('status', 'Overtime '.$decision.'d. Re-generate a draft payroll period to include the change.');
    }

    public function payslips(Request $request): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee !== null, 403, 'Your user account is not linked to an employee record.');
        $items = PayrollItem::query()->with('period')->where('employee_id', $employee->id)->whereHas('period', fn ($q) => $q->whereIn('status', ['approved', 'paid', 'closed']))->latest('id')->paginate($this->perPage($request, 20))->withQueryString();

        return view('payroll.payslips.index', compact('items'));
    }

    private function companyId(): int
    {
        return (int) Company::query()->value('id');
    }

    private function ensurePeriodCompany(PayrollPeriod $period): void
    {
        abort_unless($period->company_id === $this->companyId(), 404);
    }
}
