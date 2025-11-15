<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Registra comandos de consola (autoload de app/Console/Commands).
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }

    /**
     * Si en tu proyecto defines comandos manualmente, inclúyelos aquí también.
     * (En Laravel 11 no es obligatorio; los de app/Console/Commands se autodescubren.)
     */
    protected $commands = [
        \App\Console\Commands\BudgetsResequence::class,
        \App\Console\Commands\ComplianceExport::class,
        \App\Console\Commands\ComplianceAnomalyScan::class,
        \App\Console\Commands\GenerateRecurringInvoices::class,
        \App\Console\Commands\ProjectReset::class, // ← NUEVO
    ];

    /**
     * Programación de tareas CRON.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Ejemplos (actívalos si lo necesitas):
        // $schedule->command('invoices:generate-recurring')->dailyAt('02:00')->withoutOverlapping();
        // $schedule->command('compliance:anomaly-scan')->weeklyOn(1, '03:00'); // lunes 03:00
        // $schedule->command('compliance:export')->weeklyOn(1, '03:15');
    }
}