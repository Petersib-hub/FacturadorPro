<?php

namespace App\Models;

use App\Models\Traits\Tenantable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Client extends Model
{
    use Tenantable, SoftDeletes;

    protected $fillable = [
        'user_id','name','email','phone','tax_id','address','zip','city','country',
        'public_token', // <- añadir
    ];

    protected static function booted(): void
    {
        static::creating(function (Client $client) {
            if (empty($client->public_token)) {
                $client->public_token = Str::random(48);
            }
        });
    }
    // ...
    protected $casts = [
        'consent_accepted_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
