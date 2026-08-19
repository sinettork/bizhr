<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $companyId = $this->currentCompanyId($request);
        $employee = $user->employee;
        if ($employee !== null) {
            abort_unless((int) $employee->company_id === $companyId, 403);
        }
        $canManage = $user->can('attendance.report') || $user->can('attendance.approve');
        $date = $request->date('date') ?? today();

        $records = Attendance::query()
            ->with(['employee.branch', 'employee.department'])
            ->whereDate('work_date', $date)
            ->whereHas('employee', fn (Builder $employees) => $employees->where('company_id', $companyId))
            ->when(! $canManage, fn (Builder $query) => $query->where('employee_id', $employee?->id ?: 0))
            ->orderBy('employee_id')
            ->paginate($this->perPage($request, 20))
            ->withQueryString();

        $today = $employee
            ? Attendance::query()->where('employee_id', $employee->id)->whereDate('work_date', today())->first()
            : null;

        $summaryQuery = Attendance::query()
            ->whereDate('work_date', $date)
            ->whereHas('employee', fn (Builder $employees) => $employees->where('company_id', $companyId));

        $summary = [
            'records' => $canManage ? $records->total() : ($today ? 1 : 0),
            'present' => $canManage ? (clone $summaryQuery)->whereIn('status', ['present', 'late', 'remote_work', 'business_trip'])->count() : ($today?->isPresent() ? 1 : 0),
            'late' => $canManage ? (clone $summaryQuery)->where('late_minutes', '>', 0)->count() : (($today?->late_minutes ?? 0) > 0 ? 1 : 0),
            'open' => $canManage ? (clone $summaryQuery)->whereNotNull('check_in_at')->whereNull('check_out_at')->count() : ($today?->check_in_at && ! $today->check_out_at ? 1 : 0),
        ];

        return view('attendance.index', compact('records', 'date', 'today', 'canManage', 'summary'));
    }
}
