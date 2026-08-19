<?php

namespace App\Http\Controllers;

use App\Models\LeaveBalance;
use App\Models\LeaveBalanceAdjustment;
use App\Models\LeaveType;
use App\Services\LeaveBalanceService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveBalanceController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $this->currentCompanyId($request);
        $year = min(2100, max(2000, (int) $request->input('year', now()->year)));
        $search = trim((string) $request->input('search', ''));
        $leaveTypeId = $request->integer('leave_type_id') ?: null;
        $query = LeaveBalance::query()
            ->with(['employee.branch', 'employee.department', 'leaveType'])
            ->where('year', $year)
            ->whereHas('employee', fn (Builder $employees) => $employees->where('company_id', $companyId))
            ->when($leaveTypeId, fn (Builder $balances) => $balances->where('leave_type_id', $leaveTypeId))
            ->when($search, fn (Builder $balances) => $balances->whereHas('employee', fn (Builder $employees) => $employees
                ->where('company_id', $companyId)
                ->where(fn (Builder $names) => $names
                    ->where('employee_code', 'like', "%{$search}%")
                    ->orWhere('full_name_en', 'like', "%{$search}%")
                    ->orWhere('full_name_km', 'like', "%{$search}%")
                    ->orWhere('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%"))));
        $statistics = [
            'records' => (clone $query)->count(),
            'earned' => (float) (clone $query)->sum('earned_days'),
            'used' => (float) (clone $query)->sum('used_days'),
            'remaining' => (float) (clone $query)->sum('remaining_days'),
        ];

        return view('leave.balances.index', [
            'balances' => $query->orderBy('employee_id')->orderBy('leave_type_id')->paginate($this->perPage($request, 20))->withQueryString(),
            'leaveTypes' => LeaveType::query()->where('company_id', $companyId)->orderBy('name')->get(),
            'year' => $year,
            'statistics' => $statistics,
        ]);
    }

    public function initialize(Request $request, LeaveBalanceService $service): RedirectResponse
    {
        $data = $request->validate(['year' => ['required', 'integer', 'between:2000,2100']]);
        $created = $service->initializeYearForCompany($this->currentCompanyId($request), $data['year']);

        return back()->with('status', $created ? "Initialized {$created} leave balance records." : 'Leave balances are already initialized.');
    }

    public function synchronize(Request $request, LeaveBalanceService $service): RedirectResponse
    {
        $data = $request->validate(['year' => ['required', 'integer', 'between:2000,2100']]);
        $count = $service->syncCompanyYear($this->currentCompanyId($request), $data['year']);

        return back()->with('status', "Synchronized {$count} leave balance records.");
    }

    public function adjust(Request $request, LeaveBalance $balance, LeaveBalanceService $service): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);
        $data = $request->validate([
            'adjustment_days' => ['required', 'numeric', 'between:-365,365'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);
        abort_unless($balance->employee()->where('company_id', $companyId)->exists(), 404);
        $previous = (float) $balance->adjustment_days;
        $service->setAdjustment($balance, (float) $data['adjustment_days']);
        LeaveBalanceAdjustment::query()->create([
            'leave_balance_id' => $balance->id,
            'previous_days' => $previous,
            'adjustment_days' => $data['adjustment_days'],
            'reason' => trim($data['reason']),
            'adjusted_by' => $request->user()->id,
        ]);

        return back()->with('status', 'Leave balance adjustment saved.');
    }
}
