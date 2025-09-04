@extends('layouts.app')
@section('title', 'Utilisateurs')

@section('content')
    <h2>Liste des utilisateurs</h2>

    <a href="{{ route('users.create') }}" class="btn btn-success mb-3">Ajouter un utilisateur</a>

    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Rôle</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->role }}</td>
                <td>
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-warning btn-sm">Modifier</a>

                    <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm">Supprimer</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
