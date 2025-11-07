<?php

namespace App\Contracts\Verifactu;

use Illuminate\Contracts\Support\Arrayable;

interface Verifier
{
    /** Prepara el payload de alta/anulación con el esquema requerido */
    public function preparePayload(object $invoice): array;

    /** Envía el registro a través del proveedor seleccionado y devuelve respuesta */
    public function send(array $payload): array;

    /** Verifica y persiste cambios en la factura (hash, estado, respuesta) */
    public function verify(object $invoice): void;

    /** Genera el contenido SVG del QR para incluir en el PDF */
    public function buildQr(array $payload, array $providerResponse): string;

    /** Genera exportaciones mensuales (YYYY-MM) por tenant */
    public function exportMonthly(string $period, object $tenant): string;
}
