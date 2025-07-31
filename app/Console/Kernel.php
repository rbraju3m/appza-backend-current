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
        // request log clean except 10 days
        $schedule->command('logs:clean-requests --days=10')->daily();

        // Run daily backup at 01:00 AM
        $schedule->command('backup:run --only-db')->dailyAt('12:40');

        // Clean old backups weekly on Sundays at 02:00 AM
        $schedule->command('backup:clean')->weeklyOn(0, '02:00');

        // Monitor backup health daily at 03:00 AM
        $schedule->command('backup:monitor')->dailyAt('03:00');

        // Optional: Clear cache every night
        // $schedule->command('cache:clear')->dailyAt('03:30');

        // Optional: Run queued jobs every minute (if using queues)
        // $schedule->command('queue:work --stop-when-empty')->everyMinute();
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
