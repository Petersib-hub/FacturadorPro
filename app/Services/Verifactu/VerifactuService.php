<?php

namespace App\Services\Verifactu;

use App\Contracts\Verifactu\Verifier;
use App\Contracts\Fiskaly\FiskalyClientInterface;
use App\Models\Verifactu\EventLog;
use App\Enums\Verifactu\LogEventType;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Throwable;

class VerifactuService implements Verifier
{
    public function __construct(
        protected FiskalyClientInterface $provider
    ) {}

    public function preparePayload(object $invoice): array
    {
        // Mapea tu entidad factura → estructura requerida por el proveedor/AEAT
        $payload = [
            'serie' => $invoice->series ?? null,
            'numero' => $invoice->number ?? null,
            'fechaExpedicion' => $invoice->issue_date?->format('Y-m-d'),
            'emisor' => [
                'nif' => $invoice->company_vat ?? null,
                'nombre' => $invoice->company_name ?? null,
            ],
            'receptor' => [
                'nif' => $invoice->customer_vat ?? null,
                'nombre' => $invoice->customer_name ?? null,
            ],
            'totales' => [
                'base' => (float)($invoice->subtotal ?? 0),
                'impuestos' => (float)($invoice->tax_total ?? 0),
                'total' => (float)($invoice->total ?? 0),
            ],
            'hashAnterior' => $invoice->chain_previous_hash ?? null,
        ];
        return $payload;
    }

    public function send(array $payload): array
    {
        // Delegamos al proveedor (p.ej., Fiskaly SIGN ES)
        return $this->provider->sendInvoice($payload);
    }

    public function verify(object $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            $invoice->verifactu_status = 'pending';
            $invoice->save();

            $payload = $this->preparePayload($invoice);
            $response = $this->send($payload);

            // Actualiza campos críticos
            $invoice->verification_hash = $response['verification_hash'] ?? ($invoice->computeHash() ?? null);
            $invoice->verification_qr = $this->buildQr($payload, $response);
            $invoice->verifactu_payload = $payload;
            $invoice->verifactu_response = $response;
            $invoice->verifactu_status = ($response['ok'] ?? false) ? 'verified' : 'failed';
            $invoice->verifactu_verified_at = now();
            $invoice->verifactu_attempts = ($invoice->verifactu_attempts ?? 0) + 1;
            $invoice->verifactu_error = ($response['ok'] ?? false) ? null : ($response['error'] ?? 'Error desconocido');
            $invoice->save();

            // Log
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
        // Devuelve SVG del QR (usar una librería real en producción).
        // Aquí devolvemos un placeholder con datos mínimos.
        $data = http_build_query([
            'nif' => $payload['emisor']['nif'] ?? '',
            'num' => $payload['numero'] ?? '',
            'fec' => $payload['fechaExpedicion'] ?? '',
            'tot' => $payload['totales']['total'] ?? 0,
            'hash' => $providerResponse['verification_hash'] ?? '',
        ]);
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="120" height="120"><rect width="120" height="120" fill="#eee"/><text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" font-size="10">QR</text><title>' . htmlspecialchars($data) . '</title></svg>';
        return $svg;
    }

    public function exportMonthly(string $period, object $tenant): string
    {
        // Implementación real: construir XML conforme a la Orden y empaquetar ZIP
        // Devolvemos una ruta simulada de salida
        return storage_path('app/verifactu/exports/' . $period . '/export.zip');
    }
}
