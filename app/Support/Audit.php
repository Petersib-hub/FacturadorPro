<?php

namespace App\Support;

use App\Models\AuditLog;

class Audit
{
    /**
     * Registra una auditoría.
     *
     * @param string $action       ej: invoice.created, invoice.updated, budget.sent, invoice.pdf, etc.
     * @param string|null $type    ej: 'invoice' | 'budget' | 'payment'
     * @param int|string|null $id  id de la entidad
     * @param array $meta          datos adicionales
     */
    public static function record(string $action, ?string $type = null, $id = null, array $meta = []): void
    {
        try {
            $req = request();
            AuditLog::create([
                'user_id'     => optional($req->user())->id,
                'action'      => $action,
                'entity_type' => $type,
                'entity_id'   => $id,
                'meta'        => array_merge([
                    'payload'    => $req->except(['password','password_confirmation','_token']),
                    'user_agent' => substr($req->userAgent() ?? '', 0, 190),
                    'status'     => optional(response())->status() ?: null,
                ], $meta),
                'route'  => $req->path(),
                'method' => $req->method(),
                'ip'     => $req->ip(),
            ]);
        } catch (\Throwable $e) {
            // Nunca romper el flujo por el log; si quieres, guarda en laravel.log:
            // \Log::warning('Audit error: '.$e->getMessage());
        }
    }
}
