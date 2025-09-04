<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une réparation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
     @include('layouts.app')
</head>
<body class="bg-light">

    <div class="container mt-5">

        <h1 class="mb-4">🔧 Ajouter une réparation</h1>

        @if($client)
            <!-- Infos client -->
            <div class="alert alert-info">
                <strong>Client :</strong> {{ $client->prenom }} {{ $client->nom }} ({{ $client->telephone }})
            </div>

            <!-- Formulaire réparation -->
            <form method="POST" action="{{ route('reparations.store') }}" class="bg-white p-4 rounded shadow-sm">
                @csrf
                <input type="hidden" name="client_id" value="{{ $client->id }}">

                <div class="mb-3">
                    <label for="marque" class="form-label">Marque</label>
                    <input type="text" name="marque" id="marque" class="form-control" placeholder="Ex: Samsung, Apple..." required>
                </div>

                <div class="mb-3">
                    <label for="modele" class="form-label">Modèle</label>
                    <input type="text" name="modele" id="modele" class="form-control" placeholder="Ex: Galaxy S21, iPhone 13..." required>
                </div>

                <div class="mb-3">
                    <label for="probleme" class="form-label">Problème rencontré</label>
                    <textarea name="probleme" id="probleme" class="form-control" placeholder="Décris le problème..." rows="3" required></textarea>
                </div>

                <div class="mb-4">
                    <label for="date_depot" class="form-label">Date de dépôt</label>
                    <input type="date" name="date_depot" id="date_depot" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-success">
                    📦 Enregistrer la réparation
                </button>
            </form>
        @else
            <!-- Message si pas de client -->
            <div class="alert alert-danger">
                <strong>Erreur :</strong> Aucun client sélectionné.
                <a href="{{ route('reparations.findClientForm') }}" class="btn btn-sm btn-outline-danger ms-2">🔍 Rechercher un client</a>
            </div>
        @endif

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
