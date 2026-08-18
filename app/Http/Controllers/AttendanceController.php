<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $employee = $user->employee;
        $companyId = $employee->company_id ?? Company::query()->value('id');
        $canManage = $user->can('attendance.report') || $user->can('attendance.approve');
        $date = $request->date('date') ?? today();

        $records = Attendance::query()
            ->with(['employee.branch', 'employee.department'])
            ->whereDate('work_date', $date)
            ->when(! $canManage, fn (Builder $query) => $query->where('employee_id', $employee?->id ?: 0))
            ->when($canManage, fn (Builder $query) => $query->whereHas('employee', fn (Builder $employees) => $employees->where('company_id', $companyId)))
            ->orderBy('employee_id')
            ->paginate($this->perPage($request, 20))
            ->withQueryString();

        $today = $employee ? Attendance::query()->where('employee_id', $employee->id)->whereDate('work_date', today())->first() : null;
        $summary = [
            'records' => $canManage ? $records->total() : ($today ? 1 : 0),
            'present' => $canManage ? Attendance::query()->whereDate('work_date', $date)->whereHas('employee', fn (Builder $employees) => $employees->where('company_id', $companyId))->whereIn('status', ['present', 'late', 'remote_work', 'business_trip'])->count() : ($today?->isPresent() ? 1 : 0),
            'late' => $canManage ? Attendance::query()->whereDate('work_date', $date)->whereHas('employee', fn (Builder $employees) => $employees->where('company_id', $companyId))->where('late_minutes', '>', 0)->count() : ($today->late_minutes ?? 0),
            'open' => $canManage ? Attendance::query()->whereDate('work_date', $date)->whereHas('employee', fn (Builder $employees) => $employees->where('company_id', $companyId))->whereNotNull('check_in_at')->whereNull('check_out_at')->count() : ($today?->check_in_at && ! $today->check_out_at ? 1 : 0),
        ];

        return view('attendance.index', compact('records', 'date', 'today', 'canManage', 'summary'));
    }
}
