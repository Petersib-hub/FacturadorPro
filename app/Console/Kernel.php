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
    ];

    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('invoices:generate-recurring')->dailyAt('02:00')->withoutOverlapping();
        // $schedule->command('invoices:send-due-reminders')->dailyAt('09:00');
    }
}
