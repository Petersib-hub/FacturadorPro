<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;
    protected string $pdfBin;

    public function __construct(Invoice $invoice, string $pdfBin)
    {
        $this->invoice = $invoice;
        $this->pdfBin  = $pdfBin; // preferimos el binario que ya generaste en el controlador
    }

    public function build()
    {
        $subject = 'Factura ' . ($this->invoice->number ?? '');

        // Vistas HTML (principal) con fallback legacy
        $emailView = view()->exists('emails.invoice')
            ? 'emails.invoice'
            : (view()->exists('emails.invoices.send') ? 'emails.invoices.send' : null);

        // Vista texto plano (opcional)
        $emailPlainView = view()->exists('emails.invoice_plain')
            ? 'emails.invoice_plain'
            : (view()->exists('emails.invoices.send_plain') ? 'emails.invoices.send_plain' : null);

        // PDF: usa el que llegó por constructor; si viene vacío, re-generamos (fallback)
        $pdfBinary = $this->pdfBin;
        if ($pdfBinary === '' || $pdfBinary === null) {
            $pdfView  = \App\Support\PdfTemplates::invoiceView($this->invoice->user_id);
            $pdfBinary = \Barryvdh\DomPDF\Facade\Pdf::loadView($pdfView, [
                'invoice' => $this->invoice,
            ])->output();
        }

        // Build del mensaje
        $m = $this->subject($subject);

        if ($emailView) {
            $m = $m->view($emailView);
        } else {
            // fallback HTML mínimo si no existe ninguna vista
            $m = $m->html('<p>Adjunto factura ' . e($this->invoice->number) . '</p>');
        }

        if ($emailPlainView) {
            // Añadimos la alternativa en texto plano
            $m = $m->text($emailPlainView);
        }

        // Adjuntar PDF
        $fileName = ($this->invoice->number ?? 'factura') . '.pdf';

        return $m->attachData($pdfBinary, $fileName, ['mime' => 'application/pdf']);
    }
}
