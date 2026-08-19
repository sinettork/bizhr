<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Employee;
use App\Services\AssetWorkflowService;
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

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $this->currentCompanyId($request);
        $assets = Asset::query()
            ->where('company_id', $companyId)
            ->withCount(['assignments as open_assignments' => fn ($q) => $q->where('status', 'assigned')])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $term = '%'.trim((string) $request->input('search')).'%';
                $query->where(function ($assets) use ($term): void {
                    $assets->where('asset_code', 'like', $term)
                        ->orWhere('name', 'like', $term)
                        ->orWhere('category', 'like', $term)
                        ->orWhere('serial_number', 'like', $term);
                });
            })
            ->when($request->filled('category'), fn ($q) => $q->where('category', $request->string('category')->toString()))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate($this->perPage($request, 20))
            ->withQueryString();

        $categories = Asset::query()
            ->where('company_id', $companyId)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('assets.index', [
            'assets' => $assets,
            'categories' => $categories,
            'employees' => Employee::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('full_name_en')->get(),
        ]);
    }

    public function store(Request $request, UploadedFileSecurityService $fileSecurity): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);
        $data = $this->validated($request, null, $companyId);
        $image = $request->file('image');
        if ($image instanceof UploadedFile) {
            $fileSecurity->assertSafe($image, 'image');
            $storedPath = $image->store('assets/'.$companyId, 'public');
            if (! is_string($storedPath)) {
                throw new RuntimeException('Unable to store the asset image.');
            }
            $data['image_path'] = $storedPath;
        }

        Asset::query()->create(['company_id' => $companyId, ...$data]);
        $response = back()->with('status', 'Asset created.');

        return $request->input('save_action') === 'new' ? $response->with('open_modal', 'createAsset') : $response;
    }

    public function update(Request $request, Asset $asset, UploadedFileSecurityService $fileSecurity): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);
        abort_unless($asset->company_id === $companyId, 404);
        Gate::forUser($request->user())->authorize('manage', $asset);
        $data = $this->validated($request, $asset, $companyId);
        if ($asset->status === 'assigned') {
            unset($data['condition']);
        }

        $image = $request->file('image');
        $newImagePath = null;
        if ($image instanceof UploadedFile) {
            $fileSecurity->assertSafe($image, 'image');
            $storedPath = $image->store('assets/'.$companyId, 'public');
            if (! is_string($storedPath)) {
                throw new RuntimeException('Unable to store the asset image.');
            }
            $newImagePath = $storedPath;
            $data['image_path'] = $newImagePath;
        }

        $previousImagePath = $asset->image_path;
        try {
            $asset->update($data);
        } catch (\Throwable $exception) {
            if ($newImagePath !== null) {
                Storage::disk('public')->delete($newImagePath);
            }
            throw $exception;
        }

        if ($newImagePath !== null && $previousImagePath && $previousImagePath !== $newImagePath) {
            Storage::disk('public')->delete($previousImagePath);
        }

        return back()->with('status', 'Asset updated.');
    }

    public function destroy(Request $request, Asset $asset): RedirectResponse
    {
        abort_unless($asset->company_id === $this->currentCompanyId($request), 404);
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
        $companyId = $this->currentCompanyId($request);
        abort_unless($asset->company_id === $companyId, 404);
        Gate::forUser($request->user())->authorize('manage', $asset);
        $data = $request->validate([
            'employee_id' => ['required', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
            'condition_out' => ['required', 'in:new,good,fair,poor'],
            'expected_return_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);
        $employee = Employee::query()->whereKey($data['employee_id'])->where('company_id', $companyId)->firstOrFail();
        $this->runWorkflow(fn () => $workflow->assign($asset, $employee, $request->user(), $data['condition_out'], $data['expected_return_date'] ?? null));

        return back()->with('status', 'Asset assigned.');
    }

    public function receive(Request $request, AssetAssignment $assignment, AssetWorkflowService $workflow): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);
        abort_unless($assignment->asset()->where('company_id', $companyId)->exists(), 404);
        if (! $request->user()->can('asset.manage')) {
            $employeeId = Employee::query()->where('user_id', $request->user()->id)->value('id');
            abort_unless((int) $assignment->employee_id === (int) $employeeId, 403);
        }
        $data = $request->validate(['condition_in' => ['required', 'in:new,good,fair,poor,lost,retired'], 'notes' => ['nullable', 'string', 'max:2000']]);
        $this->runWorkflow(fn () => $workflow->receive($assignment, $request->user(), $data['condition_in'], $data['notes'] ?? null));

        return back()->with('status', 'Asset return recorded.');
    }

    public function mine(Request $request): View
    {
        $employee = Employee::query()->where('user_id', $request->user()->id)->first();
        abort_unless($employee !== null, 403);
        abort_unless($employee->company_id === $this->currentCompanyId($request), 403);

        return view('assets.mine', [
            'assignments' => AssetAssignment::query()
                ->with('asset')
                ->where('employee_id', $employee->id)
                ->whereHas('asset', fn ($q) => $q->where('company_id', $employee->company_id))
                ->latest('assigned_date')
                ->paginate($this->perPage($request, 20))
                ->withQueryString(),
        ]);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Asset $asset, int $companyId): array
    {
        $data = $request->validate([
            'asset_code' => ['required', 'string', 'max:80', Rule::unique('assets')->where('company_id', $companyId)->ignore($asset)],
            'name' => ['required', 'string', 'max:200'],
            'category' => ['required', 'string', 'max:100'],
            'serial_number' => ['nullable', 'string', 'max:150', Rule::unique('assets')->where('company_id', $companyId)->ignore($asset)],
            'purchase_date' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'in:USD,KHR'],
            'condition' => ['required', 'in:new,good,fair,poor'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096', 'dimensions:max_width=2400,max_height=2400'],
        ]);
        unset($data['image']);

        return $data;
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
