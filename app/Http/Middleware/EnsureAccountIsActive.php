<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            $user = $user->fresh(['roleDefinition']);
            if (! $user || ! $user->is_active || (int) $request->session()->get('account_version', 0) !== (int) $user->session_version) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('error', 'Votre accès a changé. Reconnectez-vous ou contactez le responsable de l’atelier.');
            }
            Auth::setUser($user);
        }

        return $next($request);
    }
}
