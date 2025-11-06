<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id','action','entity_type','entity_id','meta','route','method','ip'
    ];

    protected $casts = [
        'meta' => 'array',
    ];
}
