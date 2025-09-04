 @include('layouts.app')
 
<div class="container my-4">
    <h1 class="mb-3">
        <i class="bi bi-person"></i> {{ $client->prenom }} {{ $client->nom }}
    </h1>

    <p><strong>Téléphone :</strong> {{ $client->telephone }}</p>
    <p><strong>Pièce d'identité :</strong> {{ $client->piece_identite }}</p>

    <h2 class="mt-4">
        <i class="bi bi-file-earmark-text"></i> Historique des réparations
    </h2>

    @if($reparations->isEmpty())
        <div class="alert alert-info mt-3">Aucune réparation.</div>
    @else
        <div class="table-responsive mt-3">
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
                            <td>{{ ucfirst(str_replace('_', ' ', $r->statut)) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <a href="{{ route('reparations.create', ['client_id' => $client->id]) }}" class="btn btn-primary mt-3">
        ➕ Ajouter une réparation
    </a>
</div>
