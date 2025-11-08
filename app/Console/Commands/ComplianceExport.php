<?php

namespace App\Console\Commands;

use App\Models\ComplianceEventLog;
use Illuminate\Console\Command;

class ComplianceExport extends Command
{
    protected $signature = 'compliance:export
        {--article= : Filtro de artículo (p.ej. "art. 9%")}
        {--from= : Fecha/hora desde (YYYY-MM-DD)}
        {--to= : Fecha/hora hasta (YYYY-MM-DD)}
        {--format=json : json|csv}
        {--path= : Ruta de salida (por defecto storage/app/compliance_*.ext)}';

    protected $description = 'Exporta eventos de cumplimiento (por artículo y rango)';

    public function handle(): int
    {
        $q = ComplianceEventLog::query()->orderBy('id');

        if ($a = $this->option('article')) {
            $q->where('article', 'like', $a);
        }
        if ($f = $this->option('from')) {
            $q->where('created_at', '>=', $f.' 00:00:00');
        }
        if ($t = $this->option('to')) {
            $q->where('created_at', '<=', $t.' 23:59:59');
        }

        $rows = $q->get()->toArray();
        $fmt  = strtolower($this->option('format') ?: 'json');

        if ($fmt === 'csv') {
            $csv = $this->toCsv($rows);
            $path = $this->option('path') ?: storage_path('app/compliance_'.date('Ymd_His').'.csv');
            file_put_contents($path, $csv);
        } else {
            $json = json_encode($rows, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            $path = $this->option('path') ?: storage_path('app/compliance_'.date('Ymd_His').'.json');
            file_put_contents($path, $json);
        }

        $this->info("Exportado: {$path}");
        return self::SUCCESS;
    }

    private function toCsv(array $rows): string
    {
        if (!$rows) return '';
        $f = fopen('php://temp', 'r+');
        fputcsv($f, array_keys($rows[0]));
        foreach ($rows as $r) {
            // aplanar payload a JSON
            if (isset($r['payload']) && is_array($r['payload'])) {
                $r['payload'] = json_encode($r['payload'], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
            }
            fputcsv($f, array_map(fn($v) => is_bool($v) ? ($v?'1':'0') : $v, $r));
        }
        rewind($f);
        return stream_get_contents($f);
    }
}