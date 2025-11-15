<?php

namespace App\Models;

use App\Models\Traits\HasAtomicInvoiceNumber;
use App\Models\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\HasVerifactu;

class Invoice extends Model
{
    use HasAtomicInvoiceNumber;
    use HasVerifactu;
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
        'amount_paid',
        'currency',
        'status',
        'public_token',
        'notes',
        'terms',
        // vínculo con presupuesto de origen (para trazabilidad)
        'origin_budget_id',
        'origin_budget_number',
    ];

    protected $casts = [
        'date'         => 'date',
        'due_date'     => 'date',
        'subtotal'     => 'decimal:2',
        'tax_total'    => 'decimal:2',
        'total'        => 'decimal:2',
        'amount_paid'  => 'decimal:2',
    ];

    // Relaciones
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
        return $this->hasMany(InvoiceItem::class)->orderBy('position');
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function originBudget()
    {
        return $this->belongsTo(Budget::class, 'origin_budget_id');
    }

    // Estado calculado
    public function recalcStatus(): void
    {
        $paid  = (float) ($this->amount_paid ?? 0);
        $total = (float) ($this->total ?? 0);

        if ($total <= 0) {
            $this->status = $this->status ?: 'draft';
        } elseif ($paid <= 0) {
            // si estaba 'draft' lo respetamos, si no, pendiente
            $this->status = $this->status === 'draft' ? 'draft' : 'pending';
        } elseif ($paid + 0.001 < $total) {
            $this->status = 'partial'; // si manejas 'partial'
        } else {
            $this->status = 'paid';
        }
        $this->save();
    }

    // Helpers para UI
    public function getStatusLabelAttribute(): string
    {
        $map = [
            'draft'   => 'Borrador',
            'pending' => 'Pendiente',
            'partial' => 'Parcial',
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
            'partial' => 'bg-primary',
            'pending' => 'bg-warning text-dark',
            'draft'   => 'bg-secondary',
            'void'    => 'bg-dark',
            default   => 'bg-light text-dark',
        };
    }
}