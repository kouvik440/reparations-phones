<?php

namespace App\Http\Controllers;

use App\Actions\Reparations\ChangeRepairStatus;
use App\Actions\Reparations\GenerateRepairTicket;
use App\Actions\Reparations\RecordRepairDeposit;
use App\Enums\RepairStatus;
use App\Http\Requests\ChangeRepairStatusRequest;
use App\Http\Requests\StoreRepairRequest;
use App\Models\Client;
use App\Models\DeviceBrand;
use App\Models\Reparation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReparationController extends Controller
{
    public function edit(Reparation $reparation): \Inertia\Response
    {
        return \Inertia\Inertia::render('Repairs/Create', [
            'repair' => $reparation,
            'client' => $reparation->client()->first(['id', 'prenom', 'nom', 'telephone']),
            'brands' => DeviceBrand::with('deviceModels:id,device_brand_id,name')->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(\App\Http\Requests\UpdateRepairRequest $request, Reparation $reparation, \App\Actions\Reparations\UpdateRepair $update): RedirectResponse
    {
        $data = $request->validated();
        if (! $request->user()->can('repairs.set_price')) {
            unset($data['prix']);
        }
        $update->handle($reparation->id, $data);

        return redirect()->route('reparations.show', $reparation)->with('success', 'Dossier modifié. Le code de suivi est conservé.');
    }

    public function destroy(Request $request, Reparation $reparation): RedirectResponse
    {
        $data = $request->validate(['confirmation' => ['required', 'string', \Illuminate\Validation\Rule::in([$reparation->code])]]);
        $reparation->delete();

        return redirect()->route('reparations.index')->with('success', 'Dossier supprimé. Son code de suivi n’est plus accessible.');
    }
    public function index(Request $request)
    {
        $request->validate(['q' => 'nullable|string|max:100', 'statut' => 'nullable|in:all,en_attente,en_cours,termine,restitue', 'date' => 'nullable|date_format:Y-m-d']);
        $search = trim((string) $request->input('q', ''));
        $statut = $request->input('statut') ?: 'all';
        $stats = array_replace(array_fill_keys(array_keys(RepairStatus::labels()), 0), Reparation::selectRaw('statut, COUNT(*) as total')->groupBy('statut')->pluck('total', 'statut')->all());
        $query = Reparation::with('client:id,prenom,nom,telephone');
        if ($request->filled('date')) {
            $query->whereDate('date_depot', $request->input('date'));
        }
        if ($statut !== 'all') {
            $query->where('statut', $statut);
        }
        if ($search !== '') {
            $query->where(function ($query) use ($search) {
                foreach (['code', 'marque', 'modele', 'probleme'] as $field) {
                    $query->orWhere($field, 'like', '%'.$search.'%');
                }
                $query->orWhereHas('client', function ($client) use ($search) {
                    $client->where('nom', 'like', '%'.$search.'%')->orWhere('prenom', 'like', '%'.$search.'%')->orWhere('telephone', 'like', '%'.$search.'%');
                });
            });
        }
        $reparations = $query->orderByDesc('date_depot')->orderByDesc('id')->paginate(12)->withQueryString();

        return \Inertia\Inertia::render('Repairs/Index', compact('reparations', 'search', 'statut', 'stats'));
    }

    public function findClientForm()
    {
        return \Inertia\Inertia::render('Repairs/FindClient', ['clients' => null, 'telephone' => '']);
    }

    public function findClient(Request $request)
    {
        $data = $request->validate([
            'telephone' => ['bail', 'required', 'string', 'max:40', 'regex:/^\+?[0-9\s().-]+$/', function ($attribute, $value, $fail) {
                $length = strlen(preg_replace('/[^0-9]/', '', $value));
                if ($length < 8 || $length > 15) {
                    $fail('Saisissez un numéro complet de 8 à 15 chiffres.');
                }
            }],
        ], [
            'telephone.required' => 'Indiquez le numéro de téléphone du client.',
            'telephone.regex' => 'Utilisez uniquement des chiffres, avec des espaces, tirets ou un indicatif précédé de +.',
            'telephone.max' => 'Le numéro saisi est trop long.',
        ]);
        $telephone = trim($data['telephone']);
        $digits = preg_replace('/[^0-9]/', '', $telephone);
        $phoneSql = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(telephone, ' ', ''), '-', ''), '(', ''), ')', ''), '.', ''), '+', '')";
        $clients = Client::select('id', 'prenom', 'nom', 'telephone')->whereRaw($phoneSql.' = ?', [$digits])
            ->withCount('reparations')->orderBy('nom')->orderBy('prenom')->get();

        return \Inertia\Inertia::render('Repairs/FindClient', compact('clients', 'telephone'));
    }

    public function create(Request $request)
    {
        $client = null;
        if ($request->has('client_id')) {
            $client = Client::find($request->client_id);
        }

        $brands = DeviceBrand::with('deviceModels:id,device_brand_id,name')->orderBy('name')->get(['id', 'name']);

        return \Inertia\Inertia::render('Repairs/Create', compact('client', 'brands'));
    }

    public function store(StoreRepairRequest $request)
    {
        $data = $request->validated();

        if (! $request->user()->can('repairs.set_price')) {
            unset($data['prix']);
        }
        $reparation = app(RecordRepairDeposit::class)->handle($data, $request->user());

        return redirect()->route('reparations.show', $reparation)->with('success', 'Dépôt enregistré. Téléchargez le ticket à remettre au client.');
    }

    public function ticket(Reparation $reparation, GenerateRepairTicket $ticket): Response
    {
        return response($ticket->handle($reparation), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="ticket-'.$reparation->code.'.pdf"',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function show(Reparation $reparation)
    {
        $reparation->load('client:id,prenom,nom,telephone');
        $history = $reparation->statusChanges()->paginate(20);

        return \Inertia\Inertia::render('Repairs/Show', compact('reparation', 'history'));
    }

    public function updatePrix(Request $request, Reparation $reparation)
    {
        $data = $request->validate(['prix' => 'required|integer|min:0|max:1000000000']);
        $reparation->update($data);

        return back()->with('success', 'Prix de la réparation mis à jour.');
    }

    public function tracking(Request $request)
    {
        $reparation = null;
        $searched = $request->isMethod('post');
        if ($searched) {
            $data = $request->validate(['code' => 'required|string|size:8|alpha_num:ascii']);
            $reparation = Reparation::where('code', strtoupper(trim($data['code'])))->first();
        }

        return \Inertia\Inertia::render('Public/Tracking', ['reparation' => $reparation?->only('code', 'marque', 'modele', 'date_depot', 'prix', 'statut'), 'searched' => $searched]);
    }

    public function updateStatut(ChangeRepairStatusRequest $request, int $id, ChangeRepairStatus $changeStatus): JsonResponse|RedirectResponse
    {
        $data = $request->validated();
        $repair = $changeStatus->handle($id, RepairStatus::from($data['statut']), (int) $data['status_version'], $request->user(), $data['date_retrait'] ?? null);
        if ($request->expectsJson()) {
            $request->session()->flash('success', 'Statut enregistré et historique mis à jour.');

            return response()->json(['message' => 'Statut enregistré.', 'statut' => $repair->statut, 'status_version' => $repair->status_version]);
        }

        return back()->with('success', 'Statut enregistré et historique mis à jour.');
    }
}
