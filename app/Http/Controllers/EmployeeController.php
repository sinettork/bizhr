<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\EmploymentHistory;
use App\Models\EmploymentType;
use App\Models\Position;
use App\Services\UploadedFileSecurityService;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $company = $this->company();
        $query = Employee::query()
            ->with(['branch:id,name', 'department:id,name', 'position:id,title'])
            ->where('company_id', $company->id);

        if (! $request->user()->can('employee.view')) {
            $query->where('user_id', $request->user()->id);
        }

        if ($request->filled('search')) {
            $term = '%'.trim((string) $request->input('search')).'%';
            $query->where(function ($employees) use ($term): void {
                $employees->where('employee_code', 'like', $term)
                    ->orWhere('first_name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhere('full_name_en', 'like', $term)
                    ->orWhere('full_name_km', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }

        if ($request->filled('status')) {
            $query->where('employment_status', $request->string('status')->toString());
        }

        $employees = $query->orderBy('employee_code')->paginate($this->perPage($request, 20))->withQueryString();

        if ($request->header('HX-Request')) {
            return view('employees._table', compact('employees'));
        }

        return view('employees.index', [
            'employees' => $employees,
            'search' => (string) $request->input('search', ''),
            'status' => (string) $request->input('status', ''),
            'statuses' => $this->statuses(),
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->can('employee.create'), 403);

        return view('employees.form', $this->formData(new Employee([
            'hire_date' => today(),
            'employment_status' => 'Draft',
            'salary_currency' => $this->company()->currency ?: 'USD',
            'is_active' => true,
        ]), 'Create employee'));
    }

    public function store(Request $request, UploadedFileSecurityService $fileSecurity): RedirectResponse
    {
        abort_unless($request->user()->can('employee.create'), 403);

        $this->prepareGeneratedCode($request, 'employee_code', 'employees', 'employee_code', 'EMP', ['first_name', 'last_name', 'full_name_en'], 'company_id', $this->company()->id);

        $employee = DB::transaction(function () use ($request, $fileSecurity): Employee {
            $employee = Employee::query()->create($this->validated($request, null, $fileSecurity));
            $this->recordLifecycleEvent($employee, 'hire', $request->string('change_reason')->toString() ?: 'Initial employee record created.', $request);

            return $employee;
        });

        if ($request->input('save_action') === 'new') {
            return redirect()->route('employees.create')->with('success', 'Employee created successfully. Add the next employee.');
        }

        return redirect()->route('employees.show', $employee)->with('success', 'Employee created successfully.');
    }

    public function show(Request $request, Employee $employee): View
    {
        $this->authorizeEmployee($request, $employee);
        $employee->load([
            'branch',
            'department',
            'position',
            'employmentType',
            'documents',
            'employmentHistories' => fn ($query) => $query->with(['department', 'position', 'recordedBy'])->latest('effective_date'),
        ]);

        return view('employees.show', compact('employee'));
    }

    public function idCard(Request $request, Employee $employee): View
    {
        $this->authorizeEmployee($request, $employee);
        $employee->load(['company', 'branch', 'department', 'position']);

        return view('employees.id-card', compact('employee'));
    }

    public function idCardQr(Request $request, Employee $employee): StreamedResponse
    {
        $this->authorizeEmployee($request, $employee);
        $token = $this->issueIdCardVerificationToken($employee);
        $payload = route('employees.id-card.verify', ['employee' => $employee->public_id, 'token' => $token], false);

        $renderer = new GDLibRenderer(320);
        $png = (new Writer($renderer))->writeString($payload);

        return new StreamedResponse(function () use ($png): void {
            echo $png;
        }, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=300, no-transform',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function idCardVerify(Employee $employee, string $token): JsonResponse
    {
        abort_unless($employee->is_active && $employee->id_card_verification_revoked_at === null && $employee->id_card_verification_expires_at?->isFuture() && hash_equals((string) $employee->id_card_verification_token_hash, hash('sha256', $token)), 404, 'This ID card cannot be verified.');

        return response()->json([
            'valid' => true,
            'employee_code' => $employee->employee_code,
            'status' => $employee->employment_status,
            'expires_at' => $employee->id_card_expiry_date?->toIso8601String(),
            'verified_at' => now()->toIso8601String(),
        ]);
    }

    public function revokeIdCardVerificationToken(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorizeEmployee($request, $employee, true);
        $employee->update(['id_card_verification_revoked_at' => now()]);

        return back()->with('success', 'The public ID-card verification token was revoked.');
    }

    public function photo(Request $request, Employee $employee): StreamedResponse
    {
        $this->authorizeEmployee($request, $employee);
        abort_unless($employee->profile_photo !== null, 404);
        $disk = str_starts_with($employee->profile_photo, 'private/') ? 'local' : 'public';
        abort_unless(Storage::disk($disk)->exists($employee->profile_photo), 404);

        return Storage::disk($disk)->response($employee->profile_photo, null, [
            'Cache-Control' => 'private, max-age=300, no-transform',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function edit(Request $request, Employee $employee): View
    {
        $this->authorizeEmployee($request, $employee, true);

        return view('employees.form', $this->formData($employee, 'Edit employee'));
    }

    public function update(Request $request, Employee $employee, UploadedFileSecurityService $fileSecurity): RedirectResponse
    {
        $this->authorizeEmployee($request, $employee, true);
        $this->prepareGeneratedCode($request, 'employee_code', 'employees', 'employee_code', 'EMP', ['first_name', 'last_name', 'full_name_en'], 'company_id', $this->company()->id, $employee->id);
        $original = $employee->only(['branch_id', 'department_id', 'position_id', 'employment_type_id', 'base_salary', 'salary_currency', 'employment_status']);
        $data = $this->validated($request, $employee, $fileSecurity);
        $trackedChanges = array_filter(
            $original,
            fn ($value, string $key) => $request->exists($key) && (string) $value !== (string) ($data[$key] ?? null),
            ARRAY_FILTER_USE_BOTH,
        );
        if ($trackedChanges !== []) {
            $request->validate(['change_reason' => ['required', 'string', 'min:5', 'max:1000'], 'effective_date' => ['required', 'date']]);
        }
        DB::transaction(function () use ($employee, $data, $trackedChanges, $request): void {
            $employee->update($data);
            if ($trackedChanges !== []) {
                $event = match (true) {
                    array_key_exists('employment_status', $trackedChanges) && in_array($employee->employment_status, ['Resigned', 'Terminated', 'Retired'], true) => strtolower($employee->employment_status),
                    array_key_exists('branch_id', $trackedChanges) || array_key_exists('department_id', $trackedChanges) => 'transfer',
                    array_key_exists('position_id', $trackedChanges) => 'promotion_or_position_change',
                    array_key_exists('base_salary', $trackedChanges) || array_key_exists('salary_currency', $trackedChanges) => 'salary_change',
                    default => 'employment_change',
                };
                $this->recordLifecycleEvent($employee, $event, $request->string('change_reason')->toString(), $request);
            }
            if (in_array($employee->employment_status, ['Resigned', 'Terminated', 'Retired'], true) && $employee->user_id) {
                DB::table('users')->where('id', $employee->user_id)->update(['is_active' => false, 'updated_at' => now()]);
                DB::table('sessions')->where('user_id', $employee->user_id)->delete();
            }
        });

        return redirect()->route('employees.show', $employee)->with('success', 'Employee updated successfully.');
    }

    public function destroy(Request $request, Employee $employee): RedirectResponse
    {
        abort_unless($employee->company_id === $this->company()->id, 404);
        Gate::forUser($request->user())->authorize('delete', $employee);

        DB::transaction(function () use ($employee): void {
            if ($employee->user_id) {
                DB::table('users')->where('id', $employee->user_id)->update(['is_active' => false, 'updated_at' => now()]);
                DB::table('sessions')->where('user_id', $employee->user_id)->delete();
            }
            $employee->delete();
        });

        return redirect()->route('employees.index')->with('success', 'Employee archived successfully.');
    }

    public function requestRehire(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorizeEmployee($request, $employee, true);
        abort_unless(in_array($employee->employment_status, ['Resigned', 'Terminated', 'Retired'], true), 422, 'Only separated employees can be rehired.');
        $data = $request->validate(['effective_date' => ['required', 'date'], 'reason' => ['required', 'string', 'min:15', 'max:1000']]);

        $employee->update([
            'rehire_requested_at' => now(), 'rehire_requested_by' => $request->user()->id,
            'rehire_effective_date' => $data['effective_date'], 'rehire_reason' => trim($data['reason']),
            'rehire_approved_at' => null, 'rehire_approved_by' => null,
        ]);

        return back()->with('success', 'Rehire request submitted for approval.');
    }

    public function approveRehire(Request $request, Employee $employee): RedirectResponse
    {
        $this->authorizeEmployee($request, $employee, true);
        abort_unless($employee->rehire_requested_at !== null && $employee->rehire_approved_at === null, 422, 'There is no pending rehire request.');
        abort_unless($employee->rehire_requested_by !== $request->user()->id, 422, 'The requester cannot approve this rehire.');

        DB::transaction(function () use ($employee, $request): void {
            $employee->update([
                'employment_status' => 'Active', 'is_active' => true,
                'rehire_approved_at' => now(), 'rehire_approved_by' => $request->user()->id,
            ]);
            if ($employee->user_id) {
                DB::table('users')->where('id', $employee->user_id)->update(['is_active' => true, 'updated_at' => now()]);
            }
            $request->merge(['effective_date' => $employee->rehire_effective_date?->toDateString()]);
            $this->recordLifecycleEvent($employee, 'rehire', 'Approved rehire: '.$employee->rehire_reason, $request);
        });

        return back()->with('success', 'Employee rehire approved and activated.');
    }

    /** @return array<string, mixed> */
    private function formData(Employee $employee, string $title): array
    {
        $company = $this->company();

        return [
            'title' => $title,
            'employee' => $employee,
            'branches' => Branch::query()->where('company_id', $company->id)->where('is_active', true)->orderByDesc('is_head_office')->orderBy('name')->get(),
            'departments' => Department::query()->where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get(),
            'positions' => Position::query()->where('company_id', $company->id)->where('is_active', true)->orderBy('sort_order')->orderBy('title')->get(),
            'employmentTypes' => EmploymentType::query()->where('company_id', $company->id)->where('is_active', true)->orderBy('sort_order')->orderBy('name')->get(),
            'statuses' => $this->statuses(),
        ];
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, ?Employee $employee = null, ?UploadedFileSecurityService $fileSecurity = null): array
    {
        $company = $this->company();
        $employeeCode = Rule::unique('employees', 'employee_code');
        if ($employee) {
            $employeeCode->ignore($employee->id);
        }

        $data = $request->validate([
            'branch_id' => ['required', Rule::exists('branches', 'id')->where('company_id', $company->id)],
            'department_id' => ['required', Rule::exists('departments', 'id')->where('company_id', $company->id)->where('branch_id', $request->input('branch_id'))],
            'position_id' => ['nullable', Rule::exists('positions', 'id')->where('company_id', $company->id)->where('branch_id', $request->input('branch_id'))->where('department_id', $request->input('department_id'))],
            'employment_type_id' => ['nullable', Rule::exists('employment_types', 'id')->where('company_id', $company->id)],
            'employee_code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9._-]+$/', $employeeCode],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'full_name_km' => ['nullable', 'string', 'max:255'],
            'full_name_en' => ['nullable', 'string', 'max:255'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'national_id' => ['nullable', 'string', 'max:20'],
            'passport_number' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'hire_date' => ['required', 'date'],
            'probation_end_date' => ['nullable', 'date', 'after_or_equal:hire_date'],
            'contract_start_date' => ['nullable', 'date'],
            'contract_end_date' => ['nullable', 'date', 'after_or_equal:contract_start_date'],
            'base_salary' => ['nullable', 'numeric', 'min:0'],
            'salary_currency' => ['required', Rule::in(['USD', 'KHR'])],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:30'],
            'id_card_expiry_date' => ['nullable', 'date'],
            'employment_status' => ['required', Rule::in($this->statuses())],
            'is_active' => ['nullable', 'boolean'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=120,min_height=120,max_width=3000,max_height=3000'],
            'remove_profile_photo' => ['nullable', 'boolean'],
        ]);

        foreach (['position_id', 'employment_type_id', 'date_of_birth', 'probation_end_date', 'contract_start_date', 'contract_end_date', 'base_salary', 'payment_method', 'bank_name', 'bank_account_name', 'bank_account_number', 'emergency_contact_name', 'emergency_contact_phone', 'id_card_expiry_date', 'national_id', 'passport_number', 'address', 'city', 'phone', 'email', 'full_name_km', 'full_name_en', 'gender'] as $field) {
            $data[$field] = filled($data[$field] ?? null) ? $data[$field] : null;
        }

        $data['employee_code'] = strtoupper(trim($data['employee_code']));
        if (Employee::query()->where('company_id', $company->id)->where('employee_code', $data['employee_code'])->when($employee, fn ($query) => $query->whereKeyNot($employee->id))->exists()) {
            throw ValidationException::withMessages(['employee_code' => 'Employee code already exists. Please choose a unique code.']);
        }
        $data['first_name'] = trim($data['first_name']);
        $data['last_name'] = trim($data['last_name']);
        $data['is_active'] = $request->boolean('is_active');
        $data['company_id'] = $company->id;

        unset($data['remove_profile_photo']);

        if ($request->boolean('remove_profile_photo') && $employee?->profile_photo) {
            $this->deleteProfilePhoto($employee->profile_photo);
            $data['profile_photo'] = null;
        }

        if ($request->hasFile('profile_photo')) {
            ($fileSecurity ?? app(UploadedFileSecurityService::class))->assertSafe($request->file('profile_photo'), 'profile_photo');
            if ($employee?->profile_photo) {
                $this->deleteProfilePhoto($employee->profile_photo);
            }
            $employeeDirectory = $employee?->id ?: 'new';
            $data['profile_photo'] = $request->file('profile_photo')->store("private/companies/{$company->id}/employees/{$employeeDirectory}/profile-photos", 'local');
        } elseif (! $request->boolean('remove_profile_photo')) {
            unset($data['profile_photo']);
        }

        return $data;
    }

    private function authorizeEmployee(Request $request, Employee $employee, bool $editing = false): void
    {
        abort_unless($employee->company_id === $this->company()->id, 404);
        Gate::forUser($request->user())->authorize($editing ? 'update' : 'view', $employee);
    }

    private function issueIdCardVerificationToken(Employee $employee): string
    {
        $expiresAt = $employee->id_card_expiry_date?->copy()->endOfDay() ?? now()->addYear();
        $token = Str::random(64);
        $employee->update([
            'id_card_verification_token_hash' => hash('sha256', $token),
            'id_card_verification_expires_at' => $expiresAt,
            'id_card_verification_revoked_at' => null,
        ]);

        return $token;
    }

    private function deleteProfilePhoto(string $path): void
    {
        Storage::disk(str_starts_with($path, 'private/') ? 'local' : 'public')->delete($path);
    }

    private function recordLifecycleEvent(Employee $employee, string $event, string $reason, Request $request): void
    {
        EmploymentHistory::query()->create([
            'employee_id' => $employee->id,
            'branch_id' => $employee->branch_id,
            'department_id' => $employee->department_id,
            'position_id' => $employee->position_id,
            'employment_type' => $employee->employmentType?->name,
            'event_type' => $event,
            'effective_date' => $request->date('effective_date') ?? $employee->hire_date ?? today(),
            'base_salary' => $employee->base_salary,
            'salary_currency' => $employee->salary_currency,
            'notes' => trim($reason),
            'recorded_by' => $request->user()->id,
        ]);
    }

    private function company(): Company
    {
        return Company::query()->firstOrFail();
    }

    /** @return list<string> */
    private function statuses(): array
    {
        return ['Draft', 'Active', 'On probation', 'On leave', 'Suspended', 'Resigned', 'Terminated', 'Retired'];
    }
}
