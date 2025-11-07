<?php

namespace App\Models\Verifactu;

use Illuminate\Database\Eloquent\Model;

class EventLog extends Model
{
    protected $table = 'verifactu_event_logs';

    protected $fillable = [
        'tenant_type', 'tenant_id',
        'actor_type', 'actor_id',
        'event_type', 'source', 'ip', 'user_agent', 'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function tenant()
    {
        return $this->morphTo();
    }

    public function actor()
    {
        return $this->morphTo();
    }
}
