<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Support\Audit;
use Illuminate\Http\Request;

class PublicBudgetController extends Controller
{
    /**
     * Muestra un presupuesto público por token y registra auditoría.
     */
    public function show(string $token)
    {
        $budget = Budget::with(['client','items'])
            ->where('public_token', $token)
            ->firstOrFail();

        // Auditoría: visualización pública de presupuesto
        Audit::record('public.budget.viewed', 'budget', $budget->id, [
            'token'      => $token,
            'budget_no'  => $budget->number,
        ]);

        return view('public.budgets.show', compact('budget'));
    }

    /**
     * Aceptar presupuesto desde portal público.
     */
    public function accept(Request $request, string $token)
    {
        $budget = Budget::where('public_token', $token)->firstOrFail();

        if (in_array($budget->status, ['draft','sent'])) {
            $budget->update(['status' => 'accepted']);
        }

        // Auditoría: aceptación
        Audit::record('public.budget.accepted', 'budget', $budget->id, [
            'token'     => $token,
            'budget_no' => $budget->number,
        ]);

        return redirect()
            ->route('public.budgets.show', $token)
            ->with('ok','¡Presupuesto aceptado! Gracias.');
    }

    /**
     * Rechazar presupuesto desde portal público.
     */
    public function reject(Request $request, string $token)
    {
        $budget = Budget::where('public_token', $token)->firstOrFail();

        if (in_array($budget->status, ['draft','sent'])) {
            $budget->update(['status' => 'rejected']);
        }

        // Auditoría: rechazo
        Audit::record('public.budget.rejected', 'budget', $budget->id, [
            'token'     => $token,
            'budget_no' => $budget->number,
        ]);

        return redirect()
            ->route('public.budgets.show', $token)
            ->with('ok','Hemos registrado tu rechazo. Gracias.');
    }
}
