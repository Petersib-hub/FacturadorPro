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
        $year = now()->year;

        $prefix = match ($type) {
            'budget'  => 'PRES',
            'invoice' => 'FAC', // coherente con el resto del proyecto
            default   => strtoupper($type),
        };

        return DB::transaction(function () use ($type, $userId, $year, $prefix) {
            // Localiza o crea la fila del contador para (user_id, type, year)
            // y la bloquea para evitar carreras.
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
                // Nota: al crear ya estamos dentro de la transacción.
            }

            // Incrementa el contador y guarda
            $row->last = (int) $row->last + 1;
            $row->save();

            // Formatea la secuencia a 4 dígitos
            $seq = str_pad((string) $row->last, 4, '0', STR_PAD_LEFT);

            return "{$prefix}-{$year}-{$seq}";
        });
    }
}
