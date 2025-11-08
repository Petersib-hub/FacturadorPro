<?php

namespace App\Observers;

use App\Models\InvoicePayment;
use App\Support\Compliance;

class InvoicePaymentObserver
{
    public function created(InvoicePayment $payment): void
    {
        Compliance::log('art. 9', 'PAYMENT_REGISTERED', 'Pago registrado', [
            'invoice_id' => $payment->invoice_id,
            'amount'     => $payment->amount,
            'method'     => $payment->method,
        ], 'invoice', $payment->invoice_id, auth()->id());
    }

    public function deleted(InvoicePayment $payment): void
    {
        Compliance::log('art. 9', 'PAYMENT_DELETED', 'Pago eliminado', [
            'invoice_id' => $payment->invoice_id,
            'amount'     => $payment->amount,
        ], 'invoice', $payment->invoice_id, auth()->id());
    }
}