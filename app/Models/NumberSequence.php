<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NumberSequence extends Model
{
    // Trabajamos por (type, series, year)
    protected $fillable = ['user_id','type','series','year','last'];

    /**
     * Genera el siguiente número atómico por tipo+serie+año.
     *  - budget  -> PRES-YYYY-#### (por defecto)
     *  - invoice -> FAC-YYYY-#### (por defecto)
     */
    public static function next(string $type, ?string $series = null, ?int $year = null): string
    {
        $year   = $year   ?? (int) now()->year;
        $series = $series ?? ($type === 'budget' ? 'PRES' : ($type === 'invoice' ? 'FAC' : strtoupper($type)));

        $prefix = $series; // prefijo visible

        return DB::transaction(function () use ($type, $series, $year, $prefix) {
            $row = static::where([
                'type'   => $type,
                'series' => $series,
                'year'   => $year,
            ])->lockForUpdate()->first();

            if (!$row) {
                $row = static::create([
                    'user_id' => null,
                    'type'    => $type,
                    'series'  => $series,
                    'year'    => $year,
                    'last'    => 0,
                ]);
            }

            $row->last = (int) $row->last + 1;
            $row->save();

            $seq = str_pad((string) $row->last, 4, '0', STR_PAD_LEFT);
            return "{$prefix}-{$year}-{$seq}";
        });
    }
}