<?php

namespace App\Models;

use App\Models\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
