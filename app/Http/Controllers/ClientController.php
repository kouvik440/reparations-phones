<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    private const PHONE_SQL = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(telephone, ' ', ''), '-', ''), '(', ''), ')', ''), '.', ''), '+', '')";

    public function index(Request $request)
    {
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'filter' => ['nullable', Rule::in(['all', 'active', 'without'])]]);
        $search = trim($data['q'] ?? '');
        $filter = $data['filter'] ?? 'all';
        $query = Client::query()->select('id', 'prenom', 'nom', 'telephone')->withCount('reparations')->withCount(['reparations as active_count' => fn ($q) => $q->whereIn('statut', ['en_attente', 'en_cours'])]);
        if ($search !== '') {
            $terms = preg_split('/\s+/u', $search);
            $query->where(function ($q) use ($search, $terms) {
                $q->where(function ($names) use ($terms) {
                    foreach ($terms as $term) {
                        $names->where(fn ($name) => $name->where('prenom', 'like', '%'.$term.'%')->orWhere('nom', 'like', '%'.$term.'%'));
                    }
                });
                $digits = preg_replace('/[^0-9]/', '', $search);
                if ($digits !== '' && preg_match('/^[+0-9\s().-]+$/', $search)) {
                    $q->orWhereRaw(self::PHONE_SQL.' LIKE ?', ['%'.$digits.'%']);
                }
            });
        }
        if ($filter === 'active') {
            $query->whereHas('reparations', fn ($q) => $q->whereIn('statut', ['en_attente', 'en_cours']));
        } elseif ($filter === 'without') {
            $query->doesntHave('reparations');
        }
        $clients = $query->orderBy('nom')->orderBy('prenom')->orderBy('id')->paginate(12)->withQueryString();
        $stats = [
            'total' => Client::count(),
            'active' => Client::whereHas('reparations', fn ($q) => $q->whereIn('statut', ['en_attente', 'en_cours']))->count(),
            'without' => Client::doesntHave('reparations')->count(),
        ];

        return \Inertia\Inertia::render('Clients/Index', compact('clients', 'search', 'filter', 'stats'));
    }

    public function create(Request $request)
    {
        $data = $request->validate(['telephone' => 'nullable|string|max:40']);

        return \Inertia\Inertia::render('Clients/Create', ['telephone' => $data['telephone'] ?? '']);
    }

    public function store(Request $request)
    {
        $request->merge([
            'prenom' => trim((string) $request->input('prenom', '')),
            'nom' => trim((string) $request->input('nom', '')),
            'telephone' => preg_replace('/[\s().-]+/u', '', (string) $request->input('telephone', '')),
            'piece_identite' => trim((string) $request->input('piece_identite', '')),
        ]);
        $data = $request->validate([
            'prenom' => ['required', 'string', 'max:255'],
            'nom' => ['required', 'string', 'max:255'],
            'telephone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9]{8,15}$/', 'unique:clients,telephone', function ($attribute, $value, $fail) {
                if (Client::whereRaw(self::PHONE_SQL.' = ?', [ltrim($value, '+')])->exists()) {
                    $fail('Ce numéro est déjà associé à un client. Retrouvez sa fiche dans le répertoire.');
                }
            }],
            'piece_identite' => ['required', 'string', 'max:255'],
            'next' => ['nullable', Rule::in(['profile', 'repair'])],
        ], [
            'prenom.required' => 'Le prénom est obligatoire.',
            'nom.required' => 'Le nom est obligatoire.',
            'telephone.required' => 'Le téléphone est obligatoire.',
            'telephone.regex' => 'Saisissez un numéro de 8 à 15 chiffres, avec un indicatif précédé de + si nécessaire.',
            'telephone.unique' => 'Ce numéro est déjà associé à un client.',
            'piece_identite.required' => 'La référence de la pièce d’identité est obligatoire.',
        ]);
        $client = Client::create(collect($data)->only(['prenom', 'nom', 'telephone', 'piece_identite'])->all());

        return ($request->input('next') === 'repair' && $request->user()->can('repairs.create')
            ? redirect()->route('reparations.create', ['client_id' => $client->id])
            : redirect()->route('clients.show', $client))
            ->with('success', 'Client enregistré. Vous pouvez maintenant ajouter un dépôt.');
    }

    public function show(Client $client)
    {
        $reparations = $client->reparations()->select('id', 'client_id', 'code', 'marque', 'modele', 'probleme', 'date_depot', 'statut')->latest('id')->paginate(10)->withQueryString();
        $client->loadCount(['reparations', 'reparations as active_count' => fn ($q) => $q->whereIn('statut', ['en_attente', 'en_cours'])]);

        return \Inertia\Inertia::render('Clients/Show', compact('client', 'reparations'));
    }
}
