<?php

namespace App\Models\Concerns;

use App\Services\Verifactu\VerifactuService;

trait HasVerifactu
{
    public function getVerifactuStatusLabelAttribute(): string
    {
        return match ($this->verifactu_status) {
            'draft' => 'Borrador',
            'pending' => 'Pendiente',
            'verified' => 'Verificada',
            'failed' => 'Con errores',
            default => ucfirst($this->verifactu_status ?? 'desconocido'),
        };
    }

    public function verificationHash(): ?string
    {
        return $this->verification_hash;
    }

    public function verifactuQrSvg(): ?string
    {
        return $this->verification_qr;
    }

    public function buildChainPayload(): array
    {
        // Devuelve un subconjunto estable de campos para hash (ajústalo a tu esquema)
        return [
            'series' => $this->series ?? null,
            'number' => $this->number ?? null,
            'issue_date' => $this->issue_date?->format('Y-m-d'),
            'customer_vat' => $this->customer_vat ?? null,
            'total' => (string)($this->total ?? '0.00'),
            'previous_hash' => $this->chain_previous_hash ?? null,
        ];
    }

    public function computeHash(): string
    {
        $payload = $this->buildChainPayload();
        return hash('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    public function verifyWithVerifactu(VerifactuService $service): void
    {
        $service->verifyInvoice($this);
    }
}
