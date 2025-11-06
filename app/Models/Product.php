<?php

namespace App\Models;

use App\Models\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use Tenantable, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'unit_price',
        'tax_rate',
        'tax_rate_id',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'tax_rate'   => 'decimal:3',
    ];

    public function user(){ return $this->belongsTo(User::class); }
    public function taxRate(){ return $this->belongsTo(TaxRate::class); }

    /* ---------- Compatibilidad con 'price' ---------- */
    // Leer $product->price devolverá unit_price
    public function getPriceAttribute()
    {
        return $this->unit_price;
    }
    // Asignar $product->price = 100 guardará unit_price = 100
    public function setPriceAttribute($value): void
    {
        $this->attributes['unit_price'] = $value;
    }
}
