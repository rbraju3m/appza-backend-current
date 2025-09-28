<?php

declare(strict_types=1);

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

final class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */

    protected function schedule(Schedule $schedule): void
    {
        // Clean custom logs at 01:00 AM
        $schedule->command('logs:clean-requests --days=10')->dailyAt('01:00');

        // Daily DB backup at 01:10 AM
        $schedule->command('backup:run --only-db')->dailyAt('01:10');

        // Backup cleanup at 01:20 AM
        $schedule->command('backup:clean')->dailyAt('01:20');

        // Clean activity logs at 01:30 AM
        $schedule->command('activitylog:clean --days=10 --force')->dailyAt('01:30');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // Automatically load all Artisan commands in app/Console/Commands
        $this->load(__DIR__ . '/Commands');

        // Optionally load specific command files
        // require base_path('routes/console.php');
    }
}
