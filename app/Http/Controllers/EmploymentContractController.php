<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmploymentContract;
use App\Services\EmploymentContractService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\CarbonImmutable;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmploymentContractController extends Controller
{
    public function index(Request $request)
    {
        $contracts = EmploymentContract::query()
            ->with('employee')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($q) => $q->where('contract_number', 'like', $term)
                    ->orWhereHas('employee', fn ($q) => $q->where('full_name_km', 'like', $term)
                        ->orWhere('full_name_en', 'like', $term)
                        ->orWhere('employee_code', 'like', $term)));
            })
            ->latest('start_date')
            ->paginate(20)
            ->withQueryString();

        return view('pages.employment-contracts.index', compact('contracts'));
    }

    public function create(Request $request)
    {
        $renewal = $request->filled('renew')
            ? EmploymentContract::query()->whereIn('status', ['active', 'expiring'])
                ->where('type', 'fdc')->findOrFail($request->integer('renew'))
            : null;

        return view('pages.employment-contracts.create', [
            'employees' => Employee::query()->active()->orderBy('employee_code')->get(),
            'renewal' => $renewal,
        ]);
    }

    public function store(Request $request, EmploymentContractService $service): RedirectResponse
    {
        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'previous_contract_id' => ['nullable', 'exists:employment_contracts,id'],
            'contract_number' => ['required', 'string', 'max:100', 'unique:employment_contracts'],
            'type' => ['required', Rule::in(['fdc', 'udc', 'probation', 'apprenticeship', 'internship'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'signed_at' => ['nullable', 'date'],
            'probation_category' => ['nullable', Rule::in(['regular', 'specialized', 'non_specialized'])],
            'probation_end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'salary_amount' => ['required', 'numeric', 'min:0'],
            'salary_currency' => ['required', Rule::in(['USD', 'KHR'])],
            'pay_type' => ['required', Rule::in(['monthly', 'daily', 'hourly'])],
            'work_hours_per_day' => ['required', 'numeric', 'min:1', 'max:24'],
            'work_days_per_week' => ['required', 'numeric', 'min:1', 'max:7'],
            'document' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        $employee = Employee::with(['company', 'branch', 'department', 'position'])->findOrFail($data['employee_id']);
        $previous = isset($data['previous_contract_id'])
            ? EmploymentContract::query()->whereIn('status', ['active', 'expiring'])
                ->where('type', 'fdc')->findOrFail($data['previous_contract_id'])
            : null;

        if ($previous && $previous->employee_id !== $employee->id) {
            abort(422, 'Renewal employee does not match the previous contract.');
        }
        $path = $request->file('document')?->store('private/employment-contracts');

        $contract = EmploymentContract::create([
            ...collect($data)->except('document')->all(),
            'company_id' => $employee->company_id,
            'position_title' => $employee->position?->name_km ?? $employee->position?->name,
            'department_name' => $employee->department?->name_km ?? $employee->department?->name,
            'branch_name' => $employee->branch?->name_km ?? $employee->branch?->name,
            'document_path' => $path,
            'original_name' => $request->file('document')?->getClientOriginalName(),
            'renewal_notice_date' => $this->renewalNoticeDate($data['start_date'], $data['end_date'] ?? null),
            'status' => 'draft',
        ]);

        $service->submit($contract, $request->user());

        return redirect()->route('contracts.index')->with('success', 'កិច្ចសន្យាត្រូវបានបញ្ជូនសម្រាប់អនុម័ត។');
    }

    public function approve(Request $request, EmploymentContract $contract, EmploymentContractService $service): RedirectResponse
    {
        $service->approve($contract, $request->user());
        return back()->with('success', 'បានអនុម័តកិច្ចសន្យា។');
    }

    public function renew(EmploymentContract $contract): RedirectResponse
    {
        abort_unless($contract->type === 'fdc' && in_array($contract->status, ['active', 'expiring'], true), 422);
        return redirect()->route('contracts.create', ['renew' => $contract->id]);
    }

    public function terminate(Request $request, EmploymentContract $contract, EmploymentContractService $service): RedirectResponse
    {
        $data = $request->validate([
            'termination_date' => ['required', 'date'],
            'termination_reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);
        $service->terminate($contract, $request->user(), $data['termination_date'], $data['termination_reason']);
        return back()->with('success', 'បានបញ្ចប់កិច្ចសន្យា និងរក្សាទុកមូលហេតុ។');
    }

    public function mine(Request $request)
    {
        abort_unless($request->user()->employee, 403);
        $contracts = EmploymentContract::query()
            ->where('employee_id', $request->user()->employee->id)
            ->latest('start_date')->paginate(20);
        return view('pages.employment-contracts.mine', compact('contracts'));
    }

    public function download(Request $request, EmploymentContract $contract): StreamedResponse
    {
        $own = $request->user()->employee?->id === $contract->employee_id;
        abort_unless($own || $request->user()->can('contract.view'), 403);
        abort_unless($contract->document_path && Storage::exists($contract->document_path), 404);
        return Storage::download($contract->document_path, $contract->original_name ?: 'employment-contract.pdf');
    }

    private function renewalNoticeDate(string $startDate, ?string $endDate): ?CarbonImmutable
    {
        if (! $endDate) {
            return null;
        }

        $start = CarbonImmutable::parse($startDate);
        $end = CarbonImmutable::parse($endDate);
        $days = $start->diffInDays($end);

        return $end->subDays($days > 365 ? 15 : ($days > 183 ? 10 : 0));
    }
}
