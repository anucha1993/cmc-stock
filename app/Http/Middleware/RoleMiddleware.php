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
     * @param  string  ...$roles
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

        // Check if user has any of the required roles
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }
        }

        // Check by role levels
        foreach ($roles as $role) {
            switch (strtolower($role)) {
                case 'master-admin':
                    if ($user->isMasterAdmin()) {
                        return $next($request);
                    }
                    break;
                case 'admin':
                    if ($user->isAdmin()) {
                        return $next($request);
                    }
                    break;
                case 'member':
                    if ($user->isMember()) {
                        return $next($request);
                    }
                    break;
            }
        }

        abort(403, 'Unauthorized access.');
    }
}
