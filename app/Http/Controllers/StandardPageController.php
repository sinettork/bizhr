<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use App\Models\PayrollPeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class StandardPageController extends Controller
{
    /**
     * Render a server-side record screen for modules awaiting their dedicated
     * create/edit workflow. Every page is data-backed and permission-protected.
     */
    public function show(Request $request, string $page): View
    {
        $modules = $this->modules();
        abort_unless(isset($modules[$page]), 404);
        $module = $modules[$page];
        $model = $module['model'];
        $query = $model::query();

        if (Schema::hasColumn($query->getModel()->getTable(), 'company_id')) {
            $query->where('company_id', Company::query()->value('id') ?: 0);
        }

        if (($module['mine'] ?? false) === true) {
            $column = $module['owner_column'] ?? 'employee_id';
            $ownerId = $column === 'id' ? $request->user()?->id : $request->user()?->employee?->id;
            $query->where($column, $ownerId ?: 0);
        }

        if ($request->filled('search') && ! empty($module['search'])) {
            $term = '%'.trim((string) $request->input('search')).'%';
            $query->where(function (Builder $searchQuery) use ($module, $term): void {
                foreach ($module['search'] as $index => $column) {
                    $index === 0
                        ? $searchQuery->where($column, 'like', $term)
                        : $searchQuery->orWhere($column, 'like', $term);
                }
            });
        }

        if ($request->filled('status') && ! empty($module['has_status'])) {
            $query->where($module['status_column'] ?? 'status', $request->string('status'));
        }

        $records = $query->latest('id')->paginate($this->perPage($request, 20))->withQueryString();
        $statusOptions = ! empty($module['has_status'])
            ? $model::query()->whereNotNull($module['status_column'] ?? 'status')->distinct()->orderBy($module['status_column'] ?? 'status')->pluck($module['status_column'] ?? 'status')->filter()->values()
            : collect();
        $workspaceMode = match (true) {
            str_starts_with($page, 'my-') || in_array($page, ['my-payroll', 'my-training', 'my-assets', 'my-expenses'], true) => 'personal',
            str_contains($page, 'review') || str_contains($page, 'corrections') || in_array($page, ['expenses', 'tasks'], true) => 'queue',
            str_contains($page, 'report') || $page === 'audit-logs' => 'report',
            in_array($page, ['payroll-statutory', 'payroll-reports'], true) => 'directory-placeholder',
            $page === 'payroll-statutory' => 'configuration',
            default => 'directory',
        };
        $workspaceContext = match ($workspaceMode) {
            'personal' => 'My work',
            'queue' => 'Review queue',
            'report' => 'Reporting',
            'directory-placeholder' => '[PLACEHOLDER] Read-only Directory',
            'configuration' => 'Administration',
            default => 'Directory',
        };
        $summary = [
            ['label' => 'Records shown', 'value' => number_format($records->total())],
            ['label' => $hasStatusLabel = ! empty($module['has_status']) ? 'Current filter' : 'Workspace', 'value' => ! empty($module['has_status']) && $request->filled('status') ? ucfirst(str_replace('_', ' ', $request->string('status'))) : ucfirst($workspaceMode)],
        ];

        return view('pages.standard', [
            'title' => $module['title'],
            'icon' => $module['icon'],
            'page' => $page,
            'description' => $module['description'],
            'columns' => $module['columns'],
            'records' => $records,
            'statusOptions' => $statusOptions,
            'hasStatus' => (bool) ($module['has_status'] ?? false),
            'search' => (string) $request->input('search', ''),
            'status' => (string) $request->input('status', ''),
            'statusColumn' => $module['status_column'] ?? 'status',
            'workspaceMode' => $workspaceMode,
            'workspaceContext' => 'Read-only '.$workspaceContext,
            'summary' => $summary,
            'isPlaceholder' => $workspaceMode === 'directory-placeholder',
        ]);
    }

    /** @return array<string, array<string, mixed>> */
    private function modules(): array
    {
        return [
            'payroll-statutory' => $this->module('Statutory profiles', 'fa-file-invoice-dollar', Employee::class, 'Employee statutory and payroll profile records.', ['employee_code', 'full_name_en', 'base_salary', 'salary_currency', 'employment_status'], ['employee_code', 'full_name_en'], true),
            'payroll-reports' => $this->module('Payroll reports', 'fa-chart-column', PayrollPeriod::class, 'Completed payroll periods available for reporting.', ['name', 'start_date', 'end_date', 'payment_date', 'status'], ['name'], true),
        ];
    }

    /**
     * @param  class-string<Model>  $model
     * @param  list<string>  $columns
     * @param  list<string>  $search
     * @return array<string, mixed>
     */
    private function module(string $title, string $icon, string $model, string $description, array $columns, array $search, bool $hasStatus, bool $mine = false, string $ownerColumn = 'employee_id', string $statusColumn = 'status'): array
    {
        return compact('title', 'icon', 'model', 'description', 'columns', 'search', 'hasStatus', 'mine', 'ownerColumn', 'statusColumn');
    }
}
