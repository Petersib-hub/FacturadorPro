<?php

namespace App\Mail;

use App\Models\Budget;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BudgetMail extends Mailable
{
    use Queueable, SerializesModels;

    public Budget $budget;
    protected string $pdfBin;

    public function __construct(Budget $budget, string $pdfBin)
    {
        $this->budget = $budget;
        $this->pdfBin = $pdfBin; // preferimos el binario que ya generaste en el controlador
    }

    public function build()
    {
        $subject = 'Presupuesto ' . ($this->budget->number ?? '');

        // Vistas HTML (principal) con fallback legacy
        $emailView = view()->exists('emails.budget')
            ? 'emails.budget'
            : (view()->exists('emails.budgets.send') ? 'emails.budgets.send' : null);

        // Vista texto plano (opcional)
        $emailPlainView = view()->exists('emails.budget_plain')
            ? 'emails.budget_plain'
            : (view()->exists('emails.budgets.send_plain') ? 'emails.budgets.send_plain' : null);

        // PDF: usa el que llegó por constructor; si viene vacío, re-generamos (fallback)
        $pdfBinary = $this->pdfBin;
        if ($pdfBinary === '' || $pdfBinary === null) {
            $pdfView  = \App\Support\PdfTemplates::budgetView($this->budget->user_id);
            $pdfBinary = \Barryvdh\DomPDF\Facade\Pdf::loadView($pdfView, [
                'budget' => $this->budget,
            ])->output();
        }

        // Build del mensaje
        $m = $this->subject($subject);

        if ($emailView) {
            $m = $m->view($emailView);
        } else {
            // fallback HTML mínimo si no existe ninguna vista
            $m = $m->html('<p>Adjunto presupuesto ' . e($this->budget->number) . '</p>');
        }

        if ($emailPlainView) {
            // Añadimos la alternativa en texto plano
            $m = $m->text($emailPlainView);
        }

        // Adjuntar PDF
        $fileName = ($this->budget->number ?? 'presupuesto') . '.pdf';

        return $m->attachData($pdfBinary, $fileName, ['mime' => 'application/pdf']);
    }
}
