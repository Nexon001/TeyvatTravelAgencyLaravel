<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ends an administrator's session after a period without opening an admin page (FR-35).
 * The time limit is config('admin.idle_minutes'). Each admin page refreshes the timer.
 */
class AdminIdleTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        $limit = (int) config('admin.idle_minutes') * 60;
        $last = $request->session()->get('admin_last_activity');

        if ($last !== null && (time() - (int) $last) > $limit) {
            Auth::guard('web')->logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with('status', 'You were logged out after a period of inactivity. Please log in again.');
        }

        $request->session()->put('admin_last_activity', time());

        return $next($request);
    }
}
