<?php

namespace App\Services\Verifactu;

use App\Contracts\Verifactu\Verifier;
use App\Contracts\Fiskaly\FiskalyClientInterface;
use App\Models\Verifactu\EventLog;
use App\Enums\Verifactu\LogEventType;
use Illuminate\Support\Facades\DB;

class VerifactuService implements Verifier
{
    public function __construct(
        protected FiskalyClientInterface $provider,
        protected PayloadBuilder $builder,
        protected QrService $qr,
        protected Exporter $exporter
    ) {}

    public function preparePayload(object $invoice): array
    {
        return $this->builder->buildAlta($invoice);
    }

    public function send(array $payload): array
    {
        return $this->provider->sendInvoice($payload);
    }

    public function verify(object $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            $invoice->verifactu_status = 'pending';
            $invoice->save();

            $payload = $this->preparePayload($invoice);
            $response = $this->send($payload);

            $invoice->verification_hash = $response['verification_hash'] ?? $invoice->computeHash();
            $invoice->verification_qr = $this->buildQr($payload, $response);
            $invoice->verifactu_payload = $payload;
            $invoice->verifactu_response = $response;
            $invoice->verifactu_status = ($response['ok'] ?? false) ? 'verified' : 'failed';
            $invoice->verifactu_verified_at = now();
            $invoice->verifactu_attempts = ($invoice->verifactu_attempts ?? 0) + 1;
            $invoice->verifactu_error = ($response['ok'] ?? false) ? null : ($response['error'] ?? 'Error desconocido');
            $invoice->save();

            EventLog::create([
                'tenant_type' => $invoice->tenant_type ?? null,
                'tenant_id' => $invoice->tenant_id ?? null,
                'event_type' => ($response['ok'] ?? false) ? LogEventType::INVOICE_VERIFIED->value : LogEventType::INVOICE_VERIFICATION_FAILED->value,
                'source' => 'system',
                'context' => [
                    'invoice_id' => $invoice->id,
                    'status' => $invoice->verifactu_status,
                    'provider_response' => $response,
                ],
            ]);
        });
    }

    public function buildQr(array $payload, array $providerResponse): string
    {
        return $this->qr->makeSvg($payload, $providerResponse);
    }

    public function exportMonthly(string $period, object $tenant): string
    {
        return $this->exporter->generateMonthly($period, $tenant);
    }
}
