<?php

namespace App\Support;

use App\Models\ComplianceEventLog;
use Illuminate\Support\Facades\DB;

class Compliance
{
    /**
     * Registro de evento de cumplimiento con cadena hash (art. 9 + art. 13).
     * $article: "art. 9.1.a", "art. 13", "art. 20", etc. (Orden HAC/1177/2024)
     * $code:    nombre corto del evento ("NO_VERIFACTU_START", "VERIFY_OK", etc.)
     */
    public static function log(string $article, string $code, ?string $message = null, array $payload = [],
                               ?string $entityType = null, ?int $entityId = null, ?int $userId = null,
                               string $boeRef = 'BOE-A-2024-22138'): ComplianceEventLog
    {
        return DB::transaction(function () use ($article, $code, $message, $payload, $entityType, $entityId, $userId, $boeRef) {
            $prev = ComplianceEventLog::query()->orderByDesc('id')->lockForUpdate()->first();
            $prevHash = $prev?->hash;

            $toHash = [
                'boe_ref'    => $boeRef,
                'article'    => $article,
                'code'       => $code,
                'message'    => $message,
                'entity'     => $entityType . ':' . ($entityId ?? ''),
                'user_id'    => $userId,
                'payload'    => $payload,
                'prev_hash'  => $prevHash,
                'timestamp'  => now()->toIso8601String(),
            ];
            $hash = hash('sha256', json_encode($toHash, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));

            return ComplianceEventLog::create([
                'boe_ref'    => $boeRef,
                'article'    => $article,
                'code'       => $code,
                'message'    => $message,
                'entity_type'=> $entityType,
                'entity_id'  => $entityId,
                'user_id'    => $userId,
                'payload'    => $payload ?: null,
                'prev_hash'  => $prevHash,
                'hash'       => $hash,
                'created_at' => now(),
            ]);
        });
    }

    /** Checks básicos para el checklist (art. 6–8, 9, 13–15, 20–21). */
    public static function checklistStatus(): array
    {
        $ok = fn($b) => $b ? 'OK' : 'PEND';
        $existsTable = fn($name) => \Illuminate\Support\Facades\Schema::hasTable($name);
        $hasCol = fn($t,$c) => \Illuminate\Support\Facades\Schema::hasColumn($t,$c);

        return [
            // Art. 6 Integridad/Inalterabilidad -> hash en eventos y encadenamiento
            'art_6_integridad' => $ok($existsTable('compliance_event_logs') && $hasCol('compliance_event_logs','hash') && $hasCol('compliance_event_logs','prev_hash')),
            // Art. 7 Trazabilidad -> prev_hash + referencia entidad
            'art_7_trazabilidad' => $ok($existsTable('compliance_event_logs') && $hasCol('compliance_event_logs','prev_hash') && $hasCol('compliance_event_logs','entity_type')),
            // Art. 8 Conservación/Accesibilidad/Legibilidad -> existe exportación y timestamps
            'art_8_conservacion' => $ok($existsTable('compliance_event_logs') && $hasCol('compliance_event_logs','created_at')),
            // Art. 9 Registro de eventos -> tabla y códigos
            'art_9_registro' => $ok($existsTable('compliance_event_logs')),
            // Art. 13 Huella/hash
            'art_13_huella' => $ok($existsTable('compliance_event_logs') && $hasCol('compliance_event_logs','hash')),
            // Art. 14 Firma electrónica (marcamos PEND hasta que se firme el XML/JSON de facturación)
            'art_14_firma' => 'PEND',
            // Art. 15 Declaración responsable -> vista declaracion + ruta
            'art_15_declaracion' => $ok(view()->exists('compliance.declaracion')),
            // Art. 20–21 Código QR -> comprobamos include (tu parcial verifactu._qr)
            'art_20_21_qr' => $ok(view()->exists('verifactu._qr')),
        ];
    }
}