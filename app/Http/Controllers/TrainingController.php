<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\TrainingCourse;
use App\Models\TrainingEnrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $this->currentCompanyId($request);
        $courses = TrainingCourse::query()->where('company_id', $companyId)->withCount('enrollments')->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.trim($request->string('search')).'%'))->latest()->paginate($this->perPage($request, 20))->withQueryString();

        return view('training.index', ['courses' => $courses, 'employees' => Employee::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('full_name_en')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:200'], 'description' => ['nullable', 'string', 'max:5000'], 'duration_minutes' => ['required', 'integer', 'min:0', 'max:100000'], 'is_mandatory' => ['nullable', 'boolean']]);
        TrainingCourse::query()->create(['company_id' => $this->currentCompanyId($request), 'created_by' => $request->user()->id, ...$data, 'is_mandatory' => $request->boolean('is_mandatory'), 'is_active' => true]);
        $response = back()->with('status', 'Training course created.');

        return $request->input('save_action') === 'new' ? $response->with('open_modal', 'createCourse') : $response;
    }

    public function enroll(Request $request, TrainingCourse $course): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);
        abort_unless($course->company_id === $companyId, 404);
        $data = $request->validate([
            'employee_id' => ['required', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'due_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);
        $employee = Employee::query()->whereKey($data['employee_id'])->where('company_id', $companyId)->firstOrFail();
        TrainingEnrollment::query()->firstOrCreate(
            ['training_course_id' => $course->id, 'employee_id' => $employee->id],
            ['status' => 'assigned', 'progress' => 0, 'due_date' => $data['due_date'] ?? null, 'assigned_by' => $request->user()->id],
        );

        return back()->with('status', 'Training assigned.');
    }

    public function update(Request $request, TrainingCourse $course): RedirectResponse
    {
        abort_unless($course->company_id === $this->currentCompanyId($request), 404);
        $data = $request->validate(['title' => ['required', 'string', 'max:200'], 'description' => ['nullable', 'string', 'max:5000'], 'duration_minutes' => ['required', 'integer', 'min:0', 'max:100000']]);
        $course->update([...$data, 'is_mandatory' => $request->boolean('is_mandatory'), 'is_active' => $request->boolean('is_active')]);

        return back()->with('status', 'Training course updated.');
    }

    public function destroy(Request $request, TrainingCourse $course): RedirectResponse
    {
        abort_unless($course->company_id === $this->currentCompanyId($request), 404);
        if ($course->enrollments()->whereNotIn('status', ['completed', 'cancelled'])->exists()) {
            return back()->withErrors(['course' => 'Complete or cancel active enrollments before archiving this course.']);
        }
        $course->update(['is_active' => false]);
        $course->delete();

        return back()->with('status', 'Training course archived; enrollment history was retained.');
    }

    public function mine(Request $request): View
    {
        $employee = $request->user()->employee;
        abort_unless($employee !== null, 403);
        $companyId = $this->currentCompanyId($request);
        abort_unless($employee->company_id === $companyId, 403);

        return view('training.mine', [
            'enrollments' => TrainingEnrollment::query()
                ->with('course')
                ->where('employee_id', $employee->id)
                ->whereHas('course', fn ($q) => $q->where('company_id', $companyId))
                ->latest()
                ->paginate($this->perPage($request, 20))
                ->withQueryString(),
        ]);
    }

    public function progress(Request $request, TrainingEnrollment $enrollment): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_unless($employee !== null, 403);
        $companyId = $this->currentCompanyId($request);
        abort_unless($enrollment->employee_id === $employee->id, 403);
        abort_unless($enrollment->course()->where('company_id', $companyId)->exists(), 404);
        $data = $request->validate(['progress' => ['required', 'integer', 'between:0,100'], 'score' => ['nullable', 'numeric', 'between:0,100']]);
        $enrollment->update(['progress' => $data['progress'], 'score' => $data['score'] ?? $enrollment->score, 'status' => $data['progress'] === 100 ? 'completed' : 'in_progress', 'started_at' => $enrollment->started_at ?? now(), 'completed_at' => $data['progress'] === 100 ? now() : null]);

        return back()->with('status', 'Training progress updated.');
    }
}
