<?php

namespace App\Models;

use App\Models\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Database\Eloquent\SoftDeletes;

class TaxRate extends Model
{
    use Tenantable;
    // use SoftDeletes; // ← Actívalo solo si tu tabla tiene 'deleted_at'

    protected $fillable = ['user_id','name','rate','is_default','is_exempt'];

    protected $casts = [
        'rate'       => 'decimal:3',
        'is_default' => 'boolean',
        'is_exempt'  => 'boolean',
    ];

    /**
     * Scope: tasas del usuario autenticado
     */
    public function scopeForAuthUser(Builder $q): Builder
    {
        return $q->where('user_id', auth()->id());
    }

    /**
     * Asegura user_id y consistencia de exención al crear/guardar
     */
    protected static function booted(): void
    {
        static::creating(function (TaxRate $model) {
            if (empty($model->user_id) && auth()->check()) {
                $model->user_id = auth()->id();
            }
            if ($model->is_exempt) {
                $model->rate = 0;
            }
        });

        static::saving(function (TaxRate $model) {
            if ($model->is_exempt) {
                $model->rate = 0;
            }
        });
    }
}
