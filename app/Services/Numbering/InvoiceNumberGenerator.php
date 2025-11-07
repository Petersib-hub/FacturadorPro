<?php

namespace App\Services\Numbering;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Genera numeración atómica para facturas.
 * Formato por defecto: FAC-{YYYY}-{####}
 * - Agrupa por año (columna year si existe, si no YEAR(date|issue_date)).
 * - Si existe columna 'series' o 'serie', puedes pasar una serie para agrupar.
 * - Respeta multiusuario via user_id (si lo proporcionas).
 */
class InvoiceNumberGenerator
{
    /**
     * @param int|null    $userId  Agrupar por usuario (recomendado si tu app es multi-tenant por user_id)
     * @param int|null    $year    Año objetivo; si null se deduce de $dateRef o de now()
     * @param string|null $series  Serie (solo si tu tabla tiene 'series'/'serie'); si null se usa 'FAC'
     * @param string|null $dateRef Fecha referencia (Y-m-d o Y-m-d H:i:s) para deducir el año si no hay columna year
     *
     * @return array{year:int,sequence:int,number:string,series:string}
     */
    public function next(?int $userId = null, ?int $year = null, ?string $series = null, ?string $dateRef = null): array
    {
        $table      = 'invoices';
        $cols       = Schema::getColumnListing($table);
        $has        = fn(string $c) => in_array($c, $cols, true);
        $seriesCol  = $has('series') ? 'series' : ($has('serie') ? 'serie' : null);
        $dateCol    = $has('date') ? 'date' : ($has('issue_date') ? 'issue_date' : null);
        $hasYearCol = $has('year');
        $hasSeqCol  = $has('sequence');

        $series  = $series ?? 'FAC';
        $year    = $year   ?? (int)date('Y', $dateRef ? strtotime($dateRef) : time());

        return DB::transaction(function () use ($table, $seriesCol, $dateCol, $hasYearCol, $hasSeqCol, $userId, $series, $year) {
            $q = DB::table($table);
            if ($hasSeqCol) {
                $q->selectRaw('MAX(sequence) as max_seq');
            } else {
                $q->selectRaw('COUNT(*) as max_seq');
            }

            if ($userId !== null && Schema::hasColumn($table, 'user_id')) {
                $q->where('user_id', $userId);
            }

            if ($hasYearCol) {
                $q->where('year', $year);
            } elseif ($dateCol) {
                $q->whereYear($dateCol, $year);
            }

            if ($seriesCol) {
                $q->where($seriesCol, $series);
            }

            $q->lockForUpdate();

            $row     = $q->first();
            $nextSeq = (int)($row->max_seq ?? 0) + 1;

            $num = sprintf('%s-%d-%04d', $series, $year, $nextSeq);

            return [
                'year'     => $year,
                'sequence' => $nextSeq,
                'number'   => $num,
                'series'   => $series,
            ];
        });
    }
}
