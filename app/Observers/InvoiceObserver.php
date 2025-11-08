<?php

namespace App\Observers;

use App\Models\Invoice;
use App\Support\Compliance;

class InvoiceObserver
{
    public function created(Invoice $invoice): void
    {
        Compliance::log('art. 9', 'INVOICE_CREATED', 'Factura creada', [
            'number' => $invoice->number,
            'total'  => $invoice->total,
        ], 'invoice', $invoice->id, auth()->id());
    }

    public function updated(Invoice $invoice): void
    {
        // Cambio de estado general
        if ($invoice->wasChanged('status')) {
            Compliance::log('art. 9', 'INVOICE_STATUS_CHANGED', 'Cambio de estado de factura', [
                'number' => $invoice->number,
                'from'   => $invoice->getOriginal('status'),
                'to'     => $invoice->status,
            ], 'invoice', $invoice->id, auth()->id());
        }

        // Cambio de estado Veri*factu (detecta verificada / error)
        if ($invoice->wasChanged('verifactu_status')) {
            $to = $invoice->verifactu_status;
            $code = $to === 'verified' ? 'VERIFY_OK'
                  : ($to === 'failed' ? 'VERIFY_FAIL' : 'VERIFY_STATUS');
            Compliance::log('art. 9', $code, 'Estado Veri*factu actualizado', [
                'number' => $invoice->number,
                'from'   => $invoice->getOriginal('verifactu_status'),
                'to'     => $to,
            ], 'invoice', $invoice->id, auth()->id());
        }
    }

    public function deleted(Invoice $invoice): void
    {
        Compliance::log('art. 9', 'INVOICE_DELETED', 'Factura eliminada', [
            'number' => $invoice->number,
        ], 'invoice', $invoice->id, auth()->id());
    }
}