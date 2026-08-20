<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use App\Services\EmployeeOffboardingReadinessService;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class GuardEmployeeArchiveReadiness
{
    public function __construct(
        private readonly EmployeeOffboardingReadinessService $readiness,
    ) {}

    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if (! $request->routeIs('employees.destroy')) {
            return $next($request);
        }

        $employee = $request->route('employee');
        if (! $employee instanceof Employee) {
            return $next($request);
        }

        if (! in_array($employee->employment_status, ['Resigned', 'Terminated', 'Retired'], true)) {
            return back()->withErrors([
                'employee' => 'Separate the employee first by setting the correct Resigned, Terminated, or Retired status before archiving the record.',
            ]);
        }

        $readiness = $this->readiness->forEmployee($employee);
        if (! $readiness['ready']) {
            return back()->withErrors([
                'employee' => 'Employee offboarding still has '.$readiness['outstanding'].' outstanding area'.($readiness['outstanding'] === 1 ? '' : 's').'. Resolve the offboarding readiness checklist before archiving the record.',
            ]);
        }

        return $next($request);
    }
}
