<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait Tenantable
{
    protected static function bootTenantable()
    {
        static::creating(function ($model) {
            if (Auth::check() && empty($model->user_id)) {
                $model->user_id = Auth::id();
            }
        });

        // Scope global para aislar por user_id (solo en web, no en consola)
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (app()->runningInConsole()) return;
            if (Auth::check()) {
                $builder->where($builder->getModel()->getTable().'.user_id', Auth::id());
            }
        });
    }

    // Helper local scope manual (útil en consola/tests)
    public function scopeForAuthUser(Builder $q, ?int $userId = null)
    {
        return $q->where($this->getTable().'.user_id', $userId ?? Auth::id());
    }
}
