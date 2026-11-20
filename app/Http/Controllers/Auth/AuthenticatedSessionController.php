<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Logging in happens in the pop-up. A visitor sent here (by the "auth" middleware, an idle logout or an
     * old link) goes to the home page with the pop-up open. The flash message, if any, is kept for that page.
     */
    public function create(Request $request): RedirectResponse
    {
        $request->session()->reflash();

        return redirect()->route('home', ['auth' => 'login']);
    }

    /**
     * One login form for everyone. The pop-up sends it with fetch() and gets JSON back; a plain form post
     * gets a redirect. "redirect" is where to go next, or null to stay on the current page and reload it:
     * the page the visitor was sent away from, the dashboard for an administrator, otherwise nowhere.
     */
    public function store(LoginRequest $request): RedirectResponse|JsonResponse
    {
        $request->authenticate();

        // New session id after login, so an id planted before login is useless.
        $request->session()->regenerate();

        $target = $request->session()->pull('url.intended')
            ?? ($request->user()->role === 'admin' ? route('admin.dashboard') : null);

        return $request->wantsJson()
            ? response()->json(['redirect' => $target])
            : redirect($target ?? route('home'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
