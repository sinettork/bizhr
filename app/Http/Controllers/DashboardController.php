<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Company;
use App\Models\DataExport;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\ExpenseClaim;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\PayrollPeriod;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $employee = Employee::query()->where('user_id', $user->id)->first();
        $companyId = $employee->company_id ?? Cache::remember(
            'dashboard:default-company-id',
            now()->addMinutes(10),
            fn (): ?int => Company::query()->value('id'),
        );
        $isManagerOrAdmin = $user->can('attendance.report') || $user->can('employee.view');
        $managerDepartmentId = $user->hasRole('Manager') && ! $user->hasAnyRole(['Super Admin', 'Owner', 'HR Administrator'])
            ? $employee?->department_id
            : null;

        $scopeEmployees = static function (Builder $query) use ($companyId, $managerDepartmentId): void {
            if ($companyId) {
                $query->where('company_id', $companyId);
            }

            if ($managerDepartmentId) {
                $query->where('department_id', $managerDepartmentId);
            }
        };

        $metrics = [
            'employees' => 0,
            'present' => 0,
            'late' => 0,
            'leave' => 0,
            'openTasks' => 0,
            'leaveBalance' => 0,
            'pendingExports' => DataExport::query()->where('user_id', $user->id)->whereIn('status', ['queued', 'processing'])->count(),
            'pendingExpenses' => 0,
            'pendingPayroll' => 0,
        ];

        if ($isManagerOrAdmin) {
            $employees = Employee::query()->where('is_active', true);
            $scopeEmployees($employees);
            $metrics['employees'] = $employees->count();

            $attendance = Attendance::query()->whereDate('work_date', today())->whereHas('employee', $scopeEmployees);
            $metrics['present'] = (clone $attendance)->whereIn('status', ['present', 'late', 'half_day', 'remote_work', 'business_trip'])->count();
            $metrics['late'] = (clone $attendance)->where('late_minutes', '>', 0)->count();
            $metrics['leave'] = LeaveRequest::query()->where('status', 'approved')->whereDate('start_date', '<=', today())->whereDate('end_date', '>=', today())->whereHas('employee', $scopeEmployees)->count();
            $metrics['scheduled'] = EmployeeSchedule::query()->whereDate('work_date', today())->where('is_rest_day', false)->whereHas('employee', $scopeEmployees)->count();
            $metrics['openCheckouts'] = (clone $attendance)->whereNotNull('check_in_at')->whereNull('check_out_at')->count();
            $metrics['absent'] = max(0, $metrics['scheduled'] - $metrics['present'] - $metrics['leave']);
            $metrics['pendingLeaveApprovals'] = LeaveRequest::query()->whereIn('status', ['pending', 'manager_approved'])->whereHas('employee', $scopeEmployees)->count();
            $metrics['pendingCorrections'] = AttendanceCorrection::query()->where('status', 'pending')->whereHas('employee', $scopeEmployees)->count();
        }

        $myAttendance = null;
        $mySchedule = null;
        $myTasks = collect();
        $myUpcomingLeave = collect();

        if ($employee) {
            $metrics['openTasks'] = Task::query()->where('assigned_to', $employee->id)->whereNotIn('status', ['verified', 'cancelled'])->count();
            $metrics['leaveBalance'] = LeaveBalance::query()->where('employee_id', $employee->id)->sum('remaining_days');
            $myAttendance = Attendance::query()->where('employee_id', $employee->id)->whereDate('work_date', today())->first();
            $mySchedule = EmployeeSchedule::query()->with('workShift')->where('employee_id', $employee->id)->whereDate('work_date', today())->first();
            $myTasks = Task::query()->where('assigned_to', $employee->id)->whereNotIn('status', ['verified', 'cancelled'])->orderBy('due_date')->limit(5)->get();
            $myUpcomingLeave = LeaveRequest::query()->with('leaveType')->where('employee_id', $employee->id)->whereIn('status', ['pending', 'manager_approved', 'approved'])->whereDate('end_date', '>=', today())->orderBy('start_date')->limit(3)->get();
        }

        if ($user->can('payroll.view') && $companyId) {
            $metrics['pendingPayroll'] = PayrollPeriod::query()->where('company_id', $companyId)->where('status', 'awaiting_approval')->count();
        }
        if ($user->can('expense.view') && $companyId) {
            $metrics['pendingExpenses'] = ExpenseClaim::query()->whereIn('status', ['pending_manager', 'pending_accounting'])->whereHas('employee', fn (Builder $query) => $query->where('company_id', $companyId))->count();
        }

        $recentAttendances = $isManagerOrAdmin
            ? Attendance::query()->with('employee:id,first_name,last_name,full_name_km,full_name_en')->whereDate('work_date', today())->whereHas('employee', $scopeEmployees)->latest('check_in_at')->limit(6)->get()
            : collect();

        $actionItems = collect([
            ['label' => 'Leave requests awaiting review', 'count' => $metrics['pendingLeaveApprovals'] ?? 0, 'route' => 'leave.requests.review', 'permission' => 'leave.approve', 'icon' => 'fa-calendar-check'],
            ['label' => 'Attendance corrections awaiting review', 'count' => $metrics['pendingCorrections'] ?? 0, 'route' => 'attendance.corrections.review', 'permission' => 'attendance.approve', 'icon' => 'fa-clipboard-check'],
            ['label' => 'Payroll periods awaiting approval', 'count' => $metrics['pendingPayroll'], 'route' => 'payroll.review', 'permission' => 'payroll.approve', 'icon' => 'fa-file-circle-check'],
            ['label' => 'Expense claims awaiting action', 'count' => $metrics['pendingExpenses'], 'route' => 'expenses.index', 'permission' => ['expense.approve-manager', 'expense.approve-accounting'], 'icon' => 'fa-receipt'],
        ])->filter(fn (array $item) => is_array($item['permission'])
            ? $user->hasAnyPermission($item['permission'])
            : $user->can($item['permission']));

        return view('dashboard.index', compact(
            'metrics',
            'recentAttendances',
            'isManagerOrAdmin',
            'actionItems',
            'employee',
            'myAttendance',
            'mySchedule',
            'myTasks',
            'myUpcomingLeave',
        ));
    }
}
