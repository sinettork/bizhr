<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmploymentHistory;
use App\Models\Branch;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmploymentHistoryController extends Controller
{
    public function index(Employee $employee): View
    {
        abort_unless(auth()->user()?->can('employee.view'), 403);

        return view('employees.history', [
            'employee' => $employee->load(['branch', 'department', 'position', 'employmentType']),
            'histories' => $employee->employmentHistories()->with(['branch', 'department', 'position', 'recordedBy'])->latest('effective_date')->get(),
            'branches' => Branch::where('company_id', $employee->company_id)->where('is_active', true)->orderBy('name')->get(),
            'departments' => Department::with('branch')
                ->where('company_id', $employee->company_id)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
            'positions' => Position::where('company_id', $employee->company_id)->where('is_active', true)->orderBy('title')->get(),
        ]);
    }

    public function store(Request $request, Employee $employee): RedirectResponse
    {
        abort_unless(auth()->user()?->can('employee.edit'), 403);

        $data = $request->validate([
            'event_type' => ['required', 'in:Hired,Transfer,Promotion,Position change,Salary adjustment,Contract renewal,Status change,Other'],
            'effective_date' => ['required', 'date'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'position_id' => ['nullable', 'integer', 'exists:positions,id'],
            'employment_type' => ['nullable', 'string', 'max:255'],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'salary_currency' => ['nullable', 'in:USD,KHR'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $employee->employmentHistories()->create([...$data, 'recorded_by' => auth()->id()]);

        return back()->with('status', 'Employment history entry added.');
    }

    public function destroy(Employee $employee, EmploymentHistory $history): RedirectResponse
    {
        abort_unless(auth()->user()?->can('employee.edit'), 403);
        abort_unless($history->employee_id === $employee->id, 404);

        $history->delete();

        return back()->with('status', 'Employment history entry removed.');
    }
}
