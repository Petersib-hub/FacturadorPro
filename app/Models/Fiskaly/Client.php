<?php

namespace App\Models\Fiskaly;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'fiskaly_clients';
    protected $fillable = [
        'fiskaly_organization_id','remote_id','api_key','api_secret','base_url','live_mode',
    ];
    protected $casts = [
        'api_key' => 'encrypted:string',
        'api_secret' => 'encrypted:string',
        'live_mode' => 'boolean',
    ];
    public function organization() { return $this->belongsTo(Organization::class, 'fiskaly_organization_id'); }
}
