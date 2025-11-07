<?php

namespace App\Models\Fiskaly;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $table = 'fiskaly_organizations';

    protected $fillable = [
        'tenant_type','tenant_id','name','nif','remote_id','managed','live_mode',
    ];

    protected $casts = [
        'managed' => 'boolean',
        'live_mode' => 'boolean',
    ];

    public function tenant() { return $this->morphTo(); }
    public function taxpayers() { return $this->hasMany(Taxpayer::class); }
    public function signers() { return $this->hasMany(Signer::class); }
    public function clients() { return $this->hasMany(Client::class); }
}
