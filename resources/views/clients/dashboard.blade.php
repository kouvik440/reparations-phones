@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <h1>Bienvenue, {{ auth()->user()->name }}</h1>

    @if(auth()->user()->isAdmin())
        <div class="alert alert-info">
            Vous êtes connecté en tant qu'**Administrateur**.
        </div>

        <a href="{{ route('users.index') }}" class="btn btn-primary">Gérer les utilisateurs</a>
    @else
        <div class="alert alert-secondary">
            Vous êtes connecté en tant qu'utilisateur standard.
        </div>
    @endif
@endsection
