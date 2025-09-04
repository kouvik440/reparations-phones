@extends('layouts.app')
@section('title', 'Tableau de bord')

@section('content')
    <h2>Tableau de bord</h2>

    <p>Bienvenue, {{ auth()->user()->name }} ({{ auth()->user()->role }})</p>

    @if(auth()->user()->role === 'admin')
        <p>Vous avez un accès complet à l'administration.</p>
        <a href="{{ route('users.index') }}" class="btn btn-primary">Gérer les utilisateurs</a>
    @else
        <p>Accès limité. Vous pouvez consulter vos réparations.</p>
    @endif
@endsection
