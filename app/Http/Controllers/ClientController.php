<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct()
    {
        // Habilita las abilities de la ClientPolicy: viewAny, view, create, update, delete...
        $this->authorizeResource(Client::class, 'client');
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $q = $request->get('q', '');

        $clients = \App\Models\Client::query()
            ->where('user_id', auth()->id())
            ->when($q, function ($b) use ($q) {
                $b->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('tax_id', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('clients.index', compact('clients', 'q'));
    }


    public function create()
    {
        return view('clients.create');
    }

    public function store(StoreClientRequest $request)
    {
        $data = $request->validated();
        // forzamos el owner siempre
        $data['user_id'] = auth()->id();

        // RGPD: si se aceptó el checkbox, guardamos timestamp
        if ($request->boolean('consent')) {
            $data['consent_accepted_at'] = now();
        }

        $client = Client::create($data);

        return redirect()
            ->route('clients.show', $client)
            ->with('ok', 'Cliente creado correctamente.');
    }

    public function show(Client $client)
    {
        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client)
    {
        $data = $request->validated();

        // Nunca permitimos cambiar el owner por request
        unset($data['user_id']);

        if ($request->boolean('consent')) {
            $data['consent_accepted_at'] = now();
        }

        $client->update($data);

        return redirect()
            ->route('clients.show', $client)
            ->with('ok', 'Cliente actualizado.');
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('ok', 'Cliente eliminado.');
    }
}
