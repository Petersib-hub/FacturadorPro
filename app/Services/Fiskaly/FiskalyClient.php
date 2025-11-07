<?php

namespace App\Services\Fiskaly;

use App\Contracts\Fiskaly\FiskalyClientInterface;

class FiskalyClient implements FiskalyClientInterface
{
    public function ensureOrganization(array $data): array
    {
        // Llamadas reales a la API del proveedor
        return ['ok' => true, 'id' => 'org_123'];
    }

    public function ensureTaxpayer(array $data): array
    {
        return ['ok' => true, 'id' => 'tax_123'];
    }

    public function ensureSigner(array $data): array
    {
        return ['ok' => true, 'id' => 'sig_123'];
    }

    public function ensureClient(array $data): array
    {
        return ['ok' => true, 'id' => 'cli_123', 'api_key' => '***'];
    }

    public function sendInvoice(array $payload): array
    {
        // Simula operación exitosa
        return [
            'ok' => true,
            'verification_hash' => hash('sha256', json_encode($payload)),
            'provider_id' => 'inv_123',
        ];
    }
}
