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
    // ...

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
        'public_token', // <- añade esto
        'notes',
        'terms',
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

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

    public function recalcStatus(): void
    {
        $paid = (float) $this->amount_paid;
        $total = (float) $this->total;

        if ($paid <= 0) {
            $this->status = 'pending';
        } elseif ($paid > 0 && $paid + 0.001 < $total) {
            $this->status = 'partial';
        } else {
            $this->status = 'paid';
        }
        $this->save();
    }

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