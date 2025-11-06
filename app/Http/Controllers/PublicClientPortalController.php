<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Budget;
use Illuminate\Http\Request;

class PublicClientPortalController extends Controller
{
    public function index(string $token, Request $request)
    {
        // Carga cliente por token (sin auth)
        $client = Client::where('public_token',$token)->firstOrFail();

        // Filtros sencillos
        $tab = $request->get('tab','invoices'); // invoices|budgets
        $q   = $request->get('q');

        // Sólo documentos del mismo tenant del cliente
        $invoices = Invoice::with('items')
            ->where('user_id', $client->user_id)
            ->where('client_id', $client->id)
            ->when($q, fn($b)=>$b->where('number','like',"%{$q}%"))
            ->latest('date')
            ->paginate(10, ['*'], 'pi')
            ->appends($request->all());

        $budgets = Budget::with('items')
            ->where('user_id', $client->user_id)
            ->where('client_id', $client->id)
            ->when($q, fn($b)=>$b->where('number','like',"%{$q}%"))
            ->latest('date')
            ->paginate(10, ['*'], 'pb')
            ->appends($request->all());

        return view('public.portal.client', compact('client','invoices','budgets','tab','q'));
    }
}
