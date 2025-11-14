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
        $this->pdfBin = $pdfBin; // binario ya generado
    }

    public function build()
    {
        $subject = 'Presupuesto ' . ($this->budget->number ?? '');

        $emailView = view()->exists('emails.budget')
            ? 'emails.budget'
            : (view()->exists('emails.budgets.send') ? 'emails.budgets.send' : null);

        $emailPlainView = view()->exists('emails.budget_plain')
            ? 'emails.budget_plain'
            : (view()->exists('emails.budgets.send_plain') ? 'emails.budgets.send_plain' : null);

        $pdfBinary = $this->pdfBin;
        if ($pdfBinary === '' || $pdfBinary === null) {
            $pdfView   = \App\Support\PdfTemplates::budgetView($this->budget->user_id);
            $pdfBinary = \Barryvdh\DomPDF\Facade\Pdf::loadView($pdfView, [
                'budget' => $this->budget,
            ])->output();
        }

        $m = $this->subject($subject)
                 ->with(['budget' => $this->budget]);

        if ($emailView) {
            $m = $m->view($emailView);
        } else {
            $m = $m->html('<p>Adjunto presupuesto ' . e($this->budget->number) . '</p>');
        }

        if ($emailPlainView) {
            $m = $m->text($emailPlainView);
        }

        $fileName = ($this->budget->number ?? 'presupuesto') . '.pdf';

        return $m->attachData($pdfBinary, $fileName, ['mime' => 'application/pdf']);
    }
}