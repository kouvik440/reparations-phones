<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion.
     */
    public function loginForm()
    {
        return view('auth.login');
    }

    /**
     * Traite la tentative de connexion.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'name' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Redirection personnalisée selon le rôle
            $user = Auth::user();

            if ($user->role === 'admin') {
                return redirect()->route('dashboard');
            } else {
                return redirect()->route('reparations.index');
            }
        }

        return back()->with('error', 'Identifiants incorrects.');
    }

    /**
     * Déconnecte l'utilisateur.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /**
     * Affiche le formulaire d'enregistrement uniquement si aucun admin n'existe.
     */
    public function registerForm()
    {
        if (User::where('role', 'admin')->exists()) {
            return redirect()->route('login')
                ->with('error', 'Un administrateur existe déjà. Veuillez vous connecter.');
        }

        return view('auth.register');
    }

    /**
     * Enregistre un nouvel administrateur.
     */
    public function register(Request $request)
    {
        // Validation des champs
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:users,name',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'code' => 'required|string'
        ]);

        // Vérification du code d’entrée
        if ($validated['code'] !== 'cbc') {
            return back()->withErrors(['code' => 'Code d’entrée incorrect.'])->withInput();
        }

        // Création de l'utilisateur avec mot de passe haché
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
        ]);

        // Redirection vers la page de connexion avec message de succès
        return redirect()->route('login')->with('success', 'Administrateur créé avec succès.');
    }

    /**
     * Affiche le tableau de bord admin.
     */
    public function admin()
    {
        return view('admin.index');
    }

=======
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
>>>>>>> version1
}
