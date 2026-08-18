<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeSchedule;
use App\Models\WorkShift;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmployeeScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = Company::query()->value('id');
        abort_unless($companyId, 404);
        $workDate = $request->date('date') ?? CarbonImmutable::today();

        $schedules = EmployeeSchedule::query()
            ->with(['employee.branch', 'employee.department', 'workShift'])
            ->whereDate('work_date', $workDate)
            ->whereHas('employee', fn ($query) => $query->where('company_id', $companyId))
            ->when($request->filled('branch_id'), fn ($query) => $query->where('branch_id', $request->integer('branch_id')))
            ->orderBy('branch_id')
            ->orderBy('employee_id')
            ->paginate($this->perPage($request, 30))
            ->withQueryString();

        return view('attendance.schedules.index', [
            'schedules' => $schedules,
            'workDate' => $workDate,
            'branches' => Branch::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(),
            'employees' => Employee::query()->where('company_id', $companyId)->where('is_active', true)->with('branch')->orderBy('first_name')->orderBy('last_name')->get(),
            'shifts' => WorkShift::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('start_time')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $employee = $this->employeeForCompany($data['employee_id']);
        if (! $employee->branch_id) {
            throw ValidationException::withMessages(['employee_id' => 'Assign the employee to a branch before publishing a schedule.']);
        }

        EmployeeSchedule::query()->create([
            ...$data,
            'branch_id' => $employee->branch_id,
        ]);

        $response = back()->with('status', 'Employee schedule created.');

        return $request->input('save_action') === 'new'
            ? $response->with('open_modal', 'createSchedule')
            : $response;
    }

    public function update(Request $request, EmployeeSchedule $schedule): RedirectResponse
    {
        $this->ensureCompany($schedule);
        $data = $this->validated($request, $schedule);
        $employee = $this->employeeForCompany($data['employee_id']);
        if (! $employee->branch_id) {
            throw ValidationException::withMessages(['employee_id' => 'Assign the employee to a branch before publishing a schedule.']);
        }

        $schedule->update([
            ...$data,
            'branch_id' => $employee->branch_id,
        ]);

        return back()->with('status', 'Employee schedule updated.');
    }

    public function destroy(EmployeeSchedule $schedule): RedirectResponse
    {
        $this->ensureCompany($schedule);
        $schedule->delete();

        return back()->with('status', 'Employee schedule deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?EmployeeSchedule $schedule = null): array
    {
        $companyId = (int) Company::query()->value('id');
        $data = $request->validate([
            'employee_id' => ['required', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'work_date' => ['required', 'date', 'after_or_equal:2000-01-01', 'before_or_equal:2100-12-31', Rule::unique('employee_schedules')->where('employee_id', $request->integer('employee_id'))->ignore($schedule)],
            'work_shift_id' => ['nullable', Rule::exists('work_shifts', 'id')->where('company_id', $companyId)],
            'is_rest_day' => ['boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $isRestDay = $request->boolean('is_rest_day');
        if (! $isRestDay && empty($data['work_shift_id'])) {
            throw ValidationException::withMessages(['work_shift_id' => 'Choose a work shift, or mark the date as a rest day.']);
        }

        return [...$data, 'is_rest_day' => $isRestDay, 'work_shift_id' => $isRestDay ? null : $data['work_shift_id']];
    }

    private function employeeForCompany(int $employeeId): Employee
    {
        return Employee::query()->where('company_id', Company::query()->value('id'))->findOrFail($employeeId);
    }

    private function ensureCompany(EmployeeSchedule $schedule): void
    {
        abort_unless($schedule->employee()->where('company_id', Company::query()->value('id'))->exists(), 404);
    }
}
