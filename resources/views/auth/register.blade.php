@extends('layouts.app')
@section('title', 'Créer un administrateur')

@section('content')
    <h2>Créer un administrateur</h2>

    {{-- Messages flash --}}
    @if(session('success')) 
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="mb-3">
            <label>Nom d'utilisateur</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Adresse email (optionnel)</label>
            <input type="email" name="email" class="form-control">
        </div>

        <div class="mb-3">
            <label>Mot de passe</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Code d’entrée</label>
            <input type="password" name="code" class="form-control" placeholder="Entrez le code d’accès (cbc)" required>
        </div>

        <button type="submit" class="btn btn-success">Créer</button>
    </form>
@endsection
