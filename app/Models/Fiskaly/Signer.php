<?php

namespace App\Models\Fiskaly;

use Illuminate\Database\Eloquent\Model;

class Signer extends Model
{
    protected $table = 'fiskaly_signers';
    protected $fillable = [
        'fiskaly_organization_id','remote_id','name','email','certificate_id','credentials',
    ];
    protected $casts = [
        // Requiere Laravel 10+ con casts 'encrypted:*' habilitados en config/app.php
        'credentials' => 'encrypted:array',
    ];
    public function organization() { return $this->belongsTo(Organization::class, 'fiskaly_organization_id'); }
}
