<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Resequencia las facturas por año (y opcionalmente por serie) en orden cronológico,
 * generando number con formato "FAC-YYYY-####" (o con la serie real si existe la columna "series"/"serie").
 *
 * Opciones:
 *  --user_id=        : Limitar a un usuario concreto
 *  --series=FAC      : Serie a usar si quieres forzar una serie al generar el number (si NO usas --per-series)
 *  --per-series      : Si existe columna series/serie, resecuencia por cada serie separadamente
 *  --year=YYYY       : Limitar a un año concreto
 *  --dry             : Simulación (no guarda)
 *  --force           : No pedir confirmación
 *
 * Ejemplos:
 *   php artisan invoices:resequence --dry
 *   php artisan invoices:resequence --year=2025 --force
 *   php artisan invoices:resequence --per-series --force
 *   php artisan invoices:resequence --user_id=3 --series=FAC --year=2025 --force
 */
class InvoiceResequence extends Command
{
    protected $signature = 'invoices:resequence
        {--user_id= : Restringir a un usuario}
        {--series=FAC : Serie a usar si no se resecuencia por series}
        {--per-series : Resecuenciar por serie (si existe columna series/serie)}
        {--year= : Solo un año concreto (YYYY)}
        {--dry : Simulación, no guarda}
        {--force : Ejecuta sin confirmación}';

    protected $description = 'Resecuenciar facturas por año (y opcionalmente por serie), asignando number como SERIES-YYYY-#### sin colisiones.';

    public function handle(): int
    {
        $Budget = \App\Models\Invoice::class; // por coherencia con tu proyecto
        $table  = (new $Budget)->getTable();

        // Detectar columnas disponibles
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
        $has = fn(string $c) => in_array($c, $columns, true);
        $seriesCol = $has('series') ? 'series' : ($has('serie') ? 'serie' : null);
        $dateCol   = $has('date') ? 'date' : ($has('issue_date') ? 'issue_date' : ($has('created_at') ? 'created_at' : null));
        if (!$dateCol) {
            $this->error("No se encontró columna de fecha ('date' | 'issue_date' | 'created_at') en {$table}.");
            return self::FAILURE;
        }

        $userId    = $this->option('user_id') ?: null;
        $force     = (bool)$this->option('force');
        $dry       = (bool)$this->option('dry');
        $perSeries = (bool)$this->option('per-series') && (bool)$seriesCol; // solo si existe columna serie
        $forcedSer = $this->option('series') ?: 'FAC';
        $yearOpt   = $this->option('year') ? (int)$this->option('year') : null;

        if (!$force && !$dry) {
            $this->line('Este comando modificará number/sequence/year de facturas.');
            if ($perSeries) {
                $this->line('Modo: per-series (cada serie tendrá su propia secuencia).');
            } else {
                $this->line("Modo: serie fija '{$forcedSer}' para el number.");
            }
            if (!$this->confirm('¿Continuar?')) {
                $this->info('Cancelado.');
                return self::SUCCESS;
            }
        }

        // Determinar años a procesar
        $years = [];
        if ($yearOpt) {
            $years = [$yearOpt];
        } else {
            $years = $Budget::query()
                ->when($userId, fn($q) => $q->where('user_id', $userId))
                ->whereNull('deleted_at')
                ->when($has('year'), fn($q) => $q->whereNotNull('year'))
                ->distinct()
                ->pluck($has('year') ? 'year' : DB::raw("YEAR({$dateCol})"))
                ->filter()
                ->map(fn($y) => (int)$y)
                ->sort()
                ->values()
                ->all();

            if (empty($years)) {
                // Fallback: si no hay year, tomamos el mínimo año en base a la fecha
                $min = $Budget::query()
                    ->when($userId, fn($q) => $q->where('user_id', $userId))
                    ->whereNull('deleted_at')
                    ->min(DB::raw("YEAR({$dateCol})"));
                $years = [$min ?: (int)date('Y')];
            }
        }

        foreach ($years as $year) {
            if ($perSeries) {
                // Obtener series existentes para ese año
                $seriesValues = $seriesCol
                    ? $Budget::query()
                        ->when($userId, fn($q) => $q->where('user_id', $userId))
                        ->whereNull('deleted_at')
                        ->where(function ($q) use ($year, $dateCol, $has) {
                            if ($has('year')) $q->where('year', $year);
                            $q->orWhereYear($dateCol, $year);
                        })
                        ->whereNotNull($seriesCol)
                        ->distinct()
                        ->pluck($seriesCol)
                        ->filter()
                        ->values()
                        ->all()
                    : [null];

                foreach ($seriesValues as $ser) {
                    $this->resecuenciaGrupo($Budget, $table, $dateCol, $year, $userId, $dry, $ser, $seriesCol);
                }
            } else {
                // Resecuencia con una sola serie impuesta en el number
                $this->resecuenciaGrupo($Budget, $table, $dateCol, $year, $userId, $dry, $forcedSer, $seriesCol, forceSeriesInNumber: true);
            }
        }

        $this->info('Resecuenciación de facturas finalizada.');
        return self::SUCCESS;
    }

    /**
     * Resecuencia un grupo: por año (y serie si aplica), en orden cronológico + id.
     */
    protected function resecuenciaGrupo(
        string $Model,
        string $table,
        string $dateCol,
        int $year,
        ?int $userId,
        bool $dry,
        ?string $seriesValue,
        ?string $seriesCol,
        bool $forceSeriesInNumber = false
    ): void {
        DB::transaction(function () use ($Model, $table, $dateCol, $year, $userId, $dry, $seriesValue, $seriesCol, $forceSeriesInNumber) {
            $q = $Model::query()
                ->when($userId, fn($qq) => $qq->where('user_id', $userId))
                ->whereNull('deleted_at')
                ->where(function ($qq) use ($year, $dateCol) {
                    $qq->where('year', $year)
                       ->orWhereYear($dateCol, $year);
                });

            if ($seriesCol && $seriesValue !== null) {
                $q->where($seriesCol, $seriesValue);
            }

            $rows = $q->orderBy($dateCol)->orderBy('id')->lockForUpdate()->get();

            $seq = 1;
            $count = 0;
            $useSeriesForNumber = $forceSeriesInNumber
                ? (string)$seriesValue
                : ($seriesValue ?? 'FAC');

            foreach ($rows as $row) {
                $num = sprintf('%s-%d-%04d', $useSeriesForNumber, $year, $seq);

                if ($dry) {
                    $this->line(sprintf(
                        'DRY: id=%d (%s %s) -> number=%s, sequence=%d, year=%d%s',
                        $row->id,
                        $seriesCol ? "{$seriesCol}={$row->{$seriesCol}}" : 'sin_serie',
                        "{$dateCol}={$row->{$dateCol}}",
                        $num,
                        $seq,
                        $year,
                        $seriesCol ? ", set_series=" . ($seriesValue ?? '(sin cambio)') : ''
                    ));
                } else {
                    $row->number = $num;
                    if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'sequence')) {
                        $row->sequence = $seq;
                    }
                    if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'year')) {
                        $row->year = $year;
                    }
                    if ($seriesCol && $seriesValue !== null) {
                        // Actualizar la columna series/serie si queremos unificar a esa serie específica
                        $row->{$seriesCol} = $seriesValue;
                    }
                    $row->save();
                }

                $seq++;
                $count++;
            }

            $labelSerie = $seriesCol
                ? (' serie=' . ($seriesValue ?? '(todas)'))
                : '';
            $this->info(($dry ? 'Simulados ' : 'Actualizados ') . "{$count} registros para año={$year}{$labelSerie}.");
        });
    }
}