<?php

namespace App\Contracts\Fiskaly;

interface FiskalyClientInterface
{
    /** Crea/obtiene organización/remotos necesarios */
    public function ensureOrganization(array $data): array;

    /** Crea/obtiene taxpayer */
    public function ensureTaxpayer(array $data): array;

    /** Crea/obtiene signer */
    public function ensureSigner(array $data): array;

    /** Crea/obtiene client (credenciales) */
    public function ensureClient(array $data): array;

    /** Envía alta/anulación de factura y devuelve respuesta del proveedor */
    public function sendInvoice(array $payload): array;
}
