<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceDueReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;
    protected string $pdfBin;

    public function __construct(Invoice $invoice, string $pdfBin)
    {
        $this->invoice = $invoice;
        $this->pdfBin  = $pdfBin;
    }

    public function build()
    {
        $subject = 'Recordatorio de vencimiento — ' . ($this->invoice->number ?? 'Factura');

        $view = view()->exists('emails.invoices.reminder')
            ? 'emails.invoices.reminder'
            : (view()->exists('emails.invoice') ? 'emails.invoice' : null);

        if (!$view) {
            return $this->subject($subject)
                ->html('<p>Recordatorio de pago para la factura '.$this->invoice->number.'</p>')
                ->attachData($this->pdfBin, ($this->invoice->number ?? 'factura').'.pdf', ['mime'=>'application/pdf']);
        }

        return $this->subject($subject)
            ->view($view)
            ->attachData($this->pdfBin, ($this->invoice->number ?? 'factura').'.pdf', ['mime'=>'application/pdf']);
    }
}
