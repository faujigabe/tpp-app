<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('database:backup')->dailyAt('01:00')->timezone('Asia/Jakarta')->withoutOverlapping();
        $schedule->command('database:backup --weekly')->sundays()->at('02:00')->timezone('Asia/Jakarta')->withoutOverlapping();
        $schedule->command('audit:prune')->monthlyOn(1, '03:00')->timezone('Asia/Jakarta')->withoutOverlapping();
        $schedule->command('notifications:prune')->monthlyOn(1, '03:30')->timezone('Asia/Jakarta')->withoutOverlapping();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
