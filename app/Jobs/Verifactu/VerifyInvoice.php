<?php

namespace App\Jobs\Verifactu;

use App\Services\Verifactu\VerifactuService;
use App\Support\Verifactu\EventLogger;
use App\Enums\Verifactu\LogEventType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerifyInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public $invoice, public $tenant = null) {}

    public function handle(VerifactuService $service): void
    {
        EventLogger::log(LogEventType::INVOICE_VERIFICATION_REQUESTED->value, ['invoice_id' => $this->invoice->id], null, $this->tenant);
        $service->verify($this->invoice);
    }
}
