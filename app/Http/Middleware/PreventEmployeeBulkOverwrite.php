<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventEmployeeBulkOverwrite
{
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if (! $request->isMethod('post') || ! $request->is('imports/employees/confirm')) {
            return $next($request);
        }

        $token = trim((string) $request->input('token'));
        if (strlen($token) !== 48) {
            return $next($request);
        }

        $sessionKey = "bulk_imports.{$token}";
        $saved = $request->session()->get($sessionKey);
        if (! is_array($saved) || ($saved['type'] ?? null) !== 'employees' || ! is_array($saved['rows'] ?? null)) {
            return $next($request);
        }

        $companyId = filter_var($saved['company_id'] ?? null, FILTER_VALIDATE_INT);
        if (! $companyId) {
            return $next($request);
        }

        $codes = collect($saved['rows'])
            ->map(fn ($row): string => strtoupper(trim((string) data_get($row, 'data.employee_code', ''))))
            ->filter()
            ->unique()
            ->values();

        if ($codes->isEmpty()) {
            return $next($request);
        }

        $existing = Employee::query()
            ->where('company_id', $companyId)
            ->whereIn('employee_code', $codes->all())
            ->orderBy('employee_code')
            ->pluck('employee_code');

        if ($existing->isEmpty()) {
            return $next($request);
        }

        $request->session()->forget($sessionKey);

        return redirect()->route('imports.index')->withErrors([
            'file' => 'Employee bulk import is create-only. Existing employee code(s) cannot be overwritten: '.$existing->join(', ').'. Use the employee edit and lifecycle workflow for controlled changes.',
        ]);
    }
}
