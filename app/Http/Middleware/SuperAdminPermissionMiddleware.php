<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware as SpatiePermissionMiddleware;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminPermissionMiddleware
{
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if ($request->user()?->hasRole('Super Admin')) {
            return $next($request);
        }

        return app(SpatiePermissionMiddleware::class)->handle($request, $next, ...$permissions);
    }
}
