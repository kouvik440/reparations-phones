<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Gestion des Réparations')</title>

    <!-- Bootstrap 5.3.2 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            padding-top: 70px;
        }

        .navbar-brand {
            font-weight: bold;
        }

        .alert {
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <!-- Barre de navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top shadow-sm">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">📱 Réparations</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
                    aria-controls="navbarContent" aria-expanded="false" aria-label="Basculer la navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('clients.index') }}">Clients</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('reparations.index') }}">Réparations</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('reparations.findClientForm') }}">Ajouter réparation</a>
                    </li>
                </ul>

                <form method="POST" action="{{ route('logout') }}" class="d-flex">
                    @csrf
                    <button type="submit" class="btn btn-outline-light">Se déconnecter</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container">

        <!-- Messages flash -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        @endif

        @yield('content')
    </div>

</body>
</html>
