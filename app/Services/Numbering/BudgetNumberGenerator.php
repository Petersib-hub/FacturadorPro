<?php

namespace App\Services\Numbering;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BudgetNumberGenerator
{
    /**
     * Genera la siguiente numeración de presupuesto de forma atómica.
     * Formato: PRES-{YYYY}-{####}
     */
    public function next(?int $year = null, string $series = 'PRES'): array
    {
        $year = $year ?: now()->year;

        return DB::transaction(function () use ($year, $series) {
            // Bloquea filas candidatas para evitar carrera
            $row = DB::table('budgets')
                ->selectRaw('MAX(sequence) as max_seq')
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            $next = (int) ($row->max_seq ?? 0) + 1;

            $number = sprintf('%s-%d-%04d', $series, $year, $next);

            return [
                'year'     => $year,
                'sequence' => $next,
                'number'   => $number,
                'series'   => $series,
            ];
        });
    }
}