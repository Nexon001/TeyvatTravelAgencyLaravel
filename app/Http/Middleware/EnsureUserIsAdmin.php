<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps non-admin accounts out of the admin pages (FR-33).
 * Put it after "auth": guests are sent to the login page first, logged-in travellers get a 403.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->role !== 'admin') {
            abort(403, 'This area is for administrators only.');
        }

        return $next($request);
    }
}
