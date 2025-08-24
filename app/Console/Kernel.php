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

    /*protected function schedule(Schedule $schedule): void
    {
        // Clean activity logs older than 10 days (custom command)
        $schedule->command('logs:clean-requests --days=10')->daily();

        // Daily DB backup at 01:00 AM
        $schedule->command('backup:run --only-db')->dailyAt('01:00');

        // Keep only backups from the last 5 days - clean at 01:10 AM
        $schedule->command('backup:clean')->dailyAt('01:10');

        // Monitor backup health daily at 01:20 AM
//        $schedule->command('backup:monitor')->dailyAt('01:20');

        // Optional: you can add queue or cache commands later

        $schedule->call(function () {
            \Log::info('✔️ Laravel scheduler is running at ' . now());
        })->everyMinute();
    }*/

    protected function schedule(Schedule $schedule): void
    {
        // Temporarily disable all real scheduled commands
        // $schedule->command('logs:clean-requests --days=10')->daily();
        // $schedule->command('backup:run --only-db')->dailyAt('01:00');
        // $schedule->command('backup:clean')->dailyAt('01:10');
        // $schedule->command('backup:monitor')->dailyAt('01:20');

        $schedule->call(function () {
            \Log::info('🟢 Laravel scheduler test triggered at ' . now()->toDateTimeString());
        })->withoutOverlapping()->everyMinute(); // Add withoutOverlapping to be safe

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
