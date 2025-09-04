@extends('layouts.app')
@section('title', 'Créer un utilisateur')
@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

@section('content')
    <h2>Créer un utilisateur</h2>

    <form method="POST" action="{{ route('users.store') }}">
        @csrf

        <div class="mb-3">
            <label>Nom</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Mot de passe</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Rôle</label>
            <select name="role" class="form-control" required>
                <option value="user">Utilisateur</option>
                <option value="admin">Administrateur</option>
            </select>
        </div>

        <button class="btn btn-success">Créer</button>
    </form>
@endsection
