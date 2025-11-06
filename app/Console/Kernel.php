<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Registra los comandos Artisan de la app.
     * Si creaste app/Console/Commands/GenerateRecurringInvoices.php
     * no hace falta listarlo manualmente, pero puedes hacerlo en $commands.
     */
    protected function commands(): void
    {
        // Carga automática de comandos desde app/Console/Commands
        $this->load(__DIR__.'/Commands');

        // Rutas de consola (opcional, si usas routes/console.php)
        require base_path('routes/console.php');
    }

    /**
     * Define el scheduler (tareas programadas).
     */
    protected function schedule(Schedule $schedule): void
    {
        // Genera facturas desde plantillas recurrentes cada madrugada
        // Cambia la hora a la que prefieras (formato HH:MM, 24h).
        $schedule->command('invoices:generate-recurring')
            ->dailyAt('02:00')
            ->withoutOverlapping();

            // Recordatorios diarios a las 09:00 hora del servidor
            $schedule->command('invoices:send-due-reminders')->dailyAt('09:00');

            //$schedule->command('invoices:generate-recurring')->everyMinute()
            //->appendOutputTo(storage_path('logs/scheduler.log'));


        // Ejemplos que podrías querer más adelante:
        // $schedule->command('horizon:snapshot')->everyFiveMinutes();
        // $schedule->command('queue:work --stop-when-empty')->everyMinute();
    }

    /**
     * (Opcional) Forzar zona horaria del scheduler si no quieres la de config/app.php
     */
    // protected function scheduleTimezone(): \DateTimeZone|string|null
    // {
    //     return 'Europe/Madrid';
    // }
}
