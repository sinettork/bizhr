<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeeGoal;
use App\Models\KpiTemplate;
use App\Models\PerformanceReview;
use App\Models\Position;
use App\Services\PerformanceReviewService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PerformanceController extends Controller
{
    public function templates(Request $request): View
    {
        $companyId = $this->currentCompanyId($request);

        return view('performance.templates', [
            'templates' => KpiTemplate::query()->with(['position', 'items'])->where('company_id', $companyId)->paginate($this->perPage($request, 20))->withQueryString(),
            'positions' => Position::query()
                ->with(['department:id,name', 'branch:id,name'])
                ->where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('title')
                ->orderBy('department_id')
                ->get(),
        ]);
    }

    public function storeTemplate(Request $request): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);
        $rawItems = $request->input('items');
        $items = [];
        if (is_array($rawItems)) {
            foreach ($rawItems as $item) {
                if (! is_array($item)) {
                    continue;
                }
                foreach ($item as $value) {
                    if (is_scalar($value) && trim((string) $value) !== '') {
                        $items[] = $item;
                        break;
                    }
                }
            }
        }
        $request->merge(['items' => $items]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('kpi_templates')->where('company_id', $companyId)->where('position_id', $request->input('position_id'))],
            'description' => ['nullable', 'string', 'max:2000'],
            'position_id' => ['nullable', Rule::exists('positions', 'id')->where('company_id', $companyId)],
            'review_frequency' => ['required', 'in:monthly,quarterly,semiannual,annual'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.measurement_unit' => ['required', 'string', 'max:30'],
            'items.*.target_value' => ['required', 'numeric'],
            'items.*.weight' => ['required', 'numeric', 'gt:0', 'lte:100'],
        ]);
        $totalWeight = array_reduce($data['items'], fn (float $total, array $item): float => $total + (float) $item['weight'], 0.0);
        if (abs($totalWeight - 100) >= 0.001) {
            throw ValidationException::withMessages(['items' => 'KPI weights must total exactly 100%.']);
        }
        $template = KpiTemplate::query()->create([...array_diff_key($data, ['items' => true]), 'company_id' => $companyId, 'created_by' => $request->user()->id, 'is_active' => true]);
        foreach ($data['items'] as $index => $item) {
            $template->items()->create([...$item, 'scoring_direction' => 'higher_is_better', 'sort_order' => $index + 1]);
        }

        return back()->with('status', 'KPI template created.');
    }

    public function goals(Request $request): View
    {
        $companyId = $this->currentCompanyId($request);
        $goals = EmployeeGoal::query()->with('employee')->where('company_id', $companyId)->latest('due_date')->paginate($this->perPage($request, 20))->withQueryString();

        return view('performance.goals', ['goals' => $goals, 'employees' => Employee::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('full_name_en')->get()]);
    }

    public function storeGoal(Request $request): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);
        $data = $request->validate(['employee_id' => ['required', Rule::exists('employees', 'id')->where('company_id', $companyId)], 'title' => ['required', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:2000'], 'measurement_unit' => ['required', 'string', 'max:30'], 'target_value' => ['required', 'numeric'], 'weight' => ['required', 'numeric', 'gt:0', 'lte:100'], 'start_date' => ['required', 'date'], 'due_date' => ['required', 'date', 'after_or_equal:start_date']]);
        EmployeeGoal::query()->create([...$data, 'company_id' => $companyId, 'current_value' => 0, 'scoring_direction' => 'higher_is_better', 'status' => 'active', 'assigned_by' => $request->user()->id, 'activated_at' => now()]);

        return back()->with('status', 'Goal assigned.');
    }

    public function myGoals(Request $request): View
    {
        $companyId = $this->currentCompanyId($request);
        $employee = Employee::query()->where('user_id', $request->user()->id)->where('company_id', $companyId)->first();
        abort_unless($employee !== null, 403);

        return view('performance.my-goals', ['goals' => EmployeeGoal::query()->where('employee_id', $employee->id)->where('company_id', $companyId)->latest('due_date')->paginate($this->perPage($request, 20))->withQueryString()]);
    }

    public function updateGoal(Request $request, EmployeeGoal $goal): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);
        $employee = Employee::query()->where('user_id', $request->user()->id)->where('company_id', $companyId)->first();
        abort_unless($employee !== null && (int) $goal->employee_id === (int) $employee->id && (int) $goal->company_id === $companyId, 403);
        $data = $request->validate(['employee_reported_value' => ['required', 'numeric'], 'employee_note' => ['required', 'string', 'min:3', 'max:2000']]);
        abort_unless(in_array($goal->status, ['active', 'returned'], true), 422);
        $goal->update([...$data, 'status' => 'submitted', 'submitted_at' => now()]);

        return back()->with('status', 'Goal progress submitted.');
    }

    public function reviews(Request $request): View
    {
        $companyId = $this->currentCompanyId($request);

        return view('performance.reviews', ['reviews' => PerformanceReview::query()->with(['employee.department', 'reviewer', 'scores'])->where('company_id', $companyId)->latest('period_end')->paginate($this->perPage($request, 20))->withQueryString(), 'employees' => Employee::query()->where('company_id', $companyId)->where('is_active', true)->get()]);
    }

    public function createReview(Request $request, PerformanceReviewService $service): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);
        $data = $request->validate(['employee_id' => ['required', Rule::exists('employees', 'id')->where('company_id', $companyId)], 'period_start' => ['required', 'date'], 'period_end' => ['required', 'date', 'after_or_equal:period_start']]);
        $employee = Employee::query()->whereKey($data['employee_id'])->where('company_id', $companyId)->firstOrFail();
        $this->runWorkflow(fn () => $service->create($employee, $request->user(), $data['period_start'], $data['period_end']));

        return back()->with('status', 'Performance review created from eligible goals.');
    }

    public function submitReview(Request $request, PerformanceReview $review, PerformanceReviewService $service): RedirectResponse
    {
        abort_unless($review->company_id === $this->currentCompanyId($request), 404);
        $data = $request->validate([
            'scores' => ['required', 'array'], 'scores.*' => ['required', 'integer', 'between:1,5'],
            'comments' => ['nullable', 'array'], 'comments.*' => ['nullable', 'string', 'max:2000'],
            'strengths' => ['nullable', 'string', 'max:2000'],
            'areas_for_improvement' => ['nullable', 'string', 'max:2000'],
            'manager_comment' => ['nullable', 'string', 'max:2000'],
        ]);
        $this->runWorkflow(fn () => $service->submit($review, $request->user(), $data['scores'], $data['comments'] ?? [], $data));

        return back()->with('status', 'Performance review submitted for HR approval.');
    }

    public function transition(Request $request, PerformanceReview $review, string $action, PerformanceReviewService $service): RedirectResponse
    {
        abort_unless($review->company_id === $this->currentCompanyId($request), 404);
        abort_unless(match ($action) {
            'approve', 'close' => $request->user()->can('performance.approve'),
            'reopen' => $request->user()->can('performance.reopen'),
            default => false,
        }, 403);
        $this->runWorkflow(fn () => match ($action) {
            'approve' => $service->approve($review, $request->user()), 'close' => $service->close($review, $request->user()), 'reopen' => $service->reopen($review, $request->user(), (string) $request->validate(['reason' => ['required', 'string', 'min:15']])['reason']), default => abort(404)
        });

        return back()->with('status', 'Review workflow updated.');
    }

    public function myReviews(Request $request): View
    {
        $companyId = $this->currentCompanyId($request);
        $employee = Employee::query()->where('user_id', $request->user()->id)->where('company_id', $companyId)->first();
        abort_unless($employee !== null, 403);

        return view('performance.my-reviews', ['reviews' => PerformanceReview::query()->with('scores')->where('employee_id', $employee->id)->where('company_id', $companyId)->latest('period_end')->paginate($this->perPage($request, 20))->withQueryString()]);
    }

    public function acknowledge(Request $request, PerformanceReview $review, PerformanceReviewService $service): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);
        $employee = Employee::query()->where('user_id', $request->user()->id)->where('company_id', $companyId)->first();
        abort_unless($employee !== null && (int) $review->company_id === $companyId, 404);
        $data = $request->validate(['comment' => ['nullable', 'string', 'max:2000']]);
        $this->runWorkflow(fn () => $service->acknowledge($review, $request->user(), $data['comment'] ?? null));

        return back()->with('status', 'Review acknowledged.');
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
