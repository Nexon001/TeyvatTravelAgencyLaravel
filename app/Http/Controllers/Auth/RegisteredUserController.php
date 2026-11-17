<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class RegisteredUserController extends Controller
{
    /** Signing up happens in the pop-up: open it on its "Create account" view. */
    public function create(Request $request): RedirectResponse
    {
        $request->session()->reflash();

        return redirect()->route('home', ['auth' => 'signup']);
    }

    /** The pop-up gets JSON back ("redirect": where to go, or null to reload the current page). */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        // Same address typed in a different case is the same account.
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
            // Honeypot: hidden from people, so any value means a bot filled it in.
            'hp_website' => ['prohibited'],
        ]);

        $user = new User([
            'name' => trim($data['name']),
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // The role is never taken from the request: every sign-up is a customer.
        $user->forceFill(['role' => 'customer'])->save();

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        $target = $request->session()->pull('url.intended');

        return $request->wantsJson()
            ? response()->json(['redirect' => $target])
            : redirect($target ?? route('home'));
    }
}
