<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  Role names or "level:N" to check by minimum level
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // If no roles specified, just check if user is authenticated
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user has any of the required roles (by name, level alias, or level:N)
        foreach ($roles as $role) {
            $roleName = strtolower(trim($role));

            // Support "level:N" syntax — user must have level <= N
            if (str_starts_with($roleName, 'level:')) {
                $requiredLevel = (int) substr($roleName, 6);
                if ($user->hasMinLevel($requiredLevel)) {
                    return $next($request);
                }
                continue;
            }

            // Check by role name
            if ($user->hasRole($roleName)) {
                return $next($request);
            }

            // Check by role level alias
            if ($roleName === 'master-admin' && $user->isMasterAdmin()) {
                return $next($request);
            }
            if ($roleName === 'admin' && $user->isAdmin()) {
                return $next($request);
            }
            if ($roleName === 'supervisor' && $user->isSupervisor()) {
                return $next($request);
            }
            if ($roleName === 'staff' && $user->isStaff()) {
                return $next($request);
            }
            if ($roleName === 'viewer' && $user->hasMinLevel(5)) {
                return $next($request);
            }
            if ($roleName === 'driver' && ($user->isDriver() || $user->isStaff())) {
                return $next($request);
            }

            // Backward compat: member = supervisor
            if ($roleName === 'member' && $user->isSupervisor()) {
                return $next($request);
            }
        }

        abort(403, 'คุณไม่มีสิทธิ์เข้าถึงหน้านี้');
    }
}
