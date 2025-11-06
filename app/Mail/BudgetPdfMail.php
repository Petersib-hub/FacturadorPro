<?php

namespace App\Mail;

use App\Models\Budget;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BudgetPdfMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Budget $budget,
        public ?string $bankAccount = null,
        public ?string $billingNotes = null
    ) {}

    public function build()
    {
        $pdf = Pdf::loadView('pdf.budget', [
            'budget'       => $this->budget,
            'bank_account' => $this->bankAccount,
            'billing_notes'=> $this->billingNotes,
        ])->setPaper('a4');

        $fileName = 'Presupuesto-'.$this->budget->number.'.pdf';

        return $this->subject('Presupuesto '.$this->budget->number)
            ->view('emails.budget')
            ->attachData($pdf->output(), $fileName, ['mime' => 'application/pdf']);
    }
}
