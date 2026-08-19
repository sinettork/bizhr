<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\JobApplicant;
use App\Models\JobVacancy;
use App\Models\Position;
use App\Services\RecruitmentWorkflowService;
use App\Services\UploadedFileSecurityService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class RecruitmentController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $this->currentCompanyId($request);
        $vacancies = JobVacancy::query()->with(['branch', 'position'])->withCount('applicants')->where('company_id', $companyId)
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.trim((string) $request->input('search')).'%'))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->latest('open_date')->paginate($this->perPage($request, 20))->withQueryString();
        $applicants = JobApplicant::query()->with('vacancy')->whereHas('vacancy', fn ($query) => $query->where('company_id', $companyId))->latest('applied_at')->limit(100)->get();

        return view('recruitment.index', [
            'vacancies' => $vacancies,
            'applicants' => $applicants,
            'branches' => Branch::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(),
            'positions' => Position::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('title')->get(),
        ]);
    }

    public function storeVacancy(Request $request): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'openings' => ['required', 'integer', 'min:1', 'max:1000'],
            'position_id' => ['nullable', Rule::exists('positions', 'id')->where('company_id', $companyId)],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $companyId)],
            'open_date' => ['required', 'date'],
            'close_date' => ['nullable', 'date', 'after_or_equal:open_date'],
        ]);
        JobVacancy::query()->create([...$data, 'company_id' => $companyId, 'created_by' => $request->user()->id, 'status' => 'open']);

        return back()->with('status', 'Vacancy opened.');
    }

    public function storeApplicant(Request $request, JobVacancy $vacancy, UploadedFileSecurityService $fileSecurity): RedirectResponse
    {
        $this->ensureCompany($vacancy, $request);
        Gate::forUser($request->user())->authorize('manage', $vacancy);
        abort_unless($vacancy->status === 'open', 422, 'Applications are accepted only for open vacancies.');
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('job_applicants', 'email')->where('job_vacancy_id', $vacancy->id)],
            'phone' => ['required', 'string', 'max:50'],
            'cv' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'hr_note' => ['nullable', 'string', 'max:2000'],
        ]);
        $file = $request->file('cv');
        if ($file instanceof UploadedFile) {
            $fileSecurity->assertSafe($file, 'cv');
        }
        $disk = $this->recruitmentDisk();
        $vacancy->applicants()->create([
            ...array_diff_key($data, ['cv' => true]),
            'cv_path' => $file instanceof UploadedFile ? $file->store("recruitment/{$vacancy->id}", $disk) : null,
            'cv_original_name' => $file instanceof UploadedFile ? $file->getClientOriginalName() : null,
            'status' => 'applied',
            'applied_at' => now(),
        ]);

        return back()->with('status', 'Candidate added to the pipeline.');
    }

    public function transition(Request $request, JobApplicant $applicant, RecruitmentWorkflowService $workflow): RedirectResponse
    {
        $this->ensureCompany($applicant->vacancy, $request);
        Gate::forUser($request->user())->authorize('manage', $applicant);
        $data = $request->validate(['status' => ['required', 'string'], 'note' => ['nullable', 'string', 'max:2000']]);
        try {
            $workflow->moveApplicant($applicant, $data['status'], $data['note'] ?? null);
        } catch (DomainException $exception) {
            throw ValidationException::withMessages(['status' => $exception->getMessage()]);
        }

        return back()->with('status', 'Candidate stage updated.');
    }

    public function downloadCv(Request $request, JobApplicant $applicant): StreamedResponse
    {
        $this->ensureCompany($applicant->vacancy, $request);
        Gate::forUser($request->user())->authorize('view', $applicant);
        $disk = $this->recruitmentDisk();
        abort_unless($applicant->cv_path && Storage::disk($disk)->exists($applicant->cv_path), 404);

        return Storage::disk($disk)->download($applicant->cv_path, $applicant->cv_original_name ?: 'candidate-cv');
    }

    private function ensureCompany(JobVacancy $vacancy, Request $request): void
    {
        abort_unless($vacancy->company_id === $this->currentCompanyId($request), 404);
    }

    private function recruitmentDisk(): string
    {
        $disk = (string) config('bizhr.recruitment_disk', 'local');

        if (! in_array($disk, ['local', 's3'], true)) {
            throw new RuntimeException('BizHR recruitment files require a private local or S3 disk.');
        }

        return $disk;
    }
}
