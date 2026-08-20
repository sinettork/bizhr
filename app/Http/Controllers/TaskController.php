<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Task;
use App\Services\TaskWorkflowService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $this->currentCompanyId($request);
        $tasks = Task::query()->with(['employee.department', 'assigner'])->where('company_id', $companyId)
            ->when($request->string('search')->trim()->value(), fn ($query, $search) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")
                ->orWhereHas('employee', fn ($employee) => $employee
                    ->where('employee_code', 'like', "%{$search}%")
                    ->orWhere('full_name_en', 'like', "%{$search}%")
                    ->orWhere('full_name_km', 'like', "%{$search}%"))))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest('due_date')->paginate($this->perPage($request, 20))->withQueryString();

        return view('tasks.index', ['tasks' => $tasks, 'employees' => Employee::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('full_name_en')->get()]);
    }

    public function mine(Request $request): View
    {
        $companyId = $this->currentCompanyId($request);
        $employee = Employee::query()
            ->where('company_id', $companyId)
            ->where('user_id', $request->user()->id)
            ->first();
        abort_unless($employee !== null, 403);

        $baseQuery = Task::query()
            ->where('company_id', $companyId)
            ->where('assigned_to', $employee->id);

        $status = $request->string('status')->trim()->value();
        $tasks = (clone $baseQuery)
            ->with('assigner')
            ->when($status === 'open', fn ($query) => $query->whereNotIn('status', ['verified', 'cancelled']))
            ->when($status === 'overdue', fn ($query) => $query
                ->whereNotIn('status', ['completed', 'verified', 'cancelled'])
                ->whereDate('due_date', '<', today()))
            ->when($status === 'waiting_verification', fn ($query) => $query->where('status', 'waiting_verification'))
            ->when($status === 'completed', fn ($query) => $query->whereIn('status', ['completed', 'verified']))
            ->orderByRaw("case when status in ('verified', 'cancelled') then 1 else 0 end")
            ->orderBy('due_date')
            ->paginate($this->perPage($request, 20))
            ->withQueryString();

        $statistics = [
            'open' => (clone $baseQuery)->whereNotIn('status', ['verified', 'cancelled'])->count(),
            'overdue' => (clone $baseQuery)
                ->whereNotIn('status', ['completed', 'verified', 'cancelled'])
                ->whereDate('due_date', '<', today())
                ->count(),
            'waiting' => (clone $baseQuery)->where('status', 'waiting_verification')->count(),
            'completed' => (clone $baseQuery)->whereIn('status', ['completed', 'verified'])->count(),
        ];

        return view('tasks.mine', compact('tasks', 'statistics', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);
        $data = $request->validate([
            'assigned_to' => ['required', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'start_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);
        $employee = Employee::query()->whereKey($data['assigned_to'])->where('company_id', $companyId)->where('is_active', true)->firstOrFail();
        Task::query()->create(['company_id' => $companyId, 'assigned_by' => $request->user()->id, ...$data, 'assigned_to' => $employee->id]);
        $response = back()->with('action_feedback', 'Task assigned.');

        return $request->input('save_action') === 'new' ? $response->with('open_modal', 'createTask') : $response;
    }

    public function progress(Request $request, Task $task, TaskWorkflowService $workflow): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);
        abort_unless($task->company_id === $companyId, 404);
        $employee = Employee::query()
            ->where('company_id', $companyId)
            ->where('user_id', $request->user()->id)
            ->first();
        abort_unless($employee !== null, 403);
        abort_unless($task->assigned_to === $employee->id, 403);
        $data = $request->validate(['progress' => ['required', 'integer', 'between:0,100'], 'employee_note' => ['nullable', 'string', 'max:2000']]);
        $this->runWorkflow(fn () => $workflow->updateProgress($task, $request->user(), $data['progress'], $data['employee_note'] ?? null));

        return back()->with('action_feedback', 'Task progress updated.');
    }

    public function verify(Request $request, Task $task, TaskWorkflowService $workflow, string $decision): RedirectResponse
    {
        abort_unless($task->company_id === $this->currentCompanyId($request), 404);
        abort_unless(in_array($decision, ['approve', 'return'], true), 404);
        $data = $request->validate(['manager_note' => [$decision === 'return' ? 'required' : 'nullable', 'string', 'min:3', 'max:2000']]);
        $this->runWorkflow(fn () => $workflow->verify($task, $request->user(), $decision === 'approve', $data['manager_note'] ?? null));

        return back()->with('action_feedback', $decision === 'approve' ? 'Task verified.' : 'Task returned to employee.');
    }

    public function update(Request $request, Task $task): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);
        abort_unless($task->company_id === $companyId, 404);
        abort_unless(! in_array($task->status, ['verified', 'cancelled'], true), 422, 'Verified or cancelled tasks are locked.');
        $data = $request->validate([
            'assigned_to' => ['required', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'start_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);
        abort_unless(Employee::query()->whereKey($data['assigned_to'])->where('company_id', $companyId)->where('is_active', true)->exists(), 404);
        $task->update($data);

        return back()->with('action_feedback', 'Task updated.');
    }

    public function cancel(Request $request, Task $task, TaskWorkflowService $workflow): RedirectResponse
    {
        abort_unless($task->company_id === $this->currentCompanyId($request), 404);
        $reason = (string) $request->validate(['reason' => ['required', 'string', 'min:10', 'max:2000']])['reason'];
        $this->runWorkflow(fn () => $workflow->cancel($task, $request->user(), $reason));

        return back()->with('action_feedback', 'Task cancelled with a retained reason.');
    }

    /** @param callable(): mixed $operation */
    private function runWorkflow(callable $operation): mixed
    {
        try {
            return $operation();
        } catch (DomainException $exception) {
            throw ValidationException::withMessages(['workflow' => $exception->getMessage()]);
        }
    }
}
