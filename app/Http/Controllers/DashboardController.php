<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\DataExport;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\EmploymentContract;
use App\Models\ExpenseClaim;
use App\Models\LeaveBalance;
use App\Models\LeaveRequest;
use App\Models\PayrollPeriod;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        abort_unless($user instanceof User, 403);

        $companyId = $this->currentCompanyId($request);
        $employee = Employee::query()
            ->where('company_id', $companyId)
            ->where('user_id', $user->id)
            ->first();
        $persona = $this->resolvePersona($user);

        $data = match ($persona) {
            'super-admin' => $this->superAdminDashboard($user, $companyId),
            'owner' => $this->ownerDashboard($user, $companyId),
            'hr' => $this->hrDashboard($user, $companyId),
            'accountant' => $this->accountantDashboard($user, $companyId),
            'manager' => $this->managerDashboard($user, $companyId, $employee),
            default => $this->employeeDashboard($companyId, $employee),
        };

        return view('dashboard.index', [
            'dashboardPersona' => $persona,
            'employee' => $employee,
            ...$data,
        ]);
    }

    private function resolvePersona(User $user): string
    {
        return match (true) {
            $user->hasRole('Super Admin') => 'super-admin',
            $user->hasRole('Owner') => 'owner',
            $user->hasRole('HR Administrator') => 'hr',
            $user->hasRole('Accountant') => 'accountant',
            $user->hasRole('Manager') => 'manager',
            default => 'employee',
        };
    }

    /** @return array<string, mixed> */
    private function employeeDashboard(int $companyId, ?Employee $employee): array
    {
        $metrics = [
            'openTasks' => 0,
            'leaveBalance' => 0.0,
        ];
        $myAttendance = null;
        $mySchedule = null;
        $myTasks = collect();
        $myUpcomingLeave = collect();

        if ($employee !== null) {
            $metrics['openTasks'] = Task::query()
                ->where('company_id', $companyId)
                ->where('assigned_to', $employee->id)
                ->whereNotIn('status', ['verified', 'cancelled'])
                ->count();
            $metrics['leaveBalance'] = (float) LeaveBalance::query()
                ->where('employee_id', $employee->id)
                ->sum('remaining_days');
            $myAttendance = Attendance::query()
                ->where('employee_id', $employee->id)
                ->whereDate('work_date', today())
                ->first();
            $mySchedule = EmployeeSchedule::query()
                ->with('workShift')
                ->where('employee_id', $employee->id)
                ->whereDate('work_date', today())
                ->first();
            $myTasks = Task::query()
                ->where('company_id', $companyId)
                ->where('assigned_to', $employee->id)
                ->whereNotIn('status', ['verified', 'cancelled'])
                ->orderBy('due_date')
                ->limit(5)
                ->get();
            $myUpcomingLeave = LeaveRequest::query()
                ->with('leaveType')
                ->where('employee_id', $employee->id)
                ->whereIn('status', ['pending', 'manager_approved', 'approved'])
                ->whereDate('end_date', '>=', today())
                ->orderBy('start_date')
                ->limit(3)
                ->get();
        }

        return compact('metrics', 'myAttendance', 'mySchedule', 'myTasks', 'myUpcomingLeave');
    }

    /** @return array<string, mixed> */
    private function managerDashboard(User $user, int $companyId, ?Employee $employee): array
    {
        $departmentId = $employee?->department_id;
        $metrics = $this->workforceMetrics($companyId, $departmentId);
        $metrics['openTasks'] = $this->taskCount($companyId, $departmentId, fn (Builder $query) => $query->whereNotIn('status', ['verified', 'cancelled']));
        $metrics['waitingVerification'] = $this->taskCount($companyId, $departmentId, fn (Builder $query) => $query->where('status', 'waiting_verification'));
        $metrics['pendingLeaveApprovals'] = $departmentId === null ? 0 : LeaveRequest::query()
            ->where('status', 'pending')
            ->whereHas('employee', fn (Builder $query) => $query
                ->where('company_id', $companyId)
                ->where('department_id', $departmentId))
            ->count();
        $metrics['pendingExpenses'] = ExpenseClaim::query()
            ->where('company_id', $companyId)
            ->where('status', 'pending_manager')
            ->when($departmentId !== null, fn (Builder $query) => $query->whereHas('employee', fn (Builder $employeeQuery) => $employeeQuery->where('department_id', $departmentId)))
            ->count();

        $actionItems = collect([
            $this->actionItem('Leave requests awaiting manager review', $metrics['pendingLeaveApprovals'], 'leave.requests.review', 'fa-calendar-check'),
            $this->actionItem('Tasks waiting for verification', $metrics['waitingVerification'], 'tasks.index', 'fa-list-check'),
            $this->actionItem('Expense claims awaiting manager review', $metrics['pendingExpenses'], 'expenses.index', 'fa-receipt'),
            $this->actionItem('Attendance corrections awaiting review', $metrics['pendingCorrections'], 'attendance.corrections.review', 'fa-clipboard-check'),
        ]);

        return [
            'metrics' => $metrics,
            'actionItems' => $actionItems,
            'recentAttendances' => $this->recentAttendances($companyId, $departmentId),
            'managerContextMissing' => $departmentId === null,
        ];
    }

    /** @return array<string, mixed> */
    private function hrDashboard(User $user, int $companyId): array
    {
        $metrics = $this->workforceMetrics($companyId);
        $metrics['pendingLeaveApprovals'] = LeaveRequest::query()
            ->where('status', 'manager_approved')
            ->whereHas('employee', fn (Builder $query) => $query->where('company_id', $companyId))
            ->count();
        $metrics['pendingContracts'] = EmploymentContract::query()
            ->where('company_id', $companyId)
            ->where('status', 'pending_approval')
            ->count();
        $metrics['contractsExpiringSoon'] = EmploymentContract::query()
            ->where('company_id', $companyId)
            ->whereIn('status', ['active', 'expiring'])
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [today(), today()->addDays(30)])
            ->count();
        $metrics['waitingVerification'] = Task::query()
            ->where('company_id', $companyId)
            ->where('status', 'waiting_verification')
            ->count();

        $actionItems = collect([
            $this->actionItem('Leave requests awaiting final HR approval', $metrics['pendingLeaveApprovals'], 'leave.requests.review', 'fa-calendar-check'),
            $this->actionItem('Attendance corrections awaiting review', $metrics['pendingCorrections'], 'attendance.corrections.review', 'fa-clipboard-check'),
            $this->actionItem('Employment contracts awaiting approval', $metrics['pendingContracts'], 'contracts.index', 'fa-file-signature'),
            $this->actionItem('Contracts expiring within 30 days', $metrics['contractsExpiringSoon'], 'contracts.index', 'fa-hourglass-half'),
        ]);

        return [
            'metrics' => $metrics,
            'actionItems' => $actionItems,
            'recentAttendances' => $this->recentAttendances($companyId),
        ];
    }

    /** @return array<string, mixed> */
    private function accountantDashboard(User $user, int $companyId): array
    {
        $metrics = [
            'payrollToProcess' => PayrollPeriod::query()
                ->where('company_id', $companyId)
                ->whereIn('status', ['draft', 'processing'])
                ->count(),
            'payrollAwaitingApproval' => PayrollPeriod::query()
                ->where('company_id', $companyId)
                ->where('status', 'awaiting_approval')
                ->count(),
            'payrollReadyToPay' => PayrollPeriod::query()
                ->where('company_id', $companyId)
                ->where('status', 'approved')
                ->count(),
            'expensesForAccounting' => ExpenseClaim::query()
                ->where('company_id', $companyId)
                ->where('status', 'pending_accounting')
                ->count(),
            'expensesReadyToPay' => ExpenseClaim::query()
                ->where('company_id', $companyId)
                ->where('status', 'approved')
                ->count(),
        ];

        $actionItems = collect([
            $this->actionItem('Payroll periods ready for processing', $metrics['payrollToProcess'], 'payroll.periods.index', 'fa-gears'),
            $this->actionItem('Approved payroll periods ready for payment', $metrics['payrollReadyToPay'], 'payroll.periods.index', 'fa-money-check-dollar'),
            $this->actionItem('Expenses awaiting accounting review', $metrics['expensesForAccounting'], 'expenses.index', 'fa-receipt'),
            $this->actionItem('Approved expenses ready for payment', $metrics['expensesReadyToPay'], 'expenses.index', 'fa-money-bill-transfer'),
        ]);

        $recentPayroll = PayrollPeriod::query()
            ->where('company_id', $companyId)
            ->latest('start_date')
            ->limit(5)
            ->get();

        return compact('metrics', 'actionItems', 'recentPayroll');
    }

    /** @return array<string, mixed> */
    private function ownerDashboard(User $user, int $companyId): array
    {
        $metrics = $this->workforceMetrics($companyId);
        $metrics['pendingLeaveApprovals'] = LeaveRequest::query()
            ->where('status', 'manager_approved')
            ->whereHas('employee', fn (Builder $query) => $query->where('company_id', $companyId))
            ->count();
        $metrics['pendingPayroll'] = PayrollPeriod::query()
            ->where('company_id', $companyId)
            ->where('status', 'awaiting_approval')
            ->count();
        $metrics['pendingContracts'] = EmploymentContract::query()
            ->where('company_id', $companyId)
            ->where('status', 'pending_approval')
            ->count();
        $metrics['pendingExpenses'] = ExpenseClaim::query()
            ->where('company_id', $companyId)
            ->whereIn('status', ['pending_manager', 'pending_accounting', 'approved'])
            ->count();

        $actionItems = collect([
            $this->actionItem('Leave requests awaiting final approval', $metrics['pendingLeaveApprovals'], 'leave.requests.review', 'fa-calendar-check'),
            $this->actionItem('Payroll periods awaiting approval', $metrics['pendingPayroll'], 'payroll.review', 'fa-file-circle-check'),
            $this->actionItem('Employment contracts awaiting approval', $metrics['pendingContracts'], 'contracts.index', 'fa-file-signature'),
            $this->actionItem('Expense claims still in workflow', $metrics['pendingExpenses'], 'expenses.index', 'fa-receipt'),
        ]);

        return [
            'metrics' => $metrics,
            'actionItems' => $actionItems,
            'recentAttendances' => $this->recentAttendances($companyId),
        ];
    }

    /** @return array<string, mixed> */
    private function superAdminDashboard(User $user, int $companyId): array
    {
        $metrics = [
            'activeEmployees' => Employee::query()->where('company_id', $companyId)->where('is_active', true)->count(),
            'branches' => Branch::query()->where('company_id', $companyId)->count(),
            'linkedUsers' => User::query()->whereHas('employee', fn (Builder $query) => $query->where('company_id', $companyId))->count(),
            'inactiveUsers' => User::query()->where('is_active', false)->whereHas('employee', fn (Builder $query) => $query->where('company_id', $companyId))->count(),
            'auditToday' => AuditLog::query()->where('company_id', $companyId)->whereDate('created_at', today())->count(),
            'pendingExports' => DataExport::query()->where('company_id', $companyId)->whereIn('status', ['queued', 'processing'])->count(),
            'failedExports' => DataExport::query()->where('company_id', $companyId)->where('status', 'failed')->count(),
        ];

        $systemItems = collect([
            $this->actionItem('Inactive linked user accounts', $metrics['inactiveUsers'], 'users.index', 'fa-user-lock'),
            $this->actionItem('Exports still queued or processing', $metrics['pendingExports'], 'exports.index', 'fa-file-export'),
            $this->actionItem('Failed exports requiring review', $metrics['failedExports'], 'exports.index', 'fa-triangle-exclamation'),
        ]);

        $recentAuditLogs = AuditLog::query()
            ->with('user:id,name')
            ->where('company_id', $companyId)
            ->latest('created_at')
            ->limit(8)
            ->get();

        return compact('metrics', 'systemItems', 'recentAuditLogs');
    }

    /** @return array<string, int> */
    private function workforceMetrics(int $companyId, ?int $departmentId = null): array
    {
        $scopeEmployees = static function (Builder $query) use ($companyId, $departmentId): void {
            $query->where('company_id', $companyId);

            if ($departmentId !== null) {
                $query->where('department_id', $departmentId);
            }
        };

        $employees = Employee::query()->where('is_active', true);
        $scopeEmployees($employees);

        $attendance = Attendance::query()
            ->whereDate('work_date', today())
            ->whereHas('employee', $scopeEmployees);
        $present = (clone $attendance)
            ->whereIn('status', ['present', 'late', 'half_day', 'remote_work', 'business_trip'])
            ->count();
        $scheduled = EmployeeSchedule::query()
            ->whereDate('work_date', today())
            ->where('is_rest_day', false)
            ->whereHas('employee', $scopeEmployees)
            ->count();
        $leave = LeaveRequest::query()
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', today())
            ->whereDate('end_date', '>=', today())
            ->whereHas('employee', $scopeEmployees)
            ->count();

        return [
            'employees' => $employees->count(),
            'scheduled' => $scheduled,
            'present' => $present,
            'late' => (clone $attendance)->where('late_minutes', '>', 0)->count(),
            'leave' => $leave,
            'openCheckouts' => (clone $attendance)->whereNotNull('check_in_at')->whereNull('check_out_at')->count(),
            'absent' => max(0, $scheduled - $present - $leave),
            'pendingCorrections' => AttendanceCorrection::query()
                ->where('status', 'pending')
                ->whereHas('employee', $scopeEmployees)
                ->count(),
        ];
    }

    /** @param callable(Builder): mixed $constraint */
    private function taskCount(int $companyId, ?int $departmentId, callable $constraint): int
    {
        if ($departmentId === null) {
            return 0;
        }

        $query = Task::query()
            ->where('company_id', $companyId)
            ->whereHas('employee', fn (Builder $employeeQuery) => $employeeQuery->where('department_id', $departmentId));
        $constraint($query);

        return $query->count();
    }

    /** @return Collection<int, Attendance> */
    private function recentAttendances(int $companyId, ?int $departmentId = null): Collection
    {
        return Attendance::query()
            ->with('employee:id,first_name,last_name,full_name_km,full_name_en')
            ->whereDate('work_date', today())
            ->whereHas('employee', function (Builder $query) use ($companyId, $departmentId): void {
                $query->where('company_id', $companyId);

                if ($departmentId !== null) {
                    $query->where('department_id', $departmentId);
                }
            })
            ->latest('check_in_at')
            ->limit(6)
            ->get();
    }

    /** @return array{label: string, count: int, route: string, icon: string} */
    private function actionItem(string $label, int $count, string $route, string $icon): array
    {
        return compact('label', 'count', 'route', 'icon');
    }
}
