<?php

namespace App\Models\Fiskaly;

use Illuminate\Database\Eloquent\Model;

class Taxpayer extends Model
{
    protected $table = 'fiskaly_taxpayers';
    protected $fillable = [
        'fiskaly_organization_id','remote_id','legal_name','nif','address',
    ];
    protected $casts = [
        'address' => 'array',
    ];
    public function organization() { return $this->belongsTo(Organization::class, 'fiskaly_organization_id'); }
}
