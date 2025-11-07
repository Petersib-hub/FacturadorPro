<?php

namespace App\Models\Verifactu;

use Illuminate\Database\Eloquent\Model;

class ExportBatch extends Model
{
    protected $table = 'verifactu_export_batches';

    protected $fillable = [
        'tenant_type','tenant_id','period','status','file_path','checksum',
        'started_at','completed_at','error',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->morphTo();
    }
}
