<?php

namespace App\Services\Verifactu;

use InvalidArgumentException;

class PayloadBuilder
{
    public function buildAlta(object $invoice): array
    {
        $payload = [
            'tipoRegistro' => 'ALTA',
            'serie' => $invoice->series ?? null,
            'numero' => $invoice->number ?? null,
            'fechaExpedicion' => optional($invoice->issue_date)->format('Y-m-d') ?: ($invoice->issue_date ?? null),
            'emisor' => [
                'nif' => $invoice->company_vat ?? $invoice->issuer_vat ?? null,
                'nombre' => $invoice->company_name ?? $invoice->issuer_name ?? null,
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
        $this->assertRequired($payload, ['serie','numero','fechaExpedicion','emisor.nif','totales.total']);
        return $payload;
    }

    public function buildAnulacion(object $invoice, string $motivo = 'Anulación'): array
    {
        $payload = [
            'tipoRegistro' => 'ANULACION',
            'serie' => $invoice->series ?? null,
            'numero' => $invoice->number ?? null,
            'fechaExpedicion' => optional($invoice->issue_date)->format('Y-m-d') ?: ($invoice->issue_date ?? null),
            'motivo' => $motivo,
            'emisor' => [
                'nif' => $invoice->company_vat ?? $invoice->issuer_vat ?? null,
                'nombre' => $invoice->company_name ?? $invoice->issuer_name ?? null,
            ],
            'referenciaHash' => $invoice->verification_hash ?? null,
        ];
        $this->assertRequired($payload, ['serie','numero','fechaExpedicion','emisor.nif','referenciaHash']);
        return $payload;
    }

    protected function assertRequired(array $data, array $keys): void
    {
        foreach ($keys as $key) {
            $value = $this->arrayGet($data, $key);
            if ($value === null || $value === '') {
                throw new InvalidArgumentException("Campo requerido faltante: {$key}");
            }
        }
    }

    protected function arrayGet(array $array, string $key)
    {
        if (str_contains($key, '.')) {
            $segments = explode('.', $key);
            $current = $array;
            foreach ($segments as $segment) {
                if (!is_array($current) || !array_key_exists($segment, $current)) return null;
                $current = $current[$segment];
            }
            return $current;
        }
        return $array[$key] ?? null;
    }
}
