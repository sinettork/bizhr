<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use App\Models\TrainingCourse;
use App\Models\TrainingEnrollment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $this->companyId();
        $courses = TrainingCourse::query()->where('company_id', $companyId)->withCount('enrollments')->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.trim($request->string('search')).'%'))->latest()->paginate($this->perPage($request, 20))->withQueryString();

        return view('training.index', ['courses' => $courses, 'employees' => Employee::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('full_name_en')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(['title' => ['required', 'string', 'max:200'], 'description' => ['nullable', 'string', 'max:5000'], 'duration_minutes' => ['required', 'integer', 'min:0', 'max:100000'], 'is_mandatory' => ['nullable', 'boolean']]);
        TrainingCourse::query()->create(['company_id' => $this->companyId(), 'created_by' => $request->user()->id, ...$data, 'is_mandatory' => $request->boolean('is_mandatory'), 'is_active' => true]);
        $response = back()->with('status', 'Training course created.');

        return $request->input('save_action') === 'new' ? $response->with('open_modal', 'createCourse') : $response;
    }

    public function enroll(Request $request, TrainingCourse $course): RedirectResponse
    {
        abort_unless($course->company_id === $this->companyId(), 404);
        $data = $request->validate(['employee_id' => ['required', 'integer', 'exists:employees,id'], 'due_date' => ['nullable', 'date', 'after_or_equal:today']]);
        abort_unless(Employee::query()->whereKey($data['employee_id'])->where('company_id', $course->company_id)->exists(), 404);
        TrainingEnrollment::query()->firstOrCreate(['training_course_id' => $course->id, 'employee_id' => $data['employee_id']], ['status' => 'assigned', 'progress' => 0, 'due_date' => $data['due_date'] ?? null, 'assigned_by' => $request->user()->id]);

        return back()->with('status', 'Training assigned.');
    }

    public function update(Request $request, TrainingCourse $course): RedirectResponse
    {
        abort_unless($course->company_id === $this->companyId(), 404);
        $data = $request->validate(['title' => ['required', 'string', 'max:200'], 'description' => ['nullable', 'string', 'max:5000'], 'duration_minutes' => ['required', 'integer', 'min:0', 'max:100000']]);
        $course->update([...$data, 'is_mandatory' => $request->boolean('is_mandatory'), 'is_active' => $request->boolean('is_active')]);

        return back()->with('status', 'Training course updated.');
    }

    public function destroy(TrainingCourse $course): RedirectResponse
    {
        abort_unless($course->company_id === $this->companyId(), 404);
        if ($course->enrollments()->whereNotIn('status', ['completed', 'cancelled'])->exists()) {
            return back()->withErrors(['course' => 'Complete or cancel active enrollments before archiving this course.']);
        }
        $course->update(['is_active' => false]);
        $course->delete();

        return back()->with('status', 'Training course archived; enrollment history was retained.');
    }

    public function mine(Request $request): View
    {
        abort_unless($request->user()->employee !== null, 403);

        return view('training.mine', ['enrollments' => TrainingEnrollment::query()->with('course')->where('employee_id', $request->user()->employee->id)->latest()->paginate($this->perPage($request, 20))->withQueryString()]);
    }

    public function progress(Request $request, TrainingEnrollment $enrollment): RedirectResponse
    {
        abort_unless($enrollment->employee_id === $request->user()->employee?->id, 403);
        $data = $request->validate(['progress' => ['required', 'integer', 'between:0,100'], 'score' => ['nullable', 'numeric', 'between:0,100']]);
        $enrollment->update(['progress' => $data['progress'], 'score' => $data['score'] ?? $enrollment->score, 'status' => $data['progress'] === 100 ? 'completed' : 'in_progress', 'started_at' => $enrollment->started_at ?? now(), 'completed_at' => $data['progress'] === 100 ? now() : null]);

        return back()->with('status', 'Training progress updated.');
    }

    private function companyId(): int
    {
        return (int) Company::query()->value('id');
    }
}
