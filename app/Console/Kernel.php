<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }

    protected $commands = [
        \App\Console\Commands\BudgetsResequence::class,
        \App\Console\Commands\ComplianceExport::class,
        \App\Console\Commands\ComplianceAnomalyScan::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Tareas que ya tenías (si las activas):
        // $schedule->command('invoices:generate-recurring')->dailyAt('02:00')->withoutOverlapping();
        // $schedule->command('invoices:send-due-reminders')->dailyAt('09:00');

        // Escaneo de anomalías semanal (art. 9.1.c). Lunes 03:30 (usa timezone de config/app.php)
        $schedule->command(
            'compliance:scan --from=' . now()->subWeek()->toDateString() . ' --to=' . now()->toDateString()
        )->weeklyOn(1, '03:30')->withoutOverlapping();
    }
}