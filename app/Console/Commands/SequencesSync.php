<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SequencesSync extends Command
{
    protected $signature = 'sequences:sync
        {--type=all : budget|invoice|all}
        {--year= : Solo un año (YYYY)}
        {--series= : Forzar serie. Si no se especifica y la tabla no tiene columna series, usa PRES (budgets) o FAC (invoices)}
        {--force : No pedir confirmación}';

    protected $description = 'Alinea number_sequences.last = MAX(sequence) por (type,series,year).';

    public function handle(): int
    {
        $type    = strtolower($this->option('type') ?: 'all');
        $yearOpt = $this->option('year') ? (int)$this->option('year') : null;
        $seriesOpt = $this->option('series');

        if (!$this->option('force') && !$this->confirm('Esto actualizará contadores. ¿Continuar?')) {
            $this->info('Cancelado.');
            return self::SUCCESS;
        }

        $targets = [];
        if ($type === 'all' || $type === 'budget')  $targets[] = ['table' => 'budgets',  'type' => 'budget'];
        if ($type === 'all' || $type === 'invoice') $targets[] = ['table' => 'invoices', 'type' => 'invoice'];

        foreach ($targets as $t) {
            $this->syncOne($t['table'], $t['type'], $yearOpt, $seriesOpt);
        }

        $this->info('Sincronización completada.');
        return self::SUCCESS;
    }

    protected function syncOne(string $table, string $type, ?int $yearFilter, ?string $seriesOpt): void
    {
        if (!Schema::hasTable($table)) {
            $this->warn("Tabla {$table} no existe, saltando.");
            return;
        }
        $hasYear   = Schema::hasColumn($table, 'year');
        $hasSeq    = Schema::hasColumn($table, 'sequence');
        $hasSeries = Schema::hasColumn($table, 'series') || Schema::hasColumn($table, 'serie');

        if (!$hasSeq) {
            $this->warn("Tabla {$table} no tiene columna 'sequence', saltando.");
            return;
        }

        $yearExprStr = $hasYear ? 'year'
            : (Schema::hasColumn($table, 'date') ? 'YEAR(`date`)' : 'YEAR(`created_at`)');

        // Series a procesar
        $seriesList = collect();

        if ($seriesOpt) {
            $seriesList = collect([$seriesOpt]);
        } elseif ($hasSeries) {
            $seriesCol = Schema::hasColumn($table, 'series') ? 'series' : 'serie';
            $seriesList = DB::table($table)
                ->when($yearFilter, function ($q) use ($hasYear, $yearFilter, $table) {
                    if ($hasYear) $q->where('year', $yearFilter);
                    elseif (Schema::hasColumn($table, 'date')) $q->whereYear('date', $yearFilter);
                    else $q->whereYear('created_at', $yearFilter);
                })
                ->select($seriesCol.' as s')
                ->distinct()
                ->pluck('s')
                ->filter()
                ->map(fn($s) => (string)$s);
        } else {
            // Por defecto: PRES para budgets, FAC para invoices
            $seriesList = collect([$type === 'budget' ? 'PRES' : 'FAC']);
        }

        if ($seriesList->isEmpty()) {
            $this->warn("Sin series detectadas para {$table}; usando por defecto.");
            $seriesList = collect([$type === 'budget' ? 'PRES' : 'FAC']);
        }

        foreach ($seriesList as $series) {
            // Años presentes para esta serie
            $years = DB::table($table)
                ->when($yearFilter, function ($q) use ($hasYear, $yearFilter, $table) {
                    if ($hasYear) $q->where('year', $yearFilter);
                    elseif (Schema::hasColumn($table, 'date')) $q->whereYear('date', $yearFilter);
                    else $q->whereYear('created_at', $yearFilter);
                })
                ->when($hasSeries, function ($q) use ($table, $series) {
                    $col = Schema::hasColumn($table, 'series') ? 'series' : 'serie';
                    $q->where($col, $series);
                })
                ->when($hasYear, fn($q) => $q->whereNotNull('year'))
                ->selectRaw($yearExprStr . ' as y')
                ->distinct()
                ->pluck('y')
                ->filter()
                ->map(fn($y) => (int)$y);

            if ($years->isEmpty()) {
                // Si no hay registros, pero queremos preparar el contador del año indicado:
                if ($yearFilter) {
                    $this->upsertCounter($type, $series, (int)$yearFilter, 0);
                    $this->info("Prep {$type} serie={$series} año={$yearFilter}: last=0 (sin datos).");
                }
                continue;
            }

            foreach ($years as $year) {
                $max = DB::table($table)
                    ->when($hasYear, fn($q) => $q->where('year', $year))
                    ->when(!$hasYear && Schema::hasColumn($table, 'date'), fn($q) => $q->whereYear('date', $year))
                    ->when(!$hasYear && !Schema::hasColumn($table, 'date') && Schema::hasColumn($table, 'created_at'), fn($q) => $q->whereYear('created_at', $year))
                    ->when($hasSeries, function ($q) use ($table, $series) {
                        $col = Schema::hasColumn($table, 'series') ? 'series' : 'serie';
                        $q->where($col, $series);
                    })
                    ->max('sequence');

                $this->upsertCounter($type, $series, (int)$year, (int)$max);
                $this->info("Sync {$type} serie={$series} año={$year}: last=".(int)$max.".");
            }
        }
    }

    protected function upsertCounter(string $type, string $series, int $year, int $last): void
    {
        DB::transaction(function () use ($type, $series, $year, $last) {
            $existing = DB::table('number_sequences')->where([
                'type'   => $type,
                'series' => $series,
                'year'   => $year,
            ])->lockForUpdate()->first();

            if ($existing) {
                DB::table('number_sequences')->where('id', $existing->id)->update([
                    'last'       => max((int)$existing->last, $last),
                    'updated_at' => now(),
                ]);
            } else {
                DB::table('number_sequences')->insert([
                    'user_id'    => null,
                    'type'       => $type,
                    'series'     => $series,
                    'year'       => $year,
                    'last'       => $last,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}