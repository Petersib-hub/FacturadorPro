<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Support\Audit;

class PublicInvoiceController extends Controller
{
    /**
     * Muestra una factura pública por token y registra auditoría.
     */
    public function show(string $token)
    {
        $invoice = Invoice::with(['client','items','payments'])
            ->where('public_token', $token)
            ->firstOrFail();

        // Auditoría: visualización pública de factura
        Audit::record('public.invoice.viewed', 'invoice', $invoice->id, [
            'token'      => $token,
            'invoice_no' => $invoice->number,
        ]);

        return view('public.invoices.show', compact('invoice'));
    }
}
