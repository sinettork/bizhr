<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmploymentContract;
use App\Services\EmploymentContractService;
use App\Services\UploadedFileSecurityService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmploymentContractController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $this->currentCompanyId($request);
        $contracts = EmploymentContract::query()
            ->with('employee')
            ->where('company_id', $companyId)
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('search'), function ($q) use ($request, $companyId): void {
                $term = '%'.$request->string('search').'%';
                $q->where(fn ($q) => $q->where('contract_number', 'like', $term)
                    ->orWhereHas('employee', fn ($q) => $q->where('company_id', $companyId)
                        ->where(fn ($employeeQuery) => $employeeQuery
                            ->where('full_name_km', 'like', $term)
                            ->orWhere('full_name_en', 'like', $term)
                            ->orWhere('employee_code', 'like', $term))));
            })
            ->latest('start_date')
            ->paginate($this->perPage($request, 20))
            ->withQueryString();

        return view('pages.employment-contracts.index', compact('contracts'));
    }

    public function create(Request $request): View
    {
        $companyId = $this->currentCompanyId($request);
        $renewal = $request->filled('renew')
            ? EmploymentContract::query()->whereIn('status', ['active', 'expiring'])
                ->where('company_id', $companyId)
                ->where('type', 'fdc')->findOrFail($request->integer('renew'))
            : null;

        return view('pages.employment-contracts.create', [
            'employees' => Employee::query()->where('company_id', $companyId)->active()->orderBy('employee_code')->get(),
            'renewal' => $renewal,
        ]);
    }

    public function store(Request $request, EmploymentContractService $service, UploadedFileSecurityService $fileSecurity): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);
        $data = $request->validate([
            'employee_id' => ['required', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'previous_contract_id' => ['nullable', Rule::exists('employment_contracts', 'id')->where('company_id', $companyId)],
            'contract_number' => ['required', 'string', 'max:100', Rule::unique('employment_contracts', 'contract_number')->where('company_id', $companyId)],
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

        $employee = Employee::query()->with(['company', 'branch', 'department', 'position'])->where('company_id', $companyId)->whereKey($data['employee_id'])->firstOrFail();
        $previous = isset($data['previous_contract_id'])
            ? EmploymentContract::query()->whereIn('status', ['active', 'expiring'])
                ->where('company_id', $companyId)
                ->where('type', 'fdc')->whereKey($data['previous_contract_id'])->firstOrFail()
            : null;

        if ($previous && $previous->employee_id !== $employee->id) {
            abort(422, 'Renewal employee does not match the previous contract.');
        }

        $file = $request->file('document');
        if ($file instanceof UploadedFile) {
            $fileSecurity->assertSafe($file, 'document');
        }
        $path = $file instanceof UploadedFile
            ? $file->store('employment-contracts/'.$companyId.'/'.$employee->id, $this->contractsDisk())
            : null;

        $contract = EmploymentContract::create([
            ...array_diff_key($data, ['document' => true]),
            'company_id' => $companyId,
            'position_title' => $employee->position?->title,
            'department_name' => $employee->department?->name,
            'branch_name' => $employee->branch?->name,
            'document_path' => $path,
            'original_name' => $file?->getClientOriginalName(),
            'renewal_notice_date' => $this->renewalNoticeDate($data['start_date'], $data['end_date'] ?? null),
            'status' => 'draft',
        ]);

        $service->submit($contract, $request->user());

        return redirect()->route('contracts.index')->with('success', 'កិច្ចសន្យាត្រូវបានបញ្ជូនសម្រាប់អនុម័ត។');
    }

    public function approve(Request $request, EmploymentContract $contract, EmploymentContractService $service): RedirectResponse
    {
        $this->ensureCompany($contract, $request);
        Gate::forUser($request->user())->authorize('approve', $contract);
        $service->approve($contract, $request->user());

        return back()->with('success', 'បានអនុម័តកិច្ចសន្យា។');
    }

    public function renew(Request $request, EmploymentContract $contract): RedirectResponse
    {
        $this->ensureCompany($contract, $request);
        abort_unless($contract->type === 'fdc' && in_array($contract->status, ['active', 'expiring'], true), 422);

        return redirect()->route('contracts.create', ['renew' => $contract->id]);
    }

    public function terminate(Request $request, EmploymentContract $contract, EmploymentContractService $service): RedirectResponse
    {
        $this->ensureCompany($contract, $request);
        Gate::forUser($request->user())->authorize('terminate', $contract);
        $data = $request->validate([
            'termination_date' => ['required', 'date'],
            'termination_reason' => ['required', 'string', 'min:10', 'max:2000'],
        ]);
        $service->terminate($contract, $request->user(), $data['termination_date'], $data['termination_reason']);

        return back()->with('success', 'បានបញ្ចប់កិច្ចសន្យា និងរក្សាទុកមូលហេតុ។');
    }

    public function mine(Request $request): View
    {
        $companyId = $this->currentCompanyId($request);
        $employee = Employee::query()
            ->where('company_id', $companyId)
            ->where('user_id', $request->user()->id)
            ->first();
        abort_unless($employee !== null, 403);
        $contracts = EmploymentContract::query()
            ->where('company_id', $companyId)
            ->where('employee_id', $employee->id)
            ->latest('start_date')->paginate($this->perPage($request, 20))->withQueryString();

        return view('pages.employment-contracts.mine', compact('contracts'));
    }

    public function download(Request $request, EmploymentContract $contract): StreamedResponse
    {
        $this->ensureCompany($contract, $request);
        Gate::forUser($request->user())->authorize('view', $contract);
        $disk = $this->contractsDisk();
        abort_unless($contract->document_path && Storage::disk($disk)->exists($contract->document_path), 404);

        return Storage::disk($disk)->download($contract->document_path, $contract->original_name ?: 'employment-contract.pdf');
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

    private function ensureCompany(EmploymentContract $contract, Request $request): void
    {
        abort_unless((int) $contract->company_id === $this->currentCompanyId($request), 404);
    }

    private function contractsDisk(): string
    {
        $disk = (string) config('bizhr.contracts_disk', 'local');

        if (! in_array($disk, ['local', 's3'], true)) {
            throw new RuntimeException('BizHR employment contracts require a private local or S3 disk.');
        }

        return $disk;
    }
}
