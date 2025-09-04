<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    // Lister tous les utilisateurs
    public function index()
    {
        $users = User::all();
        return view('users.index', compact('users'));
    }

    // Formulaire de création
    public function create()
    {
        return view('users.create');
    }

    // Enregistrement d’un nouvel utilisateur
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:users',
            'password' => 'required|min:6',
            'role' => 'required|in:admin,user',
        ]);

        User::create([
            'name' => $request->name,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('users.index')->with('success', 'Utilisateur créé.');
    }

    // Formulaire d’édition
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    // Mise à jour de l’utilisateur
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|unique:users,name,' . $user->id,
            'role' => 'required|in:admin,user',
        ]);

        $user->name = $request->name;
        $user->role = $request->role;

        // si le mot de passe est rempli, on le met à jour
        if ($request->filled('password')) {
            $request->validate(['password']);
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'Utilisateur mis à jour.');
    }

    // Suppression
    public function destroy(User $user)
    {
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Impossible de supprimer votre propre compte.');
        }

        $user->delete();

        return back()->with('success', 'Utilisateur supprimé.');
    }

}
