<?php

namespace App\Http\Controllers;
use App\Models\Client;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::all();
        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'prenom' => 'required',
            'nom' => 'required',
            'telephone' => 'required|unique:clients',
            'piece_identite' => 'required',
        ]);

        Client::create($request->all());
        return redirect()->route('clients.index')->with('success', 'Client enregistré');
    }

    public function show(Client $client)
    {
        $reparations = $client->reparations()->latest()->get();
        return view('clients.show', compact('client', 'reparations'));
    }
}
