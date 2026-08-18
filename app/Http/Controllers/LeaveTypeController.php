<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\LeaveType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LeaveTypeController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = Company::query()->value('id');
        abort_unless($companyId, 404);

        $types = LeaveType::query()
            ->where('company_id', $companyId)
            ->withCount(['requests', 'balances'])
            ->when($request->string('search')->trim()->value(), fn ($query, $search) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->boolean('status')))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate($this->perPage($request, 20))
            ->withQueryString();

        return view('leave.types.index', compact('types'));
    }

    public function store(Request $request): RedirectResponse
    {
        $company = Company::query()->firstOrFail();
        $this->prepareGeneratedCode($request, 'code', 'leave_types', 'code', 'LEAVE', ['name'], 'company_id', $company->id);
        LeaveType::query()->create($this->validated($request, $company->id) + ['company_id' => $company->id]);

        $response = back()->with('status', 'Leave type created.');

        return $request->input('save_action') === 'new'
            ? $response->with('open_modal', 'createLeaveType')
            : $response;
    }

    public function update(Request $request, LeaveType $leaveType): RedirectResponse
    {
        $this->ensureCompany($leaveType);
        $this->prepareGeneratedCode($request, 'code', 'leave_types', 'code', 'LEAVE', ['name'], 'company_id', $leaveType->company_id, $leaveType->id);
        $leaveType->update($this->validated($request, $leaveType->company_id, $leaveType));

        return back()->with('status', 'Leave type updated.');
    }

    public function destroy(LeaveType $leaveType): RedirectResponse
    {
        $this->ensureCompany($leaveType);

        if ($leaveType->requests()->exists() || $leaveType->balances()->exists()) {
            return back()->withErrors(['leave_type' => 'This leave type already has requests or balances. Set it inactive instead of deleting it.']);
        }

        $leaveType->delete();

        return back()->with('status', 'Leave type deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, int $companyId, ?LeaveType $leaveType = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:50', Rule::unique('leave_types')->where('company_id', $companyId)->ignore($leaveType)],
            'days_per_year' => ['required', 'numeric', 'min:0', 'max:366'],
            'is_statutory_annual_leave' => ['boolean'],
            'maximum_carry_forward_days' => ['nullable', 'numeric', 'min:0', 'max:366'],
            'is_paid' => ['boolean'],
            'requires_attachment' => ['boolean'],
            'carry_forward_allowed' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $isStatutoryAnnualLeave = $request->boolean('is_statutory_annual_leave');
        if ($isStatutoryAnnualLeave && (float) $data['days_per_year'] < 18) {
            throw ValidationException::withMessages(['days_per_year' => 'Statutory annual leave must be at least 18 days per year.']);
        }
        if ($isStatutoryAnnualLeave && LeaveType::query()->where('company_id', $companyId)->where('is_statutory_annual_leave', true)->when($leaveType, fn ($query) => $query->whereKeyNot($leaveType->id))->exists()) {
            throw ValidationException::withMessages(['is_statutory_annual_leave' => 'Only one statutory annual leave policy can be active for a company.']);
        }

        return [
            ...$data,
            'is_statutory_annual_leave' => $isStatutoryAnnualLeave,
            'is_paid' => $request->boolean('is_paid'),
            'requires_attachment' => $request->boolean('requires_attachment'),
            'carry_forward_allowed' => $request->boolean('carry_forward_allowed'),
            'maximum_carry_forward_days' => $request->boolean('carry_forward_allowed') ? ($data['maximum_carry_forward_days'] ?? 0) : 0,
            'is_active' => $request->boolean('is_active', true),
        ];
    }

    private function ensureCompany(LeaveType $leaveType): void
    {
        abort_unless((int) $leaveType->company_id === (int) Company::query()->value('id'), 404);
    }
}
