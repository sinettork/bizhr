<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $this->companyId();
        $announcements = Announcement::query()->withCount('acknowledgements')->where('company_id', $companyId)->when($request->filled('search'), fn ($q) => $q->where('title', 'like', '%'.trim($request->string('search')).'%'))->latest('is_pinned')->latest('published_at')->paginate($this->perPage($request, 20))->withQueryString();

        return view('announcements.index', ['announcements' => $announcements, 'branches' => Branch::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get(), 'departments' => Department::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $companyId = $this->companyId();
        $this->audienceScope($data, $companyId);
        Announcement::query()->create(['company_id' => $companyId, 'created_by' => $request->user()->id, ...$data, 'published_at' => $request->boolean('publish_now') ? now() : ($data['published_at'] ?? null), 'is_pinned' => $request->boolean('is_pinned'), 'is_urgent' => $request->boolean('is_urgent'), 'requires_acknowledgement' => $request->boolean('requires_acknowledgement')]);
        $response = back()->with('status', 'Announcement created.');

        return $request->input('save_action') === 'new' ? $response->with('open_modal', 'createAnnouncement') : $response;
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        abort_unless($announcement->company_id === $this->companyId(), 404);
        $data = $this->validated($request);
        $this->audienceScope($data, $announcement->company_id);
        $announcement->update([...$data, 'is_pinned' => $request->boolean('is_pinned'), 'is_urgent' => $request->boolean('is_urgent'), 'requires_acknowledgement' => $request->boolean('requires_acknowledgement')]);

        return back()->with('status', 'Announcement updated.');
    }

    public function publish(Announcement $announcement): RedirectResponse
    {
        abort_unless($announcement->company_id === $this->companyId(), 404);
        $announcement->update(['published_at' => now()]);

        return back()->with('status', 'Announcement published.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        abort_unless($announcement->company_id === $this->companyId(), 404);
        $announcement->delete();

        return back()->with('status', 'Announcement archived.');
    }

    public function feed(Request $request): View
    {
        $employee = $request->user()->employee;
        $companyId = $employee->company_id ?? $this->companyId();
        $items = Announcement::query()->withExists(['acknowledgements as acknowledged_by_me' => fn ($query) => $query->where('users.id', $request->user()->id)])->where('company_id', $companyId)->whereNotNull('published_at')->where('published_at', '<=', now())->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))->when($employee, fn ($q) => $q->where(fn ($audience) => $audience->where('audience_type', 'all')->orWhere(fn ($x) => $x->where('audience_type', 'branch')->where('branch_id', $employee->branch_id))->orWhere(fn ($x) => $x->where('audience_type', 'department')->where('department_id', $employee->department_id))))->latest('is_urgent')->latest('is_pinned')->latest('published_at')->paginate($this->perPage($request, 20))->withQueryString();

        return view('announcements.feed', compact('items'));
    }

    public function acknowledge(Request $request, Announcement $announcement): RedirectResponse
    {
        $employee = $request->user()->employee;
        abort_unless($announcement->company_id === ($employee->company_id ?? $this->companyId()), 404);
        abort_unless($announcement->audience_type === 'all'
            || ($announcement->audience_type === 'branch' && $employee !== null && $announcement->branch_id === $employee->branch_id)
            || ($announcement->audience_type === 'department' && $employee !== null && $announcement->department_id === $employee->department_id), 404);
        abort_unless($announcement->requires_acknowledgement && $announcement->published_at !== null && $announcement->published_at <= now() && ($announcement->expires_at === null || $announcement->expires_at >= now()), 422);
        $announcement->acknowledgements()->syncWithoutDetaching([$request->user()->id => ['acknowledged_at' => now(), 'ip_address' => $request->ip()]]);

        return back()->with('status', 'Announcement acknowledged.');
    }

    /** @return array{title: string, content: string, audience_type: string, branch_id: int|null, department_id: int|null, expires_at: string|null, published_at: string|null} */
    private function validated(Request $request): array
    {
        return $request->validate(['title' => ['required', 'string', 'max:200'], 'content' => ['required', 'string', 'max:10000'], 'audience_type' => ['required', 'in:all,branch,department'], 'branch_id' => ['nullable', 'integer', 'exists:branches,id'], 'department_id' => ['nullable', 'integer', 'exists:departments,id'], 'published_at' => ['nullable', 'date'], 'expires_at' => ['nullable', 'date', 'after:published_at']]);
    }

    /** @param array{title: string, content: string, audience_type: string, branch_id: int|null, department_id: int|null, expires_at: string|null, published_at: string|null} $data */
    private function audienceScope(array &$data, int $companyId): void
    {
        if ($data['audience_type'] !== 'branch') {
            $data['branch_id'] = null;
        } if ($data['audience_type'] !== 'department') {
            $data['department_id'] = null;
        } if ($data['branch_id']) {
            abort_unless(Branch::query()->whereKey($data['branch_id'])->where('company_id', $companyId)->exists(), 404);
        } if ($data['department_id']) {
            abort_unless(Department::query()->whereKey($data['department_id'])->where('company_id', $companyId)->exists(), 404);
        }
    }

    private function companyId(): int
    {
        return (int) Company::query()->value('id');
    }
}
