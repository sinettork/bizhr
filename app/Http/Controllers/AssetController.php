<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Company;
use App\Models\Employee;
use App\Services\AssetWorkflowService;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $this->companyId();
        $assets = Asset::query()->where('company_id', $companyId)->withCount(['assignments as open_assignments' => fn ($q) => $q->where('status', 'assigned')])->when($request->filled('search'), fn ($q) => $q->where(fn ($x) => $x->where('asset_code', 'like', '%'.trim($request->string('search')).'%')->orWhere('name', 'like', '%'.trim($request->string('search')).'%')))->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))->latest()->paginate($this->perPage($request, 20))->withQueryString();

        return view('assets.index', ['assets' => $assets, 'employees' => Employee::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('full_name_en')->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Asset::query()->create(['company_id' => $this->companyId(), ...$data]);
        $response = back()->with('status', 'Asset created.');

        return $request->input('save_action') === 'new' ? $response->with('open_modal', 'createAsset') : $response;
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        abort_unless($asset->company_id === $this->companyId(), 404);
        Gate::forUser($request->user())->authorize('manage', $asset);
        $data = $this->validated($request, $asset);
        if ($asset->status === 'assigned') {
            unset($data['condition']);
        }
        $asset->update($data);

        return back()->with('status', 'Asset updated.');
    }

    public function destroy(Request $request, Asset $asset): RedirectResponse
    {
        abort_unless($asset->company_id === $this->companyId(), 404);
        Gate::forUser($request->user())->authorize('manage', $asset);
        if ($asset->assignments()->where('status', 'assigned')->exists()) {
            return back()->withErrors(['asset' => 'Return the assigned asset before archiving it.']);
        }
        $asset->update(['status' => 'retired']);
        $asset->delete();

        return back()->with('status', 'Asset retired and archived; assignment history was retained.');
    }

    public function assign(Request $request, Asset $asset, AssetWorkflowService $workflow): RedirectResponse
    {
        abort_unless($asset->company_id === $this->companyId(), 404);
        Gate::forUser($request->user())->authorize('manage', $asset);
        $data = $request->validate(['employee_id' => ['required', 'integer', 'exists:employees,id'], 'condition_out' => ['required', 'in:new,good,fair,poor'], 'expected_return_date' => ['nullable', 'date', 'after_or_equal:today']]);
        $employee = Employee::query()->whereKey($data['employee_id'])->where('company_id', $asset->company_id)->firstOrFail();
        $this->runWorkflow(fn () => $workflow->assign($asset, $employee, $request->user(), $data['condition_out'], $data['expected_return_date'] ?? null));

        return back()->with('status', 'Asset assigned.');
    }

    public function receive(Request $request, AssetAssignment $assignment, AssetWorkflowService $workflow): RedirectResponse
    {
        abort_unless($assignment->asset()->where('company_id', $this->companyId())->exists(), 404);
        if (! $request->user()->can('asset.manage')) {
            abort_unless($assignment->employee_id === $request->user()->employee?->id, 403);
        } $data = $request->validate(['condition_in' => ['required', 'in:new,good,fair,poor,lost,retired'], 'notes' => ['nullable', 'string', 'max:2000']]);
        $this->runWorkflow(fn () => $workflow->receive($assignment, $request->user(), $data['condition_in'], $data['notes'] ?? null));

        return back()->with('status', 'Asset return recorded.');
    }

    public function mine(Request $request): View
    {
        abort_unless($request->user()->employee !== null, 403);

        return view('assets.mine', ['assignments' => AssetAssignment::query()->with('asset')->where('employee_id', $request->user()->employee->id)->latest('assigned_date')->paginate($this->perPage($request, 20))->withQueryString()]);
    }

    private function companyId(): int
    {
        return (int) Company::query()->value('id');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Asset $asset = null): array
    {
        return $request->validate(['asset_code' => ['required', 'string', 'max:80', Rule::unique('assets')->where('company_id', $this->companyId())->ignore($asset)], 'name' => ['required', 'string', 'max:200'], 'category' => ['required', 'string', 'max:100'], 'serial_number' => ['nullable', 'string', 'max:150', Rule::unique('assets')->where('company_id', $this->companyId())->ignore($asset)], 'purchase_date' => ['nullable', 'date'], 'purchase_cost' => ['nullable', 'numeric', 'min:0'], 'currency' => ['required', 'in:USD,KHR'], 'condition' => ['required', 'in:new,good,fair,poor'], 'notes' => ['nullable', 'string', 'max:2000']]);
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
