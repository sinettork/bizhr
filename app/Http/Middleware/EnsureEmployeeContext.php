<?php

namespace App\Http\Middleware;

use App\Models\Employee;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployeeContext
{
    /** @var list<string> */
    private const SELF_SERVICE_ROUTES = [
        'leave.requests.index',
        'leave.requests.store',
        'leave.requests.withdraw',
        'payroll.my-payslips',
        'performance.my-goals',
        'performance.my-goals.update',
        'performance.my-reviews',
        'performance.my-reviews.acknowledge',
        'tasks.mine',
        'tasks.progress',
        'training.mine',
        'training.progress',
        'assets.mine',
        'expenses.mine',
        'expenses.store',
        'contracts.mine',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->route()?->getName(), self::SELF_SERVICE_ROUTES, true)) {
            return $next($request);
        }

        $user = $request->user();
        abort_unless($user !== null, 401);

        $companyId = $user->companyId();
        abort_unless($companyId !== null, 403, 'Your account is not linked to a company context.');

        $isPureSuperAdmin = $user->hasRole('Super Admin')
            && $user->roles()->where('name', '!=', 'Super Admin')->doesntExist();
        abort_if($isPureSuperAdmin, 403, 'This workspace is for employee self-service accounts.');

        $employee = Employee::query()
            ->where('company_id', $companyId)
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->whereNotIn('employment_status', ['Resigned', 'Terminated', 'Retired'])
            ->first();

        abort_unless($employee !== null, 403, 'Your account is not linked to an active employee record.');

        $request->attributes->set('employee_context', $employee);

        return $next($request);
    }
}
