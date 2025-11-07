<?php

namespace App\Models;

use App\Models\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Budget extends Model
{
    use Tenantable, SoftDeletes;

    protected $fillable = [
        'user_id',
        'client_id',
        'number',
        'sequence',
        'year',
        'date',
        'due_date',
        'subtotal',
        'tax_total',
        'total',
        'currency',
        'status',
        'notes',
        'terms',
        'public_token',
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        // Blindaje extra: si llega sin number (o alguien intenta forzarlo), generamos uno atómico.
        static::creating(function (Budget $budget) {
            // Si ya viene number desde el controlador, respetamos.
            if (!empty($budget->number)) {
                return;
            }

            $userId = $budget->user_id ?: auth()->id();
            $year = $budget->year ?: now()->year;

            // Si existe NumberSequence lo usamos; si no, fallback a MAX(sequence)+1 con lock.
            if (class_exists(\App\Models\NumberSequence::class)) {
                $next = \App\Models\NumberSequence::next('budget', $userId); // Formato PRES-YYYY-####
                if (preg_match('/^PRES-(\d{4})-(\d{4})$/', $next, $m)) {
                    $budget->number = $next;
                    if (static::hasColumn('year')) {
                        $budget->year = (int) $m[1];
                    }
                    if (static::hasColumn('sequence')) {
                        $budget->sequence = (int) $m[2];
                    }
                } else {
                    // Fallback si el helper no devolvió el formato esperado
                    static::fallbackAtomicNumber($budget, $year);
                }
            } else {
                static::fallbackAtomicNumber($budget, $year);
            }

            // Defaults seguros si tu tabla los exige (NOT NULL sin default)
            if (static::hasColumn('currency') && empty($budget->currency)) {
                $budget->currency = 'EUR';
            }
            if (static::hasColumn('status') && empty($budget->status)) {
                $budget->status = 'draft';
            }
            if (static::hasColumn('date') && empty($budget->date)) {
                $budget->date = now()->startOfDay();
            }
            if (static::hasColumn('due_date') && empty($budget->due_date)) {
                $budget->due_date = now()->addDays(14)->startOfDay();
            }
        });
    }

    /**
     * Fallback atómico si no existe NumberSequence: bloquea por año y calcula MAX(sequence)+1
     */
    protected static function fallbackAtomicNumber(Budget $budget, int $year): void
    {
        DB::transaction(function () use (&$budget, $year) {
            // Lock de las filas del año para evitar carrera
            $row = DB::table((new static)->getTable())
                ->selectRaw('MAX(sequence) as max_seq')
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            $nextSeq = (int)($row->max_seq ?? 0) + 1;

            if (static::hasColumn('sequence')) {
                $budget->sequence = $nextSeq;
            }
            if (static::hasColumn('year')) {
                $budget->year = $year;
            }

            $seqStr = str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);
            $budget->number = sprintf('PRES-%d-%s', $year, $seqStr);
        });
    }

    /** Helper para comprobar columnas presentes en la tabla */
    protected static function hasColumn(string $col): bool
    {
        static $cols;
        if ($cols === null) {
            $cols = Schema::getColumnListing((new static)->getTable());
        }
        return in_array($col, $cols, true);
    }

    // ================== RELACIONES ==================
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(BudgetItem::class)->orderBy('position');
    }

    // ================== ACCESORES ==================
    public function getStatusLabelAttribute(): string
    {
        $map = [
            'draft'   => 'Borrador',
            'pending' => 'Pendiente',
            'sent'    => 'Enviada',
            'paid'    => 'Pagada',
            'void'    => 'Anulada',
        ];
        return $map[$this->status] ?? ucfirst($this->status);
    }

    public function getStatusClassAttribute(): string
    {
        return match ($this->status) {
            'paid'    => 'bg-success',
            'sent'    => 'bg-info text-dark',
            'pending' => 'bg-warning text-dark',
            'draft'   => 'bg-secondary',
            'void'    => 'bg-dark',
            default   => 'bg-light text-dark',
        };
    }
}