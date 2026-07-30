<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmploymentHistory;
use App\Models\Position;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmploymentHistoryController extends Controller
{
    public function index(Employee $employee): View
    {
        $this->authorizeEmployee($employee);

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
        $this->authorizeEmployee($employee, true);

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

        foreach (['branch_id' => Branch::class, 'department_id' => Department::class, 'position_id' => Position::class] as $field => $model) {
            if (! empty($data[$field])) {
                abort_unless($model::whereKey($data[$field])->where('company_id', $employee->company_id)->exists(), 422);
            }
        }

        $employee->employmentHistories()->create([...$data, 'recorded_by' => auth()->id()]);

        return back()->with('status', 'បានបន្ថែមប្រវត្តិការងារដោយជោគជ័យ។');
    }

    public function destroy(Employee $employee, EmploymentHistory $history): RedirectResponse
    {
        $this->authorizeEmployee($employee, true);
        abort_unless($history->employee_id === $employee->id, 404);

        $history->delete();

        return back()->with('status', 'បានលុបកំណត់ត្រាប្រវត្តិការងារ។');
    }

    private function authorizeEmployee(Employee $employee, bool $editing = false): void
    {
        /** @var User|null $user */
        $user = auth()->user();
        abort_unless($user, 403);

        $actor = $user->employee;
        $isOwnRecord = $actor?->id === $employee->id;

        if ($actor && $actor->company_id !== $employee->company_id) {
            abort(403);
        }

        if ($isOwnRecord && ! $editing) {
            abort_unless($user->can('employee.view-own'), 403);

            return;
        }

        if ($user->hasAnyRole(['Manager', 'manager'])) {
            abort(403);
        }

        abort_unless(
            $editing
                ? $user->can('employee.edit') && $user->can('employee.view-sensitive')
                : $user->can('employee.view-sensitive'),
            403
        );
    }
}
