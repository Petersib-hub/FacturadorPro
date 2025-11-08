<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Budget;
use App\Support\Compliance;
use Illuminate\Console\Command;

class ComplianceAnomalyScan extends Command
{
    protected $signature = 'compliance:scan
        {--from= : YYYY-MM-DD}
        {--to= : YYYY-MM-DD}';

    protected $description = 'Escaneo básico de anomalías (art. 9.1.c)';

    public function handle(): int
    {
        $from = $this->option('from');
        $to   = $this->option('to');

        $this->info('Iniciando escaneo de anomalías...');
        $issues = [
            'invoices_negative_total' => Invoice::query()
                ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('date', '<=', $to))
                ->where('total', '<', 0)->count(),

            'budgets_negative_total' => Budget::query()
                ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('date', '<=', $to))
                ->where('total', '<', 0)->count(),

            'invoices_missing_number' => Invoice::query()
                ->when($from, fn($q) => $q->whereDate('date', '>=', $from))
                ->when($to, fn($q) => $q->whereDate('date', '<=', $to))
                ->whereNull('number')->orWhere('number','')->count(),
        ];

        Compliance::log('art. 9.1.c', 'ANOMALY_SCAN', 'Escaneo de anomalías finalizado', [
            'from' => $from, 'to' => $to, 'issues' => $issues
        ], null, null, auth()->id());

        $this->table(['Chequeo','Incidencias'], collect($issues)->map(fn($v,$k)=>[$k,$v])->toArray());
        $this->info('Escaneo completado.');
        return self::SUCCESS;
    }
}