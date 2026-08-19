<?php

namespace App\Http\Controllers;

use App\Models\ExpenseClaim;
use App\Services\ExpenseWorkflowService;
use App\Services\UploadedFileSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $this->currentCompanyId($request);
        $claims = ExpenseClaim::query()->with('employee.department')->where('company_id', $companyId)
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('expense_date')->paginate($this->perPage($request, 20))->withQueryString();

        return view('expenses.index', compact('claims'));
    }

    public function mine(Request $request): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee !== null, 403);
        $companyId = $this->currentCompanyId($request);
        abort_unless($employee->company_id === $companyId, 403);
        $claims = ExpenseClaim::query()
            ->where('company_id', $companyId)
            ->where('employee_id', $employee->id)
            ->latest('expense_date')
            ->paginate($this->perPage($request, 20))
            ->withQueryString();

        return view('expenses.mine', compact('claims'));
    }

    public function store(Request $request, UploadedFileSecurityService $fileSecurity): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee !== null, 403);
        $companyId = $this->currentCompanyId($request);
        abort_unless($employee->company_id === $companyId, 403);
        $data = $request->validate([
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
            'category' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'currency' => ['required', 'in:USD,KHR'],
            'business_purpose' => ['required', 'string', 'min:10', 'max:2000'],
            'receipt' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);
        $file = $request->file('receipt');
        abort_unless($file !== null, 422);
        $fileSecurity->assertSafe($file, 'receipt');
        $path = $file->store("expenses/{$employee->id}", $this->expensesDisk());
        ExpenseClaim::query()->create([
            ...array_diff_key($data, ['receipt' => true]),
            'company_id' => $companyId,
            'employee_id' => $employee->id,
            'receipt_path' => $path,
            'receipt_original_name' => $file->getClientOriginalName(),
            'status' => 'pending_manager',
        ]);

        return back()->with('status', 'Expense claim submitted.');
    }

    public function review(Request $request, ExpenseClaim $claim, string $stage, string $decision, ExpenseWorkflowService $workflow): RedirectResponse
    {
        abort_unless($claim->company_id === $this->currentCompanyId($request), 404);
        abort_unless(in_array($stage, ['manager', 'accounting'], true) && in_array($decision, ['approve', 'reject'], true), 404);
        Gate::forUser($request->user())->authorize($stage === 'manager' ? 'managerApprove' : 'accountingApprove', $claim);
        $data = $request->validate(['note' => ['required', 'string', 'min:3', 'max:1000']]);
        $stage === 'manager'
            ? $workflow->managerReview($claim, $request->user(), $decision === 'approve', $data['note'])
            : $workflow->accountingReview($claim, $request->user(), $decision === 'approve', $data['note']);

        return back()->with('status', 'Expense decision recorded.');
    }

    public function pay(Request $request, ExpenseClaim $claim, ExpenseWorkflowService $workflow): RedirectResponse
    {
        abort_unless($claim->company_id === $this->currentCompanyId($request), 404);
        Gate::forUser($request->user())->authorize('pay', $claim);
        $data = $request->validate(['reference' => ['required', 'string', 'min:3', 'max:100']]);
        $workflow->markPaid($claim, $request->user(), $data['reference']);

        return back()->with('status', 'Expense payment recorded.');
    }

    public function receipt(Request $request, ExpenseClaim $claim): StreamedResponse
    {
        abort_unless($claim->company_id === $this->currentCompanyId($request), 404);
        Gate::forUser($request->user())->authorize('view', $claim);
        $disk = $this->expensesDisk();
        abort_unless(Storage::disk($disk)->exists((string) $claim->receipt_path), 404);

        return Storage::disk($disk)->download((string) $claim->receipt_path, $claim->receipt_original_name ?: 'receipt');
    }

    private function expensesDisk(): string
    {
        $disk = (string) config('bizhr.expenses_disk', 'local');

        if (! in_array($disk, ['local', 's3'], true)) {
            throw new RuntimeException('BizHR expense receipts require a private local or S3 disk.');
        }

        return $disk;
    }
}
