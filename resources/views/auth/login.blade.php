@extends('layouts.app')
@section('title', 'Connexion')

@section('content')
    <h2>Connexion</h2>

    {{-- Messages flash --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label>Nom d'utilisateur</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Mot de passe</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <button class="btn btn-primary">Se connecter</button>
    </form>

    <div class="mt-3">
        <a href="{{ route('register') }}">Créer un administrateur</a>
    </div>
@endsection
