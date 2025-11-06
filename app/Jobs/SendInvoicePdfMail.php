<?php

namespace App\Jobs;

use App\Mail\InvoicePdfMail;
use App\Models\Invoice;
use App\Models\UserSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendInvoicePdfMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $invoiceId, public string $toEmail) {}

    public function handle(): void
    {
        $invoice = Invoice::with('client')->findOrFail($this->invoiceId);
        $settings = UserSetting::firstWhere('user_id', $invoice->user_id);

        Mail::to($this->toEmail)
            ->send(new InvoicePdfMail(
                invoice: $invoice,
                bankAccount: $settings?->bank_account,
                billingNotes: $settings?->billing_notes
            ));
    }
}
