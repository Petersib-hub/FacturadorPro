<?php

namespace App\Mail;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoicePdfMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public ?string $bankAccount = null,
        public ?string $billingNotes = null
    ) {}

    public function build()
    {
        // Render PDF con la plantilla que ya usas (classic/minimal/modern)
        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice'      => $this->invoice,
            'bank_account' => $this->bankAccount,
            'billing_notes'=> $this->billingNotes,
        ])->setPaper('a4');

        $fileName = 'Factura-'.$this->invoice->number.'.pdf';

        return $this->subject('Factura '.$this->invoice->number)
            ->view('emails.invoice') // Crea si no existe; puede ser un layout sencillo
            ->attachData($pdf->output(), $fileName, ['mime' => 'application/pdf']);
    }
}
