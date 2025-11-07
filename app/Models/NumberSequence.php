<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class NumberSequence extends Model
{
    protected $fillable = ['user_id','type','year','last'];

    /**
     * Genera el siguiente número para el usuario y tipo.
     * Formatos:
     *  - budget : PRES-YYYY-#### (relleno 4)
     *  - invoice: FAC-YYYY-####  (relleno 4)
     */
    public static function next(string $type, int $userId): string
    {
        $year = (int) now()->year;

        $prefix = match ($type) {
            'budget'  => 'PRES',
            'invoice' => 'FAC',
            default   => strtoupper($type),
        };

        return DB::transaction(function () use ($type, $userId, $year, $prefix) {
            $row = static::where([
                    'user_id' => $userId,
                    'type'    => $type,
                    'year'    => $year,
                ])
                ->lockForUpdate()
                ->first();

            if (!$row) {
                $row = static::create([
                    'user_id' => $userId,
                    'type'    => $type,
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
