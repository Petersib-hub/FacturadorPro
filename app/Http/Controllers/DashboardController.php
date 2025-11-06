<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Budget;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    /**
     * Dashboard (invokable).
     */
    public function __invoke()
    {
        $userId = auth()->id();

        // Rango de los últimos 6 meses (incluye hoy)
        $to   = Carbon::today();
        $from = (clone $to)->subMonths(6);

        // Facturado este mes (sumatorio de total de facturas no anuladas del mes actual)
        $invoicedThisMonth = Invoice::query()
            ->where('user_id', $userId)
            ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
            ->whereNot('status', 'void')
            ->sum('total');

        // Pendiente de cobro (suma de (total - amount_paid) > 0 en facturas no anuladas)
        $pendingAmount = Invoice::query()
            ->where('user_id', $userId)
            ->whereNot('status', 'void')
            ->get()
            ->sum(function ($i) {
                return max(0, ($i->total ?? 0) - ($i->amount_paid ?? 0));
            });

        // Presupuestos abiertos (draft o sent)
        $budgetsOpen = Budget::query()
            ->where('user_id', $userId)
            ->whereIn('status', ['draft', 'sent'])
            ->count();

        // Listas recientes
        $recentInvoices = Invoice::query()
            ->where('user_id', $userId)
            ->whereNot('status', 'void')
            ->with('client')
            ->latest('date')
            ->limit(5)
            ->get();

        $recentBudgets = Budget::query()
            ->where('user_id', $userId)
            ->with('client')
            ->latest('date')
            ->limit(5)
            ->get();

        /**
         * Top clientes (6 meses)
         * Agrupamos por cliente y sumamos el total de sus facturas no anuladas en el rango [from, to].
         * Devolvemos: client_id, total_sum y count_invoices, y cargamos el nombre del cliente.
         */
        $topClients = Invoice::query()
            ->where('user_id', $userId)
            ->whereBetween('date', [$from, $to])
            ->whereNot('status', 'void')
            ->selectRaw('client_id, SUM(total) as total_sum, COUNT(*) as count_invoices')
            ->groupBy('client_id')
            ->orderByDesc('total_sum')
            ->with('client')        // para acceder a $row->client->name
            ->limit(5)
            ->get();

        return view('dashboard', compact(
            'invoicedThisMonth',
            'pendingAmount',
            'budgetsOpen',
            'recentInvoices',
            'recentBudgets',
            'topClients',
            'from',
            'to',
        ));
    }
}
