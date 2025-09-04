<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enregistrement Client</title>

    <!-- 🔗 Lien Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Inclusion de la navbar Bootstrap -->
    @include('layouts.app')

    <div class="container mt-5">
        <h2 class="mb-4 text-center">🧾 Formulaire d'enregistrement client</h2>

        <form action="{{ route('clients.store') }}" method="POST" class="bg-white p-4 rounded shadow-sm">
            @csrf

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="prenom" class="form-label">Prénom</label>
                    <input type="text" name="prenom" id="prenom" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label for="nom" class="form-label">Nom</label>
                    <input type="text" name="nom" id="nom" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="telephone" class="form-label">Téléphone</label>
                <input type="text" name="telephone" id="telephone" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="piece_identite" class="form-label">Pièce d'identité</label>
                <input type="text" name="piece_identite" id="piece_identite" class="form-control" required>
            </div>

            <div class="d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
                <a href="{{ route('reparations.create') }}" class="btn btn-outline-secondary">➕ Ajouter réparation</a>
            </div>
        </form>
    </div>

    <!-- JS Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
