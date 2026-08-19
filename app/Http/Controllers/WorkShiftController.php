<?php

namespace App\Http\Controllers;

use App\Models\WorkShift;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WorkShiftController extends Controller
{
    public function index(Request $request): View
    {
        $companyId = $this->currentCompanyId($request);

        $shifts = WorkShift::query()
            ->where('company_id', $companyId)
            ->withCount('schedules')
            ->when($request->string('search')->trim()->value(), fn ($query, $search) => $query->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")))
            ->when($request->filled('status'), fn ($query) => $query->where('is_active', $request->boolean('status')))
            ->orderByDesc('is_active')
            ->orderBy('start_time')
            ->paginate($this->perPage($request, 20))
            ->withQueryString();

        return view('attendance.work-shifts.index', compact('shifts'));
    }

    public function store(Request $request): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);
        $this->prepareGeneratedCode($request, 'code', 'work_shifts', 'code', 'SHIFT', ['name'], 'company_id', $companyId);
        WorkShift::query()->create($this->validated($request, $companyId) + ['company_id' => $companyId]);

        $response = back()->with('status', 'Work shift created.');

        return $request->input('save_action') === 'new'
            ? $response->with('open_modal', 'createWorkShift')
            : $response;
    }

    public function update(Request $request, WorkShift $workShift): RedirectResponse
    {
        $this->ensureCompany($request, $workShift);
        $this->prepareGeneratedCode($request, 'code', 'work_shifts', 'code', 'SHIFT', ['name'], 'company_id', $workShift->company_id, $workShift->id);
        $workShift->update($this->validated($request, $workShift->company_id, $workShift));

        return back()->with('status', 'Work shift updated.');
    }

    public function destroy(Request $request, WorkShift $workShift): RedirectResponse
    {
        $this->ensureCompany($request, $workShift);

        if ($workShift->schedules()->exists()) {
            return back()->withErrors(['work_shift' => 'This shift is assigned to employee schedules. Set it inactive instead of deleting it.']);
        }

        $workShift->delete();

        return back()->with('status', 'Work shift deleted.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, int $companyId, ?WorkShift $workShift = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['required', 'string', 'max:50', Rule::unique('work_shifts')->where('company_id', $companyId)->ignore($workShift)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'break_minutes' => ['required', 'integer', 'min:0', 'max:720'],
            'late_grace_minutes' => ['required', 'integer', 'min:0', 'max:240'],
            'early_leave_grace_minutes' => ['required', 'integer', 'min:0', 'max:240'],
            'is_night_shift' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $isNightShift = $request->boolean('is_night_shift');
        $start = Carbon::createFromFormat('H:i', $data['start_time']);
        $end = Carbon::createFromFormat('H:i', $data['end_time']);
        if ($isNightShift && $end->lessThanOrEqualTo($start)) {
            $end->addDay();
        }
        $duration = $start->diffInMinutes($end) - $data['break_minutes'];

        if ($duration <= 0 || $duration > 16 * 60) {
            throw ValidationException::withMessages(['end_time' => 'Enter a valid working duration after the break.']);
        }
        if (! $isNightShift && $end->lessThanOrEqualTo($start)) {
            throw ValidationException::withMessages(['end_time' => 'A daytime shift must end after it starts. Use “overnight shift” when it crosses midnight.']);
        }

        return [
            ...$data,
            'is_night_shift' => $isNightShift,
            'is_active' => $request->boolean('is_active', true),
        ];
    }

    private function ensureCompany(Request $request, WorkShift $workShift): void
    {
        abort_unless((int) $workShift->company_id === $this->currentCompanyId($request), 404);
    }
}
