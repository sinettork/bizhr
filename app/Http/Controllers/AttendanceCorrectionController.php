<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\AuditLog;
use App\Models\PayrollPeriod;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AttendanceCorrectionController extends Controller
{
    public function index(Request $request): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee !== null, 403);
        $companyId = $this->currentCompanyId($request);
        abort_unless((int) $employee->company_id === $companyId, 403);

        $attendances = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereHas('employee', fn (Builder $query) => $query->where('company_id', $companyId))
            ->where(fn (Builder $query) => $query->whereNotNull('check_in_at')->orWhereNotNull('check_out_at'))
            ->latest('work_date')
            ->limit(90)
            ->get();
        $requests = AttendanceCorrection::query()
            ->with(['attendance', 'reviewedBy'])
            ->where('employee_id', $employee->id)
            ->whereHas('employee', fn (Builder $query) => $query->where('company_id', $companyId))
            ->latest()
            ->paginate($this->perPage($request, 20))
            ->withQueryString();

        return view('attendance.corrections.index', compact('attendances', 'requests'));
    }

    public function store(Request $request): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee !== null, 403);
        $companyId = $this->currentCompanyId($request);
        abort_unless((int) $employee->company_id === $companyId, 403);

        $data = $request->validate([
            'attendance_id' => ['required', 'integer'],
            'requested_check_in' => ['nullable', 'date'],
            'requested_check_out' => ['nullable', 'date'],
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);
        $attendance = Attendance::query()
            ->whereKey($data['attendance_id'])
            ->where('employee_id', $employee->id)
            ->whereHas('employee', fn (Builder $query) => $query->where('company_id', $companyId))
            ->firstOrFail();
        abort_if(AttendanceCorrection::query()->where('attendance_id', $attendance->id)->where('status', 'pending')->exists(), 422, 'A correction request for this attendance record is already pending.');

        $checkIn = filled($data['requested_check_in'] ?? null) ? CarbonImmutable::parse($data['requested_check_in']) : null;
        $checkOut = filled($data['requested_check_out'] ?? null) ? CarbonImmutable::parse($data['requested_check_out']) : null;
        $changedIn = $checkIn && (! $attendance->check_in_at || ! $checkIn->equalTo($attendance->check_in_at));
        $changedOut = $checkOut && (! $attendance->check_out_at || ! $checkOut->equalTo($attendance->check_out_at));
        if (! $changedIn && ! $changedOut) {
            throw ValidationException::withMessages(['requested_check_in' => 'Change either check-in or check-out before submitting.']);
        }
        $effectiveIn = $changedIn ? $checkIn : ($attendance->check_in_at ? CarbonImmutable::parse($attendance->check_in_at) : null);
        $effectiveOut = $changedOut ? $checkOut : ($attendance->check_out_at ? CarbonImmutable::parse($attendance->check_out_at) : null);
        if ($effectiveIn && $effectiveOut && $effectiveOut->lessThanOrEqualTo($effectiveIn)) {
            throw ValidationException::withMessages(['requested_check_out' => 'Check-out must be after check-in.']);
        }
        AttendanceCorrection::query()->create([
            'attendance_id' => $attendance->id,
            'employee_id' => $employee->id,
            'requested_check_in' => $changedIn ? $checkIn : null,
            'requested_check_out' => $changedOut ? $checkOut : null,
            'reason' => trim($data['reason']),
            'status' => 'pending',
        ]);

        return back()->with('status', 'Attendance correction request submitted.');
    }

    public function review(Request $request): View
    {
        $companyId = $this->currentCompanyId($request);
        $actor = $request->user()->employee;
        $query = AttendanceCorrection::query()
            ->with(['attendance', 'employee.branch', 'employee.department'])
            ->where('status', 'pending')
            ->whereHas('employee', fn (Builder $employees) => $employees->where('company_id', $companyId));

        if ($request->user()->hasRole('Manager') && ! $request->user()->hasAnyRole(['Owner', 'Super Admin', 'HR Administrator'])) {
            abort_unless($actor !== null && (int) $actor->company_id === $companyId, 403);
            $query->whereHas('employee', fn (Builder $employees) => $employees
                ->where('company_id', $companyId)
                ->where('department_id', $actor->department_id)
                ->where('id', '!=', $actor->id));
        }

        return view('attendance.corrections.review', [
            'corrections' => $query->latest()->paginate($this->perPage($request, 20))->withQueryString(),
        ]);
    }

    public function approve(Request $request, AttendanceCorrection $correction): RedirectResponse
    {
        $this->authorizeCorrection($request, $correction);
        $this->ensurePayrollIsOpen($correction);
        DB::transaction(function () use ($request, $correction): void {
            $locked = AttendanceCorrection::query()->lockForUpdate()->findOrFail($correction->id);
            abort_unless($locked->status === 'pending', 422, 'This correction has already been reviewed.');
            $attendance = Attendance::query()->lockForUpdate()->findOrFail($locked->attendance_id);
            $before = ['check_in_at' => $attendance->check_in_at?->toISOString(), 'check_out_at' => $attendance->check_out_at?->toISOString(), 'status' => $attendance->status];
            $locked->approve($request->user(), trim((string) $request->input('note')));
            $attendance->refresh();
            AuditLog::record($locked, 'approved_and_applied', $before, ['check_in_at' => $attendance->check_in_at?->toISOString(), 'check_out_at' => $attendance->check_out_at?->toISOString(), 'status' => $attendance->status, 'reason' => $locked->reason]);
        });

        return back()->with('status', 'Attendance correction approved and applied.');
    }

    public function reject(Request $request, AttendanceCorrection $correction): RedirectResponse
    {
        $data = $request->validate(['note' => ['required', 'string', 'min:3', 'max:1000']]);
        $this->authorizeCorrection($request, $correction);
        DB::transaction(function () use ($request, $correction, $data): void {
            $locked = AttendanceCorrection::query()->lockForUpdate()->findOrFail($correction->id);
            abort_unless($locked->status === 'pending', 422, 'This correction has already been reviewed.');
            $locked->reject($request->user(), trim($data['note']));
            AuditLog::record($locked, 'rejected', ['status' => 'pending'], ['status' => 'rejected', 'review_note' => trim($data['note'])]);
        });

        return back()->with('status', 'Attendance correction rejected.');
    }

    public function reopen(Request $request, AttendanceCorrection $correction): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee !== null, 403);
        $companyId = $this->currentCompanyId($request);
        abort_unless((int) $employee->company_id === $companyId, 403);
        abort_unless($correction->employee_id === $employee->id, 404);
        abort_unless($correction->employee()->where('company_id', $companyId)->exists(), 404);
        abort_unless($correction->status === 'rejected', 422, 'Only rejected correction requests can be reopened.');
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:2000']]);

        DB::transaction(function () use ($request, $correction, $data, $companyId): void {
            $locked = AttendanceCorrection::query()->lockForUpdate()->findOrFail($correction->id);
            abort_unless($locked->employee_id === $request->user()->employee->id && $locked->status === 'rejected', 422, 'This correction request cannot be reopened.');
            abort_unless($locked->employee()->where('company_id', $companyId)->exists(), 404);
            $before = ['status' => $locked->status, 'review_note' => $locked->review_note];
            $locked->reopen($request->user(), trim($data['reason']));
            AuditLog::record($locked, 'reopened', $before, ['status' => 'pending', 'reason' => trim($data['reason'])]);
        });

        return back()->with('status', 'Attendance correction reopened for review.');
    }

    private function authorizeCorrection(Request $request, AttendanceCorrection $correction): void
    {
        $companyId = $this->currentCompanyId($request);
        abort_unless($correction->status === 'pending', 422, 'This correction has already been reviewed.');
        abort_unless($correction->employee()->where('company_id', $companyId)->exists(), 404);

        if ($request->user()->hasAnyRole(['Owner', 'Super Admin', 'HR Administrator'])) {
            return;
        }

        $actor = $request->user()->employee;
        abort_unless($actor && (int) $actor->company_id === $companyId, 403);
        if ($request->user()->hasRole('Manager')) {
            abort_unless($correction->employee()->where('department_id', $actor->department_id)->whereKeyNot($actor->id)->exists(), 403);
        }
    }

    private function ensurePayrollIsOpen(AttendanceCorrection $correction): void
    {
        $attendance = $correction->attendance()->with('employee')->firstOrFail();
        $locked = PayrollPeriod::query()
            ->where('company_id', $attendance->employee->company_id)
            ->whereDate('start_date', '<=', $attendance->work_date)
            ->whereDate('end_date', '>=', $attendance->work_date)
            ->whereIn('status', ['approved', 'paid', 'closed'])
            ->exists();
        abort_if($locked, 423, 'Attendance is locked by an approved or closed payroll period.');
    }
}
