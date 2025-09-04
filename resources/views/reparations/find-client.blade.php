<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recherche de client</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
     @include('layouts.app')
</head>
<body class="bg-light">

    <div class="container mt-5">

        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h1 class="h4 mb-0">🔍 Recherche de client</h1>
            </div>
            <div class="card-body">

                @if(session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Formulaire de recherche -->
                <form method="POST" action="{{ route('reparations.findClient') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="telephone" class="form-label">Numéro de téléphone</label>
                        <input type="text" name="telephone" id="telephone" class="form-control" placeholder="Entrez le numéro du client" required>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        🔎 Rechercher
                    </button>
                </form>

                <hr>

                <p class="mt-3">
                    Pas encore de client ?  
                    <a href="{{ route('clients.create') }}" class="btn btn-outline-success ms-2">
                        ➕ Créer un nouveau client
                    </a>
                </p>

            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
