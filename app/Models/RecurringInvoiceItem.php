<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecurringInvoiceItem extends Model
{
    protected $fillable = [
        'recurring_invoice_id','product_id','description','quantity',
        'unit_price','tax_rate','discount','position',
    ];

    public function recurringInvoice()
    {
        return $this->belongsTo(RecurringInvoice::class);
    }
}
