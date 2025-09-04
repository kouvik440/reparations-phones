<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Réparations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
     @include('layouts.app')
</head>
<body class="bg-light">

    <div class="container my-5">
        <h1 class="mb-4">📋 Réparations</h1>

        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>👤 Client</th>
                        <th>📞 Téléphone</th>
                        <th>🔢 Code</th>
                        <th>📱 Modèle</th>
                        <th>⚙️ Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reparations as $r)
                        <tr>
                            <td>{{ $r->client->prenom }} {{ $r->client->nom }}</td>
                            <td>{{ $r->client->telephone }}</td>
                            <td>{{ $r->code }}</td>
                            <td>{{ $r->marque }} {{ $r->modele }}</td>
                            <td>
                                <form action="{{ route('reparations.updateStatut', $r->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <select name="statut" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="en_attente" {{ $r->statut === 'en_attente' ? 'selected' : '' }}>🕒 En attente</option>
                                        <option value="en_cours" {{ $r->statut === 'en_cours' ? 'selected' : '' }}>🔧 En cours</option>
                                        <option value="termine" {{ $r->statut === 'termine' ? 'selected' : '' }}>✅ Terminé</option>
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
