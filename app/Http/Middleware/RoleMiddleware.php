<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Role-based access control.
 * Usage: ->middleware('role:shs_staff,teacher')   (administrators always pass)
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            abort(403, 'Your account is inactive.');
        }

        if ($user->role !== 'admin' && ! in_array($user->role, $roles, true)) {
            abort(403, 'You are not allowed to access this page.');
        }

        return $next($request);
    }
}
