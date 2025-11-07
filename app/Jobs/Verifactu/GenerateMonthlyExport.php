<?php

namespace App\Jobs\Verifactu;

use App\Models\Verifactu\ExportBatch;
use App\Services\Verifactu\VerifactuService;
use App\Support\Verifactu\EventLogger;
use App\Enums\Verifactu\LogEventType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateMonthlyExport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public ExportBatch $batch) {}

    public function handle(VerifactuService $service): void
    {
        $this->batch->status = 'processing';
        $this->batch->started_at = now();
        $this->batch->save();

        $path = $service->exportMonthly($this->batch->period, $this->batch->tenant);

        $this->batch->status = 'ready';
        $this->batch->file_path = $path;
        $this->batch->completed_at = now();
        $this->batch->save();

        EventLogger::log(LogEventType::EXPORT_GENERATED->value, ['batch_id' => $this->batch->id]);
    }
}
