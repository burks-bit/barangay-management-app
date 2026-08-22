<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, $role, $permission = null): Response
    {
        if (! $request->user()) {
            return redirect()->route('login');
        }

        $role = is_array($role) ? $role : explode('|', $role);

        if (! $request->user()->hasAnyRole($role)) {
            abort(403, 'Unauthorized action.');
        }

        if ($permission) {
            $permission = is_array($permission) ? $permission : explode('|', $permission);
            if (! $request->user()->canAny($permission)) {
                abort(403, 'Unauthorized action.');
            }
        }

        return $next($request);
    }
}
