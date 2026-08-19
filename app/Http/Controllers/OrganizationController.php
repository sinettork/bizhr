<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\EmploymentType;
use App\Models\Position;
use App\Services\UploadedFileSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function company(Request $request): View
    {
        abort_unless($request->user()?->can('company.view'), 403);
        $company = Company::query()->findOrFail($this->currentCompanyId($request));

        return view('organization.company', compact('company'));
    }

    public function updateCompany(Request $request, UploadedFileSecurityService $fileSecurity): RedirectResponse
    {
        abort_unless($request->user()?->can('company.edit'), 403);
        $company = Company::query()->findOrFail($this->currentCompanyId($request));
        $data = $request->validate([
            'name' => ['required', 'string', 'max:180'], 'legal_name' => ['nullable', 'string', 'max:180'], 'local_name' => ['nullable', 'string', 'max:180'], 'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'], 'website' => ['nullable', 'url', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:100'], 'tax_id' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:1000'], 'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'], 'currency' => ['required', Rule::in(['USD', 'KHR'])],
            'timezone' => ['required', 'timezone'], 'date_format' => ['required', 'string', 'max:30'],
            'fiscal_year_start_month' => ['required', 'integer', 'between:1,12'], 'week_start_day' => ['required', 'integer', 'between:0,6'],
            'number_format' => ['required', Rule::in(['1,234.56', '1.234,56', '1 234,56'])], 'locale' => ['required', Rule::in(['en', 'km'])],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=100,min_height=50,max_width=3000,max_height=3000'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);
        unset($data['logo'], $data['remove_logo']);
        if ($request->boolean('remove_logo') && $company->logo_path) {
            Storage::disk('public')->delete($company->logo_path);
            $data['logo_path'] = null;
        }
        if ($request->hasFile('logo')) {
            $fileSecurity->assertSafe($request->file('logo'), 'logo');
            if ($company->logo_path) {
                Storage::disk('public')->delete($company->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('company/logos', 'public');
        }
        $company->update($data);

        return back()->with('status', 'Company settings saved.');
    }

    public function branches(Request $request): View
    {
        abort_unless($request->user()?->can('branch.view'), 403);
        $companyId = $this->currentCompanyId($request);

        $branches = Branch::query()
            ->withCount(['departments', 'employees'])
            ->where('company_id', $companyId)
            ->when($request->string('search')->trim()->value(), fn ($query, $search) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->boolean('status')))
            ->orderByDesc('is_head_office')
            ->orderBy('name')
            ->paginate($this->perPage($request, 20))
            ->withQueryString();

        return view('organization.branches', compact('branches'));
    }

    public function storeBranch(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('branch.create'), 403);
        $companyId = $this->currentCompanyId($request);
        $this->prepareGeneratedCode($request, 'code', 'branches', 'code', 'BR', ['name'], 'company_id', $companyId);
        $data = $request->validate(['name' => ['required', 'string', 'max:180'], 'code' => ['required', 'string', 'max:30', Rule::unique('branches')->where('company_id', $companyId)], 'manager_name' => ['nullable', 'string', 'max:180'], 'address' => ['nullable', 'string', 'max:1000'], 'city' => ['nullable', 'string', 'max:100'], 'phone' => ['nullable', 'string', 'max:50'], 'email' => ['nullable', 'email', 'max:255'], 'is_head_office' => ['boolean'], 'is_active' => ['boolean']]);
        Branch::query()->create([...$data, 'company_id' => $companyId, 'is_head_office' => $request->boolean('is_head_office'), 'is_active' => $request->boolean('is_active', true)]);

        return $this->createdResponse($request, 'Branch created.', 'branchForm');
    }

    public function updateBranch(Request $request, Branch $branch): RedirectResponse
    {
        $this->ensureCompany($branch, $request);
        $this->prepareGeneratedCode($request, 'code', 'branches', 'code', 'BR', ['name'], 'company_id', $branch->company_id, $branch->id);
        $branch->update($this->branchData($request, $branch));

        return back()->with('status', 'Branch updated.');
    }

    public function destroyBranch(Request $request, Branch $branch): RedirectResponse
    {
        $this->ensureCompany($branch, $request);
        if ($branch->is_head_office || $branch->employees()->exists() || $branch->departments()->exists()) {
            return back()->withErrors(['branch' => 'A head office or referenced branch cannot be deleted. Set it inactive instead.']);
        }
        $branch->delete();

        return back()->with('status', 'Branch deleted.');
    }

    public function departments(Request $request): View
    {
        abort_unless($request->user()?->can('department.view'), 403);
        $companyId = $this->currentCompanyId($request);

        $departments = Department::query()->with('branch')->withCount('employees')->where('company_id', $companyId)
            ->when($request->string('search')->trim()->value(), fn ($query, $search) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))
            ->when($request->filled('branch_id'), fn ($query) => $query->where('branch_id', $request->integer('branch_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->boolean('status')))
            ->orderBy('name')->paginate($this->perPage($request, 20))->withQueryString();
        $branches = Branch::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();

        return view('organization.departments', compact('departments', 'branches'));
    }

    public function storeDepartment(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('department.create'), 403);
        $companyId = $this->currentCompanyId($request);
        $this->prepareGeneratedCode($request, 'code', 'departments', 'code', 'DEPT', ['name'], 'company_id', $companyId);
        $data = $request->validate(['name' => ['required', 'string', 'max:180'], 'code' => ['required', 'string', 'max:30', Rule::unique('departments')->where('company_id', $companyId)], 'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $companyId)], 'manager_name' => ['nullable', 'string', 'max:180'], 'phone' => ['nullable', 'string', 'max:50'], 'email' => ['nullable', 'email', 'max:255'], 'description' => ['nullable', 'string', 'max:2000'], 'is_active' => ['boolean']]);
        Department::query()->create([...$data, 'company_id' => $companyId, 'is_active' => $request->boolean('is_active', true)]);

        return $this->createdResponse($request, 'Department created.', 'departmentForm');
    }

    public function updateDepartment(Request $request, Department $department): RedirectResponse
    {
        $this->ensureCompany($department, $request);
        $this->prepareGeneratedCode($request, 'code', 'departments', 'code', 'DEPT', ['name'], 'company_id', $department->company_id, $department->id);
        $department->update($this->departmentData($request, $department));

        return back()->with('status', 'Department updated.');
    }

    public function destroyDepartment(Request $request, Department $department): RedirectResponse
    {
        $this->ensureCompany($department, $request);
        if ($department->employees()->exists() || $department->positions()->exists()) {
            return back()->withErrors(['department' => 'A referenced department cannot be deleted. Set it inactive instead.']);
        }
        $department->delete();

        return back()->with('status', 'Department deleted.');
    }

    public function positions(Request $request): View
    {
        abort_unless($request->user()?->can('position.view'), 403);
        $companyId = $this->currentCompanyId($request);

        $positions = Position::query()->with(['branch', 'department'])->where('company_id', $companyId)
            ->when($request->string('search')->trim()->value(), fn ($query, $search) => $query->where(fn ($query) => $query
                ->where('title', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))
            ->when($request->filled('department_id'), fn ($query) => $query->where('department_id', $request->integer('department_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->boolean('status')))
            ->orderBy('sort_order')->orderBy('title')->paginate($this->perPage($request, 20))->withQueryString();
        $branches = Branch::query()->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();
        $departments = Department::query()->with('branch')->where('company_id', $companyId)->where('is_active', true)->orderBy('name')->get();

        return view('organization.positions', compact('positions', 'branches', 'departments'));
    }

    public function storePosition(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('position.create'), 403);
        $companyId = $this->currentCompanyId($request);
        $this->prepareGeneratedCode($request, 'code', 'positions', 'code', 'POS', ['title'], 'company_id', $companyId);
        $data = $request->validate(['title' => ['required', 'string', 'max:180'], 'code' => ['required', 'string', 'max:30', Rule::unique('positions')->where('company_id', $companyId)], 'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $companyId)], 'department_id' => ['nullable', Rule::exists('departments', 'id')->where('company_id', $companyId)], 'description' => ['nullable', 'string', 'max:2000'], 'minimum_salary' => ['nullable', 'numeric', 'min:0'], 'maximum_salary' => ['nullable', 'numeric', 'gte:minimum_salary'], 'sort_order' => ['nullable', 'integer', 'min:0'], 'is_manager_position' => ['boolean'], 'is_active' => ['boolean']]);
        if (! empty($data['branch_id']) && ! empty($data['department_id'])) {
            abort_unless(Department::query()->whereKey($data['department_id'])->where('company_id', $companyId)->where('branch_id', $data['branch_id'])->exists(), 422, 'The department must belong to the selected branch.');
        }
        Position::query()->create([...$data, 'company_id' => $companyId, 'sort_order' => $data['sort_order'] ?? 0, 'is_manager_position' => $request->boolean('is_manager_position'), 'is_active' => $request->boolean('is_active', true)]);

        return $this->createdResponse($request, 'Position created.', 'positionForm');
    }

    public function updatePosition(Request $request, Position $position): RedirectResponse
    {
        $this->ensureCompany($position, $request);
        $this->prepareGeneratedCode($request, 'code', 'positions', 'code', 'POS', ['title'], 'company_id', $position->company_id, $position->id);
        $position->update($this->positionData($request, $position));

        return back()->with('status', 'Position updated.');
    }

    public function destroyPosition(Request $request, Position $position): RedirectResponse
    {
        $this->ensureCompany($position, $request);
        if ($position->employees()->exists()) {
            return back()->withErrors(['position' => 'A position assigned to employees cannot be deleted. Set it inactive instead.']);
        }
        $position->delete();

        return back()->with('status', 'Position deleted.');
    }

    public function employmentTypes(Request $request): View
    {
        abort_unless($request->user()?->can('employment-type.view'), 403);
        $companyId = $this->currentCompanyId($request);

        $types = EmploymentType::query()->withCount('employees')->where('company_id', $companyId)
            ->when($request->string('search')->trim()->value(), fn ($query, $search) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%")))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->boolean('status')))
            ->orderBy('sort_order')->orderBy('name')->paginate($this->perPage($request, 20))->withQueryString();

        return view('organization.employment-types', compact('types'));
    }

    public function storeEmploymentType(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->can('employment-type.create'), 403);
        $companyId = $this->currentCompanyId($request);
        $this->prepareGeneratedCode($request, 'code', 'employment_types', 'code', 'TYPE', ['name'], 'company_id', $companyId);
        $data = $request->validate(['name' => ['required', 'string', 'max:180'], 'code' => ['required', 'string', 'max:30', Rule::unique('employment_types')->where('company_id', $companyId)], 'description' => ['nullable', 'string', 'max:2000'], 'sort_order' => ['nullable', 'integer', 'min:0'], 'is_active' => ['boolean']]);
        EmploymentType::query()->create([...$data, 'company_id' => $companyId, 'sort_order' => $data['sort_order'] ?? 0, 'is_active' => $request->boolean('is_active', true)]);

        return $this->createdResponse($request, 'Employment type created.', 'typeForm');
    }

    public function updateEmploymentType(Request $request, EmploymentType $employmentType): RedirectResponse
    {
        $this->ensureCompany($employmentType, $request);
        $this->prepareGeneratedCode($request, 'code', 'employment_types', 'code', 'TYPE', ['name'], 'company_id', $employmentType->company_id, $employmentType->id);
        $employmentType->update($this->employmentTypeData($request, $employmentType));

        return back()->with('status', 'Employment type updated.');
    }

    public function destroyEmploymentType(Request $request, EmploymentType $employmentType): RedirectResponse
    {
        $this->ensureCompany($employmentType, $request);
        if ($employmentType->employees()->exists()) {
            return back()->withErrors(['employment_type' => 'An employment type assigned to employees cannot be deleted. Set it inactive instead.']);
        }
        $employmentType->delete();

        return back()->with('status', 'Employment type deleted.');
    }

    /** @return array<string, mixed> */
    private function branchData(Request $request, ?Branch $branch = null): array
    {
        $companyId = $this->currentCompanyId($request);
        $data = $request->validate(['name' => ['required', 'string', 'max:180'], 'code' => ['required', 'string', 'max:30', Rule::unique('branches')->where('company_id', $companyId)->ignore($branch)], 'manager_name' => ['nullable', 'string', 'max:180'], 'address' => ['nullable', 'string', 'max:1000'], 'city' => ['nullable', 'string', 'max:100'], 'phone' => ['nullable', 'string', 'max:50'], 'email' => ['nullable', 'email', 'max:255'], 'is_head_office' => ['boolean'], 'is_active' => ['boolean']]);
        $data['is_head_office'] = $request->boolean('is_head_office');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    /** @return array<string, mixed> */
    private function departmentData(Request $request, ?Department $department = null): array
    {
        $companyId = $this->currentCompanyId($request);
        $data = $request->validate(['name' => ['required', 'string', 'max:180'], 'code' => ['required', 'string', 'max:30', Rule::unique('departments')->where('company_id', $companyId)->ignore($department)], 'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $companyId)], 'manager_name' => ['nullable', 'string', 'max:180'], 'phone' => ['nullable', 'string', 'max:50'], 'email' => ['nullable', 'email', 'max:255'], 'description' => ['nullable', 'string', 'max:2000'], 'is_active' => ['boolean']]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    /** @return array<string, mixed> */
    private function positionData(Request $request, ?Position $position = null): array
    {
        $companyId = $this->currentCompanyId($request);
        $data = $request->validate(['title' => ['required', 'string', 'max:180'], 'code' => ['required', 'string', 'max:30', Rule::unique('positions')->where('company_id', $companyId)->ignore($position)], 'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('company_id', $companyId)], 'department_id' => ['nullable', Rule::exists('departments', 'id')->where('company_id', $companyId)], 'description' => ['nullable', 'string', 'max:2000'], 'minimum_salary' => ['nullable', 'numeric', 'min:0'], 'maximum_salary' => ['nullable', 'numeric', 'gte:minimum_salary'], 'sort_order' => ['nullable', 'integer', 'min:0'], 'is_manager_position' => ['boolean'], 'is_active' => ['boolean']]);
        if (! empty($data['branch_id']) && ! empty($data['department_id'])) {
            abort_unless(Department::query()->whereKey($data['department_id'])->where('company_id', $companyId)->where('branch_id', $data['branch_id'])->exists(), 422, 'The department must belong to the selected branch.');
        }
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_manager_position'] = $request->boolean('is_manager_position');
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    /** @return array<string, mixed> */
    private function employmentTypeData(Request $request, ?EmploymentType $employmentType = null): array
    {
        $companyId = $this->currentCompanyId($request);
        $data = $request->validate(['name' => ['required', 'string', 'max:180'], 'code' => ['required', 'string', 'max:30', Rule::unique('employment_types')->where('company_id', $companyId)->ignore($employmentType)], 'description' => ['nullable', 'string', 'max:2000'], 'sort_order' => ['nullable', 'integer', 'min:0'], 'is_active' => ['boolean']]);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }

    private function ensureCompany(Branch|Department|Position|EmploymentType $record, Request $request): void
    {
        abort_unless((int) $record->company_id === $this->currentCompanyId($request), 404);
    }

    private function createdResponse(Request $request, string $message, string $modal): RedirectResponse
    {
        $response = back()->with('status', $message);

        return $request->input('save_action') === 'new'
            ? $response->with('open_modal', $modal)
            : $response;
    }
}
