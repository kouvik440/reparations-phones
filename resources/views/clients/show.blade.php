<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du client</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
     @include('layouts.app')
</head>
<body class="bg-light">

    <div class="container my-5">
        <!-- Détails du client -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h1 class="h4 mb-0">👤 {{ $client->prenom }} {{ $client->nom }}</h1>
            </div>
            <div class="card-body">
                <p><strong>📞 Téléphone :</strong> {{ $client->telephone }}</p>
                <p><strong>🆔 Pièce d'identité :</strong> {{ $client->piece_identite }}</p>
            </div>
        </div>

        <!-- Historique des réparations -->
        <div class="card">
            <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">📜 Historique des réparations</h2>
                <a href="{{ route('reparations.create', ['client_id' => $client->id]) }}" class="btn btn-light btn-sm">
                    ➕ Ajouter une réparation
                </a>
            </div>
            <div class="card-body">

                @if($reparations->isEmpty())
                    <div class="alert alert-info mb-0">Aucune réparation enregistrée.</div>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Code</th>
                                    <th>Modèle</th>
                                    <th>Problème</th>
                                    <th>Date</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reparations as $r)
                                    <tr>
                                        <td>{{ $r->code }}</td>
                                        <td>{{ $r->marque }} {{ $r->modele }}</td>
                                        <td>{{ $r->probleme }}</td>
                                        <td>{{ \Carbon\Carbon::parse($r->date_depot)->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge bg-info text-dark">
                                                {{ ucfirst(str_replace('_', ' ', $r->statut)) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
