@extends('layouts.app')
@section('title', 'Modifier utilisateur')

@section('content')
    <h2>Modifier l'utilisateur</h2>

    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf @method('PUT')

        <div class="mb-3">
            <label>Nom</label>
            <input type="text" name="name" value="{{ $user->name }}" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Nouveau mot de passe (laisser vide pour ne pas changer)</label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="mb-3">
            <label>Rôle</label>
            <select name="role" class="form-control" required>
                <option value="user" @selected($user->role === 'user')>Utilisateur</option>
                <option value="admin" @selected($user->role === 'admin')>Administrateur</option>
            </select>
        </div>

        <button class="btn btn-primary">Mettre à jour</button>
    </form>
@endsection
