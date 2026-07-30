<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Services\LeaveApprovalService;
use App\Services\LeaveRequestService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveRequestController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->can('leave.request'), 403);
        $employee = $request->user()?->employee;
        abort_unless($employee, 403);

        $types = LeaveType::query()
            ->where('company_id', $employee->company_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $requests = $employee->leaveRequests()
            ->with(['leaveType', 'manager', 'hr'])
            ->latest('start_date')
            ->latest('id')
            ->paginate(15);

        $balances = $employee->leaveBalances()
            ->with('leaveType')
            ->where('year', now()->year)
            ->orderBy('leave_type_id')
            ->get();

        $statistics = [
            'total' => $employee->leaveRequests()->count(),
            'pending' => $employee->leaveRequests()->whereIn('status', ['pending', 'manager_approved'])->count(),
            'approved' => $employee->leaveRequests()->where('status', 'approved')->count(),
            'remaining' => (float) $balances->sum('remaining_days'),
        ];

        return view('leave.requests.index', compact('employee', 'types', 'requests', 'balances', 'statistics'));
    }

    public function store(Request $request, LeaveRequestService $service): RedirectResponse
    {
        abort_unless($request->user()?->can('leave.request'), 403);
        $employee = $request->user()?->employee;
        abort_unless($employee, 403);

        $data = $request->validate([
            'leave_type_id' => ['required', 'integer', 'exists:leave_types,id'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $type = LeaveType::query()
            ->whereKey($data['leave_type_id'])
            ->where('company_id', $employee->company_id)
            ->where('is_active', true)
            ->firstOrFail();

        $service->submit(
            $employee,
            $type,
            $data['start_date'],
            $data['end_date'],
            filled($data['reason'] ?? null) ? trim($data['reason']) : null,
        );

        return back()->with('status', 'បានដាក់សំណើឈប់សម្រាកដោយជោគជ័យ។');
    }

    public function review(Request $request): View
    {
        abort_unless($request->user()?->can('leave.approve'), 403);
        $user = $request->user();
        $actor = $user->employee;

        $query = LeaveRequest::query()->with([
            'employee.branch',
            'employee.department',
            'employee.position',
            'leaveType',
            'manager',
            'hr',
        ]);

        if ($user->hasRole('Manager')) {
            abort_unless($actor, 403);
            $query->where('status', 'pending')
                ->whereHas('employee', fn (Builder $employeeQuery) => $employeeQuery
                    ->where('company_id', $actor->company_id)
                    ->where('department_id', $actor->department_id)
                    ->where('id', '!=', $actor->id));
        } else {
            abort_unless($user->hasAnyRole([
                'HR Administrator',
                'Owner',
                'Super Admin',
            ]), 403);

            $query->where('status', 'manager_approved');

            if ($actor) {
                $query->whereHas('employee', fn (Builder $employeeQuery) => $employeeQuery
                    ->where('company_id', $actor->company_id));
            }
        }

        $statisticsQuery = clone $query;
        $requests = $query->latest('start_date')->latest('id')->paginate(20);

        $statistics = [
            'pending_review' => $statisticsQuery->count(),
            'approved_today' => LeaveRequest::query()->where('status', 'approved')->whereDate('hr_reviewed_at', today())->count(),
            'rejected_today' => LeaveRequest::query()->where('status', 'rejected')->where(function (Builder $query): void {
                $query->whereDate('manager_reviewed_at', today())->orWhereDate('hr_reviewed_at', today());
            })->count(),
        ];

        return view('leave.requests.review', compact('requests', 'statistics'));
    }

    public function approve(
        LeaveRequest $leaveRequest,
        Request $request,
        LeaveApprovalService $service,
    ): RedirectResponse {
        abort_unless($request->user()?->can('leave.approve'), 403);
        $validated = $request->validate(['note' => ['nullable', 'string', 'max:1000']]);

        $result = $service->approve(
            $leaveRequest,
            $request->user(),
            filled($validated['note'] ?? null) ? trim($validated['note']) : null,
        );

        $message = $result->status === 'manager_approved'
            ? 'អ្នកគ្រប់គ្រងបានអនុម័ត។ សំណើកំពុងរង់ចាំ HR អនុម័តចុងក្រោយ។'
            : 'HR បានអនុម័តសំណើឈប់សម្រាកជាចុងក្រោយ។';

        return back()->with('status', $message);
    }

    public function reject(
        LeaveRequest $leaveRequest,
        Request $request,
        LeaveApprovalService $service,
    ): RedirectResponse {
        abort_unless($request->user()?->can('leave.approve'), 403);
        $validated = $request->validate([
            'note' => ['required', 'string', 'min:3', 'max:1000'],
        ]);

        $service->reject($leaveRequest, $request->user(), trim($validated['note']));

        return back()->with('status', 'បានបដិសេធសំណើឈប់សម្រាក។');
    }
}
