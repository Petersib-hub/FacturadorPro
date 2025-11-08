<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplianceEventLog extends Model
{
    public $timestamps = false; // usamos created_at manual
    protected $table = 'compliance_event_logs';
    protected $fillable = [
        'boe_ref','article','code','message','entity_type','entity_id','user_id','payload','prev_hash','hash','created_at'
    ];
    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];
}