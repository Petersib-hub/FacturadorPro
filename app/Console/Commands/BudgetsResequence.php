<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Budget;

class BudgetsResequence extends Command
{
    protected $signature = 'budgets:resequence {--year=} {--dry : Simulación, no guarda cambios}';
    protected $description = 'Resecuenciar presupuestos por año (PRES-YYYY-####) en orden por fecha e ID.';

    public function handle(): int
    {
        $yearOpt = $this->option('year');
        $year = $yearOpt ? (int) $yearOpt : (int) now()->year;
        $dry = (bool) $this->option('dry');

        $query = Budget::query()
            ->when(true, fn($q) => $q->where('year', $year))
            ->orderBy('date')->orderBy('id');

        $budgets = $query->get();
        $count = $budgets->count();
        if ($count === 0) {
            $this->info("No hay presupuestos para el año {$year}.");
            return self::SUCCESS;
        }

        DB::beginTransaction();
        try {
            $seq = 0;
            foreach ($budgets as $b) {
                $seq++;
                $number = sprintf('PRES-%d-%04d', $year, $seq);

                if ($dry) {
                    $this->line(sprintf(
                        'DRY: id=%d date=%s -> number=%s, sequence=%d, year=%d',
                        $b->id,
                        optional($b->date)->format('Y-m-d') ?? 'null',
                        $number,
                        $seq,
                        $year
                    ));
                } else {
                    $b->update([
                        'sequence' => $seq,
                        'year'     => $year,
                        'number'   => $number,
                    ]);
                }
            }

            if ($dry) {
                DB::rollBack();
                $this->info("Simulados {$count} registros para año={$year}.");
            } else {
                DB::commit();
                $this->info("Actualizados {$count} registros para año={$year}.");
            }
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $this->info('Resecuenciación de presupuestos finalizada.');
        return self::SUCCESS;
    }
}
