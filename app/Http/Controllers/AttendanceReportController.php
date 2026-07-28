<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceReportController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->can('attendance.report'),
            403
        );

        $validated = $request->validate([
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],

            'branch_id' => [
                'nullable',
                'integer',
                'exists:branches,id',
            ],

            'employee_id' => [
                'nullable',
                'integer',
                'exists:employees,id',
            ],

            'status' => [
                'nullable',

                Rule::in([
                    'present',
                    'late',
                    'absent',
                    'on_leave',
                    'half_day',
                    'holiday',
                    'rest_day',
                    'remote_work',
                    'business_trip',
                ]),
            ],

            'date_from' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'date_to' => [
                'nullable',
                'date_format:Y-m-d',
            ],

            'per_page' => [
                'nullable',
                'integer',

                Rule::in([
                    10,
                    25,
                    50,
                    100,
                ]),
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Filter values
        |--------------------------------------------------------------------------
        */

        $search = trim(
            (string) ($validated['search'] ?? '')
        );

        $branchId =
            $validated['branch_id'] ?? null;

        $employeeId =
            $validated['employee_id'] ?? null;

        $status =
            $validated['status'] ?? null;

        $dateFrom =
            $validated['date_from']
            ?? now()->startOfMonth()->format('Y-m-d');

        $dateTo =
            $validated['date_to']
            ?? now()->endOfMonth()->format('Y-m-d');

        $perPage = (int) (
            $validated['per_page'] ?? 25
        );

        /*
         * Automatically correct reversed dates.
         */
        if ($dateFrom > $dateTo) {
            [$dateFrom, $dateTo] = [
                $dateTo,
                $dateFrom,
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Shared filter function
        |--------------------------------------------------------------------------
        */

        $applyFilters = function (
            Builder $query
        ) use (
            $search,
            $branchId,
            $employeeId,
            $status,
            $dateFrom,
            $dateTo
        ): void {
            $query
                ->whereDate(
                    'work_date',
                    '>=',
                    $dateFrom
                )
                ->whereDate(
                    'work_date',
                    '<=',
                    $dateTo
                );

            if ($branchId) {
                $query->where(
                    'branch_id',
                    $branchId
                );
            }

            if ($employeeId) {
                $query->where(
                    'employee_id',
                    $employeeId
                );
            }

            if ($status) {
                $query->where(
                    'status',
                    $status
                );
            }

            if (filled($search)) {
                $query->whereHas(
                    'employee',
                    function (
                        Builder $employeeQuery
                    ) use ($search): void {
                        $employeeQuery->where(
                            function (
                                Builder $searchQuery
                            ) use ($search): void {
                                $searchQuery
                                    ->where(
                                        'employee_code',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'first_name',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'last_name',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'full_name_km',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'full_name_en',
                                        'like',
                                        "%{$search}%"
                                    )
                                    ->orWhere(
                                        'phone',
                                        'like',
                                        "%{$search}%"
                                    );
                            }
                        );
                    }
                );
            }
        };

        /*
        |--------------------------------------------------------------------------
        | Attendance table
        |--------------------------------------------------------------------------
        */

        $attendanceQuery = Attendance::query()
            ->with([
                'employee.branch',
                'employee.department',
                'employee.position',
                'branch',
                'approvedBy',
            ]);

        $applyFilters($attendanceQuery);

        $attendances = $attendanceQuery
            ->orderByDesc('work_date')
            ->orderBy('employee_id')
            ->paginate($perPage)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Statistics
        |--------------------------------------------------------------------------
        */

        $statisticsQuery = Attendance::query();

        $applyFilters($statisticsQuery);

        $total = (clone $statisticsQuery)
            ->count();

        $present = (clone $statisticsQuery)
            ->whereIn(
                'status',
                [
                    'present',
                    'late',
                ]
            )
            ->count();

        $late = (clone $statisticsQuery)
            ->where('status', 'late')
            ->count();

        $absent = (clone $statisticsQuery)
            ->where('status', 'absent')
            ->count();

        $workedMinutes = (int) (
            clone $statisticsQuery
        )->sum('worked_minutes');

        $overtimeMinutes = (int) (
            clone $statisticsQuery
        )->sum('overtime_minutes');

        $lateMinutes = (int) (
            clone $statisticsQuery
        )->sum('late_minutes');

        $earlyLeaveMinutes = (int) (
            clone $statisticsQuery
        )->sum('early_leave_minutes');

        $statistics = [
            'total' => $total,

            'present' => $present,

            'late' => $late,

            'absent' => $absent,

            'attendance_rate' => $total > 0
                ? round(
                    ($present / $total) * 100,
                    1
                )
                : 0,

            'worked_minutes' =>
                $workedMinutes,

            'overtime_minutes' =>
                $overtimeMinutes,

            'late_minutes' =>
                $lateMinutes,

            'early_leave_minutes' =>
                $earlyLeaveMinutes,
        ];

        /*
        |--------------------------------------------------------------------------
        | Filter options
        |--------------------------------------------------------------------------
        */

        $branches = Branch::query()
            ->where('is_active', true)
            ->orderByDesc('is_head_office')
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        $employees = Employee::query()
            ->where('is_active', true)
            ->orderBy('employee_code')
            ->get([
                'id',
                'employee_code',
                'first_name',
                'last_name',
                'full_name_km',
                'full_name_en',
                'branch_id',
            ]);

        return view(
            'attendance.reports.index',
            compact(
                'attendances',
                'statistics',
                'branches',
                'employees',
                'search',
                'branchId',
                'employeeId',
                'status',
                'dateFrom',
                'dateTo',
                'perPage'
            )
        );
    }
}