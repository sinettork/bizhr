<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateDataExport;
use App\Models\Company;
use App\Models\DataExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DataExportController extends Controller
{
    public function index(Request $request): View
    {
        $exports = DataExport::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate($this->perPage($request, 20))->withQueryString();

        return view('exports.index', compact('exports'));
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        abort_unless(in_array($type, ['employees', 'attendance', 'payroll'], true), 404);

        $permission = match ($type) {
            'employees' => 'employee.view-sensitive',
            'attendance' => 'attendance.report',
            'payroll' => 'payroll.report',
        };

        abort_unless($request->user()->can($permission), 403);

        $companyId = $request->user()->employee->company_id
            ?? Company::query()->value('id');

        abort_unless($companyId, 422, __('exports.company_required'));

        $filters = $request->validate(match ($type) {
            'employees' => [
                'branch_id' => ['nullable', 'integer', Rule::exists('branches', 'id')->where('company_id', $companyId)],
                'department_id' => ['nullable', 'integer', Rule::exists('departments', 'id')->where('company_id', $companyId)],
                'status' => ['nullable', Rule::in(['Draft', 'Active', 'On probation', 'On leave', 'Suspended', 'Resigned', 'Terminated', 'Retired'])],
            ],
            'attendance' => [
                'branch_id' => ['nullable', 'integer', Rule::exists('branches', 'id')->where('company_id', $companyId)],
                'employee_id' => ['nullable', 'integer', Rule::exists('employees', 'id')->where('company_id', $companyId)],
                'status' => ['nullable', Rule::in(['present', 'late', 'absent', 'on_leave', 'half_day', 'holiday', 'rest_day', 'remote_work', 'business_trip'])],
                'date_from' => ['nullable', 'date_format:Y-m-d'],
                'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'],
            ],
            'payroll' => [
                'period_id' => ['nullable', 'integer', Rule::exists('payroll_periods', 'id')->where('company_id', $companyId)],
            ],
        });

        $filters = array_filter($filters, fn ($value) => filled($value));
        $existing = DataExport::query()
            ->where('user_id', $request->user()->id)
            ->where('type', $type)
            ->whereIn('status', ['queued', 'processing'])
            ->where('created_at', '>=', now()->subMinute())
            ->get()
            ->first(fn (DataExport $export) => ($export->filters ?? []) === $filters);

        if ($existing) {
            return redirect()->route('exports.index')->with('success', __('exports.already_queued'));
        }

        $disk = (string) config('bizhr.exports_disk', 'local');

        if (! in_array($disk, ['local', 's3'], true)) {
            throw new RuntimeException('BizHR exports require a private local or S3 disk.');
        }

        $export = DataExport::query()->create([
            'user_id' => $request->user()->id,
            'company_id' => $companyId,
            'type' => $type,
            'filters' => $filters,
            'status' => 'queued',
            'progress' => 0,
            'disk' => $disk,
            'file_name' => 'bizhr-'.$type.'-'.now()->format('Ymd-His').'.xlsx',
        ]);

        GenerateDataExport::dispatch($export->id);

        return redirect()
            ->route('exports.index')
            ->with('success', __('exports.queued'));
    }

    public function download(Request $request, DataExport $dataExport): StreamedResponse
    {
        abort_unless($dataExport->user_id === $request->user()->id, 403);
        abort_unless($dataExport->status === 'completed', 409);
        abort_if($dataExport->expires_at?->isPast(), 410, __('exports.expired'));
        abort_unless($dataExport->file_path && Storage::disk($dataExport->disk)->exists($dataExport->file_path), 404);

        return Storage::disk($dataExport->disk)->download(
            $dataExport->file_path,
            $dataExport->file_name,
            ['Content-Type' => str_ends_with(strtolower($dataExport->file_name), '.xlsx')
                ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                : 'text/csv; charset=UTF-8'],
        );
    }
}
