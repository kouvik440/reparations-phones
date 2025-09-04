<?php
namespace App\Http\Controllers;

use App\Models\Reparation;
use App\Models\Client;
use Illuminate\Http\Request;

class ReparationController extends Controller
{
    public function index()
    {
        $reparations = Reparation::with('client')->latest()->get();
        return view('reparations.index', compact('reparations'));
    }

    public function findClientForm()
    {
        return view('reparations.find-client');
    }

    public function findClient(Request $request)
    {
        $request->validate([
            'telephone' => 'required'
        ]);

        $client = Client::where('telephone', $request->telephone)->first();

        if (!$client) {
            return back()->with('error', 'Aucun client trouvé avec ce numéro.');
        }

        return redirect()->route('clients.show', $client->id);
    }

    public function create(Request $request)
    {
        $client = null;
        if ($request->has('client_id')) {
            $client = Client::find($request->client_id);
        }

        return view('reparations.create', compact('client'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'marque' => 'required',
            'modele' => 'required',
            'probleme' => 'required',
            'date_depot' => 'required|date'
        ]);

        Reparation::create($request->all());
        return redirect()->route('reparations.index')->with('success', 'Réparation enregistrée');
    }

    public function updateStatut(Request $request, $id)
    {
        $request->validate([
            'statut' => 'required|in:en_attente,en_cours,termine'
        ]);

        $reparation = Reparation::findOrFail($id);
        $reparation->statut = $request->statut;
        $reparation->save();

        return back()->with('success', 'Statut mis à jour');
    }
}
