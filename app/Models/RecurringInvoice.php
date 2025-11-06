<?php

namespace App\Models;

use App\Models\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringInvoice extends Model
{
    use Tenantable;

    protected $fillable = [
        'user_id','client_id','start_date','frequency','currency',
        'next_run_date','last_run_date','last_invoice_id','status',
        'public_notes','terms',
    ];

    protected $casts = [
        'start_date'    => 'date',
        'next_run_date' => 'date',
        'last_run_date' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(RecurringInvoiceItem::class)->orderBy('position');
    }

    public function client() { return $this->belongsTo(Client::class); }

    /** Calcula la próxima fecha según la frecuencia */
    public function bumpNextRunDate(): void
    {
        $next = $this->next_run_date ?? now()->toDateString();

        $this->next_run_date = match ($this->frequency) {
            'monthly'   => \Carbon\Carbon::parse($next)->addMonth()->toDateString(),
            'quarterly' => \Carbon\Carbon::parse($next)->addMonths(3)->toDateString(),
            'yearly'    => \Carbon\Carbon::parse($next)->addYear()->toDateString(),
            default     => \Carbon\Carbon::parse($next)->addMonth()->toDateString(),
        };
    }
}
