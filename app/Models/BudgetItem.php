<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetItem extends Model
{
    protected $fillable = [
        'budget_id','product_id','description','quantity','unit_price','tax_rate','discount','total_line','position',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:3',
        'discount' => 'decimal:3',
        'total_line' => 'decimal:2',
    ];

    public function budget(){ return $this->belongsTo(Budget::class); }
    public function product(){ return $this->belongsTo(Product::class); }
}
