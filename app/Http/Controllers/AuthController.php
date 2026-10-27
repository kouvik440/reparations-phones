<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function loginForm(): \Inertia\Response
    {
        return \Inertia\Inertia::render('Auth/Login');
    }

    public function login(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->merge(['email' => Str::lower(trim((string) $request->input('email', '')))]);
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Indiquez votre adresse email.',
            'email.email' => 'Indiquez une adresse email valide.',
            'password.required' => 'Indiquez votre mot de passe.',
        ]);
        $key = 'login:'.hash('sha256', $credentials['email'].'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => 'Trop de tentatives. Réessayez dans '.RateLimiter::availableIn($key).' secondes.']);
        }
        if (! Auth::attempt([...$credentials, 'is_active' => true])) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['email' => 'Adresse email ou mot de passe incorrect.']);
        }
        Auth::user()->forceFill(['last_login_at' => now()])->save();
        $request->session()->put('account_version', Auth::user()->session_version);
        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()->intended(route(Auth::user()->landingRoute()));
    }

    public function logout(Request $request): \Illuminate\Http\RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        \Inertia\Inertia::clearHistory();

        return redirect()->route('login');
    }
}
